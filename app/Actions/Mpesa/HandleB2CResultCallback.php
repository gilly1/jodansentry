<?php

namespace App\Actions\Mpesa;

use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\MpesaApiLog;
use App\Models\PaymentItem;
use App\Models\SuccessfulTransaction;
use App\Services\Mpesa\MpesaResponseParser;
use Illuminate\Support\Facades\Log;

class HandleB2CResultCallback
{
    public function execute(array $data): void
    {
        $parser = new MpesaResponseParser($data);
        $conversationId = $parser->conversationId();
        $originatorId = $parser->originatorConversationId();

        if (!$conversationId && !$originatorId) {
            Log::channel('mpesa')->warning('B2C callback: No conversation IDs in payload', [
                'data' => $data,
            ]);
            return;
        }

        $query = PaymentItem::query();
        if ($conversationId) {
            $query->where('mpesa_conversation_id', $conversationId);
        }
        if ($originatorId) {
            $query->{$conversationId ? 'orWhere' : 'where'}('mpesa_originator_conversation_id', $originatorId);
        }
        $item = $query->first();

        if (!$item) {
            Log::channel('mpesa')->warning('B2C callback: No matching payment item', [
                'conversation_id' => $conversationId,
                'originator_id' => $originatorId,
            ]);
            return;
        }

        MpesaApiLog::create([
            'payment_item_id' => $item->id,
            'payment_batch_id' => $item->payment_batch_id,
            'direction' => 'callback',
            'endpoint' => 'b2c/result',
            'payload' => $data,
            'masked_payload' => $data,
        ]);

        if ($parser->resultCode() === '0') {
            $item->update([
                'status' => PaymentItemStatus::SUCCESSFUL,
                'mpesa_result_code' => $parser->resultCode(),
                'mpesa_result_description' => $parser->resultDescription(),
                'mpesa_transaction_receipt' => $parser->transactionReceipt(),
                'callback_payload' => $data,
                'processed_at' => now(),
            ]);

            $this->recordContactAndTransaction($item, $parser);
        } else {
            $item->update([
                'status' => PaymentItemStatus::FAILED,
                'mpesa_result_code' => $parser->resultCode(),
                'mpesa_result_description' => $parser->resultDescription(),
                'callback_payload' => $data,
                'failed_at' => now(),
            ]);
        }

        AuditLog::record(
            'payment_callback_received',
            $item,
            null,
            [
                'result_code' => $parser->resultCode(),
                'result_description' => $parser->resultDescription(),
                'status' => $item->status->value,
                'transaction_receipt' => $parser->transactionReceipt(),
                'phone' => $item->normalized_phone,
                'amount' => $item->amount,
            ],
        );

        app(UpdateBatchAggregateStatus::class)->execute($item->batch);
    }

    private function recordContactAndTransaction(PaymentItem $item, MpesaResponseParser $parser): void
    {
        $phone = $item->normalized_phone;
        $mpesaName = $parser->receiverPartyPublicName() ?? $item->employee_name ?? 'Unknown';

        $contact = Contact::updateOrCreate(
            ['phone_number' => $phone],
            ['mpesa_name' => $mpesaName],
        );

        SuccessfulTransaction::create([
            'contact_id' => $contact->id,
            'payment_item_id' => $item->id,
            'payment_batch_id' => $item->payment_batch_id,
            'transaction_receipt' => $parser->transactionReceipt(),
            'phone_number' => $phone,
            'amount' => $item->amount,
            'mpesa_result_description' => $parser->resultDescription(),
            'paid_at' => now(),
        ]);

        $contact->refreshTotals();
    }
}
