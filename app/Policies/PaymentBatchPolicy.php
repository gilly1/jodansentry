<?php

namespace App\Policies;

use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;
use App\Models\User;

class PaymentBatchPolicy
{
    public function view(User $user, PaymentBatch $batch): bool
    {
        if ($user->can('payment-batches.view-all')) {
            return true;
        }

        return $user->can('payment-batches.view') && $batch->uploaded_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('payment-batches.upload');
    }

    public function submit(User $user, PaymentBatch $batch): bool
    {
        return $user->can('payment-batches.submit')
            && $batch->status === PaymentBatchStatus::UPLOADED
            && $batch->uploaded_by === $user->id;
    }

    public function approve(User $user, PaymentBatch $batch): bool
    {
        return $user->can('payment-batches.approve')
            && $batch->status === PaymentBatchStatus::PENDING_APPROVAL;
    }

    public function reject(User $user, PaymentBatch $batch): bool
    {
        return $user->can('payment-batches.reject')
            && $batch->status === PaymentBatchStatus::PENDING_APPROVAL;
    }

    public function schedule(User $user, PaymentBatch $batch): bool
    {
        return $user->can('payment-batches.schedule')
            && $batch->status === PaymentBatchStatus::APPROVED;
    }

    public function process(User $user, PaymentBatch $batch): bool
    {
        return $user->can('payment-batches.process')
            && $batch->isProcessable();
    }

    public function cancel(User $user, PaymentBatch $batch): bool
    {
        return $user->hasRole('Admin')
            && $batch->isCancellable();
    }

    public function retryFailed(User $user, PaymentBatch $batch): bool
    {
        return $user->can('payment-batches.retry-failed');
    }

    public function export(User $user): bool
    {
        return $user->can('payment-batches.export');
    }
}
