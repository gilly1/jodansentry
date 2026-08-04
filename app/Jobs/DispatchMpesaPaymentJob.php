<?php

namespace App\Jobs;

use App\Actions\Mpesa\SendB2CPayment;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\PaymentItem;
use App\Services\Mpesa\MpesaException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchMpesaPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private int $paymentItemId) {}

    public function handle(SendB2CPayment $action): void
    {
        $item = PaymentItem::lockForUpdate()->find($this->paymentItemId);

        if (!$item || !$item->isPayable()) {
            return;
        }

        $item->markProcessing();

        try {
            $response = $action->execute(
                $item->normalized_phone,
                $item->amount,
                $item->narration ?? 'Salary Payment',
                $item->payment_batch_id,
                $item->id,
            );

            $item->update([
                'mpesa_originator_conversation_id' => $response->originatorConversationId(),
                'mpesa_conversation_id' => $response->conversationId(),
                'mpesa_response_code' => $response->responseCode(),
                'mpesa_response_description' => $response->responseDescription(),
                'response_payload' => $response->raw(),
            ]);

            if ($response->responseCode() !== '0') {
                $item->update([
                    'status' => PaymentItemStatus::FAILED,
                    'failed_at' => now(),
                ]);
            }

            AuditLog::record('payment_dispatched', $item, null, [
                'response_code' => $response->responseCode(),
            ]);
        } catch (MpesaException $e) {
            $item->update([
                'status' => PaymentItemStatus::FAILED,
                'mpesa_result_description' => $e->getMessage(),
                'response_payload' => $e->getResponseData(),
                'failed_at' => now(),
            ]);

            AuditLog::record('payment_failed', $item, null, null, [
                'error' => $e->getMessage(),
            ]);

            Log::channel('mpesa')->error('Payment dispatch failed', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
