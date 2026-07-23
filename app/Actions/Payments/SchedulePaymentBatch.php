<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use App\Models\User;
use Carbon\Carbon;
use DomainException;

class SchedulePaymentBatch
{
    public function handle(PaymentBatch $batch, User $user, ?Carbon $scheduledAt = null): void
    {
        if ($batch->status !== PaymentBatchStatus::APPROVED) {
            throw new DomainException('Only approved batches can be scheduled.');
        }

        if ($scheduledAt && $scheduledAt->isPast()) {
            throw new DomainException('Scheduled time must be in the future.');
        }

        if ($scheduledAt) {
            $batch->update([
                'status' => PaymentBatchStatus::SCHEDULED,
                'scheduled_at' => $scheduledAt,
            ]);

            AuditLog::record('batch_scheduled', $batch, $user, null, [
                'scheduled_at' => $scheduledAt->toIso8601String(),
            ]);
        } else {
            // Immediate execution
            $batch->update([
                'status' => PaymentBatchStatus::SCHEDULED,
                'scheduled_at' => now(),
            ]);

            AuditLog::record('batch_scheduled', $batch, $user, null, [
                'scheduled_at' => now()->toIso8601String(),
                'immediate' => true,
            ]);
        }
    }
}
