<?php

namespace App\Policies;

use App\Models\PaymentItem;
use App\Models\User;

class PaymentItemPolicy
{
    public function view(User $user, PaymentItem $item): bool
    {
        if ($user->can('payment-batches.view-all')) {
            return true;
        }

        return $user->can('payment-batches.view')
            && $item->batch->uploaded_by === $user->id;
    }

    public function retry(User $user, PaymentItem $item): bool
    {
        return $user->can('payment-batches.retry-failed');
    }
}
