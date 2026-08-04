<?php

namespace App\Actions\Mpesa;

use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\MpesaApiLog;
use App\Models\PaymentItem;
use Illuminate\Support\Facades\Log;

class HandleB2CTimeoutCallback
{
    public function execute(array $data): void
    {
        $originatorId = $data['Result']['OriginatorConversationID']
            ?? $data['OriginatorConversationID']
            ?? null;

        $conversationId = $data['Result']['ConversationID']
            ?? $data['ConversationID']
            ?? null;

        $item = PaymentItem::where('mpesa_originator_conversation_id', $originatorId)
            ->orWhere('mpesa_conversation_id', $conversationId)
            ->first();

        if (!$item) {
            Log::channel('mpesa')->warning('B2C timeout: No matching payment item', compact('originatorId', 'conversationId'));
            return;
        }

        MpesaApiLog::create([
            'payment_item_id' => $item->id,
            'payment_batch_id' => $item->payment_batch_id,
            'direction' => 'timeout',
            'endpoint' => 'b2c/timeout',
            'payload' => $data,
            'masked_payload' => $data,
        ]);

        $item->update([
            'status' => PaymentItemStatus::TIMEOUT,
            'timeout_payload' => $data,
        ]);

        AuditLog::record('payment_timeout', $item);

        app(UpdateBatchAggregateStatus::class)->execute($item->batch);
    }
}
