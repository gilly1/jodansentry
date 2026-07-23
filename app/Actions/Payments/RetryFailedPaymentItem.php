<?php

namespace App\Actions\Payments;

use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\PaymentItem;
use App\Models\User;
use DomainException;

class RetryFailedPaymentItem
{
    public function handle(PaymentItem $item, User $user): void
    {
        if (! in_array($item->status, [PaymentItemStatus::FAILED, PaymentItemStatus::TIMEOUT])) {
            throw new DomainException('Only failed or timed out items can be retried.');
        }

        if ($item->mpesa_transaction_receipt) {
            throw new DomainException('Item already has a successful M-Pesa receipt. Cannot retry.');
        }

        $maxRetries = config('mpesa.max_retries', 3);
        if ($item->attempts >= $maxRetries) {
            throw new DomainException("Item has exceeded the maximum retry count ({$maxRetries}).");
        }

        $item->update([
            'status' => PaymentItemStatus::RETRYING,
        ]);

        AuditLog::record('failed_payment_retried', $item, $user, null, [
            'attempt' => $item->attempts + 1,
        ]);

        dispatch(new \App\Jobs\DispatchMpesaPaymentJob($item->id));
    }
}
