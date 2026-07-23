<?php

namespace App\Jobs;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public int $batchId,
    ) {
        $this->queue = 'payments';
    }

    public function handle(): void
    {
        $batch = PaymentBatch::findOrFail($this->batchId);

        if (! $batch->isProcessable()) {
            return;
        }

        $batch->update([
            'status' => PaymentBatchStatus::PROCESSING,
            'processing_started_at' => now(),
        ]);

        AuditLog::record('batch_processing_started', $batch);

        $payableItems = $batch->items()
            ->whereIn('status', [
                PaymentItemStatus::VALIDATED,
                PaymentItemStatus::QUEUED,
            ])
            ->get();

        foreach ($payableItems as $item) {
            $item->markQueued();
            DispatchMpesaPaymentJob::dispatch($item->id)
                ->onQueue('payments');
        }
    }
}
