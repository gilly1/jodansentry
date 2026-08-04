<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPaymentBatchJob;
use App\Models\PaymentBatch;
use Illuminate\Console\Command;

class ProcessScheduledPaymentBatches extends Command
{
    protected $signature = 'payments:process-scheduled';
    protected $description = 'Process payment batches that are scheduled for now or earlier';

    public function handle(): int
    {
        $batches = PaymentBatch::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($batches->isEmpty()) {
            $this->info('No scheduled batches to process.');
            return 0;
        }

        foreach ($batches as $batch) {
            ProcessPaymentBatchJob::dispatch($batch->id);
            $this->info("Dispatched batch: {$batch->batch_id}");
        }

        $this->info("Dispatched {$batches->count()} batch(es) for processing.");
        return 0;
    }
}
