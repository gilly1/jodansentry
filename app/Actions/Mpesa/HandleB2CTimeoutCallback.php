<?php

namespace App\Actions\Mpesa;

use App\Actions\Payments\UpdateBatchAggregateStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\MpesaApiLog;
use App\Models\PaymentItem;

class HandleB2CTimeoutCallback
{
    public function handle(array $payload): void
    {
        MpesaApiLog::create([
            'direction' => 'timeout',
            'endpoint' => 'b2c/timeout',
            'payload' => $payload,
            'masked_payload' => $payload,
        ]);

        $result = data_get($payload, 'Result');
        if (! $result) {
            return;
        }

        $originatorConversationId = data_get($result, 'OriginatorConversationID');
        $conversationId = data_get($result, 'ConversationID');

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

        $item->update([
            'status' => PaymentItemStatus::TIMEOUT,
            'timeout_payload' => $payload,
            'failed_at' => now(),
        ]);

        AuditLog::record('payment_timeout_received', $item);

        app(UpdateBatchAggregateStatus::class)->handle($item->batch);
    }
}
