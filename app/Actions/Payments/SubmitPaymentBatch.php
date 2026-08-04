<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;

class SubmitPaymentBatch
{
    public function execute(PaymentBatch $batch): void
    {
        $user = auth()->user();
        $allowSelfApproval = config('payments.allow_self_approval', false);

        if ($allowSelfApproval && $user->can('approve payments')) {
            $batch->update([
                'status' => PaymentBatchStatus::APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'self_approved' => true,
                'submitted_at' => now(),
            ]);

            AuditLog::record('batch_self_approved', $batch);
        } else {
            $batch->update([
                'status' => PaymentBatchStatus::PENDING_APPROVAL,
                'submitted_at' => now(),
            ]);

            AuditLog::record('batch_submitted', $batch);
        }
    }
}
