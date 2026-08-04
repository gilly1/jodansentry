<?php

namespace App\Actions\Mpesa;

use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;

class UpdateBatchAggregateStatus
{
    public function execute(PaymentBatch $batch): void
    {
        $batch->refreshTotals();

        $total = $batch->valid_records;
        $successful = $batch->successful_records;
        $failed = $batch->failed_records;
        $processed = $successful + $failed;

        if ($processed < $total) {
            return; // Still processing
        }

        if ($successful === $total) {
            $batch->update([
                'status' => PaymentBatchStatus::SUCCESSFUL,
                'processing_completed_at' => now(),
            ]);
        } elseif ($failed === $total) {
            $batch->update([
                'status' => PaymentBatchStatus::FAILED,
                'processing_completed_at' => now(),
            ]);
        } else {
            $batch->update([
                'status' => PaymentBatchStatus::PARTIALLY_SUCCESSFUL,
                'processing_completed_at' => now(),
            ]);
        }
    }
}
