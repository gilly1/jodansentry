<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;

class SchedulePaymentBatch
{
    public function execute(PaymentBatch $batch, string $scheduledAt): void
    {
        $batch->update([
            'status' => PaymentBatchStatus::SCHEDULED,
            'scheduled_at' => $scheduledAt,
        ]);

        AuditLog::record('batch_scheduled', $batch, null, ['scheduled_at' => $scheduledAt]);
    }
}
