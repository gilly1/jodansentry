<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Gate;

class SubmitPaymentBatch
{
    public function handle(PaymentBatch $batch, User $user): void
    {
        if ($batch->status !== PaymentBatchStatus::UPLOADED) {
            throw new DomainException('Only uploaded batches can be submitted.');
        }

        if ($batch->valid_record_count === 0) {
            throw new DomainException('Batch has no valid payment records.');
        }

        if ($user->can('payment-batches.approve') && config('payments.allow_self_approval')) {
            $batch->update([
                'status' => PaymentBatchStatus::APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'submitted_at' => now(),
                'self_approved' => true,
            ]);

            AuditLog::record('batch_self_approved', $batch, $user);

            return;
        }

        $batch->update([
            'status' => PaymentBatchStatus::PENDING_APPROVAL,
            'submitted_at' => now(),
        ]);

        AuditLog::record('batch_submitted', $batch, $user);
    }
}
