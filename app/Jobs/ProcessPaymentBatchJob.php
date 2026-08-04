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
use Illuminate\Support\Facades\Log;

class ProcessPaymentBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    // public string $queue = 'payments';

    public function __construct(private int $batchId) {}

    public function handle(): void
    {
        $batch = PaymentBatch::find($this->batchId);

        if (!$batch || !$batch->isProcessable()) {
            // return;
            Log::warning('Batch not found or not processable', [
                'batch_id' => $this->batchId,
            ]);
        }

        $batch->update([
            'status' => PaymentBatchStatus::PROCESSING,
            'processing_started_at' => now(),
        ]);

        $payableItems = $batch->items()
            ->whereIn('status', [
                PaymentItemStatus::VALIDATED->value,
                PaymentItemStatus::QUEUED->value,
                PaymentItemStatus::FAILED->value,
                PaymentItemStatus::TIMEOUT->value,
            ])
            ->get();

        foreach ($payableItems as $item) {
            $item->markQueued();
            DispatchMpesaPaymentJob::dispatch($item->id);
        }

        AuditLog::record('batch_processing_started', $batch, null, [
            'items_queued' => $payableItems->count(),
        ]);

        Log::info('Batch processing started', [
            'batch_id' => $batch->batch_id,
            'items' => $payableItems->count(),
        ]);
    }
}
