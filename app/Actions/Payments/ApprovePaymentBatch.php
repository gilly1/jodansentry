<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use App\Models\User;
use DomainException;

class ApprovePaymentBatch
{
    public function handle(PaymentBatch $batch, User $user): void
    {
        if ($batch->status !== PaymentBatchStatus::PENDING_APPROVAL) {
            throw new DomainException('Only pending approval batches can be approved.');
        }

        $batch->update([
            'status' => PaymentBatchStatus::APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        AuditLog::record('batch_approved', $batch, $user);
    }
}
