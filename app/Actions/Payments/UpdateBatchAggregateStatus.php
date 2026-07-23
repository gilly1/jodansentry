<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\PaymentBatch;

class UpdateBatchAggregateStatus
{
    public function handle(PaymentBatch $batch): void
    {
        $batch->loadCount([
            'items as total_items_count',
            'items as successful_count' => fn ($q) => $q->where('status', PaymentItemStatus::SUCCESSFUL),
            'items as failed_count' => fn ($q) => $q->whereIn('status', [PaymentItemStatus::FAILED, PaymentItemStatus::TIMEOUT]),
            'items as processing_count' => fn ($q) => $q->whereIn('status', [PaymentItemStatus::PROCESSING, PaymentItemStatus::QUEUED, PaymentItemStatus::RETRYING]),
            'items as payable_count' => fn ($q) => $q->whereNotIn('status', [PaymentItemStatus::INVALID, PaymentItemStatus::SKIPPED]),
        ]);

        if ($batch->processing_count > 0) {
            return; // Still processing
        }

        $payable = $batch->payable_count;
        $successful = $batch->successful_count;
        $failed = $batch->failed_count;

        if ($payable === 0) {
            return;
        }

        if ($successful === $payable) {
            $status = PaymentBatchStatus::SUCCESSFUL;
        } elseif ($failed === $payable) {
            $status = PaymentBatchStatus::FAILED;
        } elseif ($successful > 0) {
            $status = PaymentBatchStatus::PARTIALLY_SUCCESSFUL;
        } else {
            return; // Indeterminate state
        }

        $batch->update([
            'status' => $status,
            'processing_completed_at' => now(),
        ]);
    }
}
