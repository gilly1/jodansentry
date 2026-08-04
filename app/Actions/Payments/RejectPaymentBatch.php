<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;

class RejectPaymentBatch
{
    public function execute(PaymentBatch $batch, string $reason): void
    {
        $batch->update([
            'status' => PaymentBatchStatus::REJECTED,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        AuditLog::record('batch_rejected', $batch, null, null, ['reason' => $reason]);
    }
}
