<?php

namespace App\Jobs;

use App\Actions\Mpesa\SendB2CPayment;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\PaymentItem;
use App\Services\Mpesa\Exceptions\MpesaException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DispatchMpesaPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $paymentItemId,
    ) {
        $this->queue = 'payments';
    }

    public function handle(SendB2CPayment $sendB2CPayment): void
    {
        DB::transaction(function () use ($sendB2CPayment) {
            $item = PaymentItem::whereKey($this->paymentItemId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $item->isPayable()) {
                return;
            }

            $item->markProcessing();

            try {
                $response = $sendB2CPayment->handle($item);

                $item->update([
                    'mpesa_originator_conversation_id' => $response->originatorConversationId(),
                    'mpesa_conversation_id' => $response->conversationId(),
                    'mpesa_response_code' => $response->responseCode(),
                    'mpesa_response_description' => $response->responseDescription(),
                    'response_payload' => $response->toArray(),
                    'attempts' => DB::raw('attempts + 1'),
                    'last_attempted_at' => now(),
                ]);

                AuditLog::record('payment_sent_to_mpesa', $item);
            } catch (MpesaException $e) {
                $item->update([
                    'status' => PaymentItemStatus::FAILED,
                    'mpesa_response_description' => $e->getMessage(),
                    'response_payload' => $e->getResponseData(),
                    'attempts' => DB::raw('attempts + 1'),
                    'last_attempted_at' => now(),
                    'failed_at' => now(),
                ]);

                AuditLog::record('payment_failed', $item, null, null, [
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
