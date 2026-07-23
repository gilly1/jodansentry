<?php

namespace App\Actions\Mpesa;

use App\Actions\Payments\UpdateBatchAggregateStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\MpesaApiLog;
use App\Models\PaymentItem;
use App\Services\Mpesa\MpesaResponseParser;

class HandleB2CResultCallback
{
    public function handle(array $payload): void
    {
        MpesaApiLog::create([
            'direction' => 'callback',
            'endpoint' => 'b2c/result',
            'payload' => $payload,
            'masked_payload' => $payload,
        ]);

        $result = data_get($payload, 'Result');
        if (! $result) {
            return;
        }

        $parser = new MpesaResponseParser($result);

        $originatorConversationId = $parser->originatorConversationId();
        $conversationId = $parser->conversationId();

        $item = PaymentItem::query()
            ->where('mpesa_originator_conversation_id', $originatorConversationId)
            ->orWhere('mpesa_conversation_id', $conversationId)
            ->first();

        if (! $item) {
            return;
        }

        if ($item->status === PaymentItemStatus::SUCCESSFUL) {
            return;
        }

        $resultCode = $parser->resultCode();
        $isSuccess = $resultCode === 0;

        $item->update([
            'status' => $isSuccess ? PaymentItemStatus::SUCCESSFUL : PaymentItemStatus::FAILED,
            'mpesa_result_code' => $resultCode,
            'mpesa_result_description' => $parser->resultDescription(),
            'mpesa_transaction_receipt' => $parser->transactionReceipt(),
            'callback_payload' => $payload,
            'processed_at' => $isSuccess ? now() : null,
            'failed_at' => ! $isSuccess ? now() : null,
        ]);

        MpesaApiLog::where('payment_item_id', $item->id)
            ->latest()
            ->first()
            ?->update(['payment_batch_id' => $item->payment_batch_id]);

        AuditLog::record(
            $isSuccess ? 'payment_succeeded' : 'payment_failed',
            $item,
            null,
            null,
            ['result_code' => $resultCode],
        );

        app(UpdateBatchAggregateStatus::class)->handle($item->batch);
    }
}
