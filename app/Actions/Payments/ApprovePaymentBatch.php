<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;

class ApprovePaymentBatch
{
    public function execute(PaymentBatch $batch): void
    {
        $batch->update([
            'status' => PaymentBatchStatus::APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLog::record('batch_approved', $batch);
    }
}
