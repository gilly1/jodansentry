<?php

namespace App\Console\Commands;

use App\Enums\PaymentBatchStatus;
use App\Jobs\ProcessPaymentBatchJob;
use App\Models\PaymentBatch;
use Illuminate\Console\Command;

class ProcessScheduledPaymentBatches extends Command
{
    protected $signature = 'payments:process-scheduled';

    protected $description = 'Process scheduled payment batches that are due';

    public function handle(): int
    {
        $dueBatches = PaymentBatch::where('status', PaymentBatchStatus::SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($dueBatches->isEmpty()) {
            $this->info('No scheduled batches due for processing.');

            return self::SUCCESS;
        }

        foreach ($dueBatches as $batch) {
            $this->info("Dispatching batch: {$batch->batch_id}");
            ProcessPaymentBatchJob::dispatch($batch->id);
        }

        $this->info("Dispatched {$dueBatches->count()} batch(es) for processing.");

        return self::SUCCESS;
    }
}
