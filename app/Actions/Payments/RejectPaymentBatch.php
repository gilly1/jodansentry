<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use App\Models\User;
use DomainException;

class RejectPaymentBatch
{
    public function handle(PaymentBatch $batch, User $user, string $reason): void
    {
        if ($batch->status !== PaymentBatchStatus::PENDING_APPROVAL) {
            throw new DomainException('Only pending approval batches can be rejected.');
        }

        $batch->update([
            'status' => PaymentBatchStatus::REJECTED,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        AuditLog::record('batch_rejected', $batch, $user, null, [
            'rejection_reason' => $reason,
        ]);
    }
}
