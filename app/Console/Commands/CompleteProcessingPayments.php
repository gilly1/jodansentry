<?php

namespace App\Console\Commands;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use App\Models\SuccessfulTransaction;
use App\Actions\Mpesa\UpdateBatchAggregateStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteProcessingPayments extends Command
{
    protected $signature = 'payments:complete-processing
        {--batch= : Limit to a specific batch (batch_id or numeric id)}
        {--dry-run : Show what would be completed without saving}';

    protected $description = 'Manually complete processing payment items that have a receipt populated (used when the M-Pesa result callback failed). Marks items successful and records them in the phonebook.';

    public function handle(): int
    {
        $query = PaymentItem::query()
            ->where('status', PaymentItemStatus::PROCESSING->value)
            ->whereNotNull('receipt')
            ->where('receipt', '!=', '')
            ->whereHas('batch', function ($q) {
                $q->where('status', PaymentBatchStatus::PROCESSING->value);
            });

        if ($batchOption = $this->option('batch')) {
            $batch = PaymentBatch::where('batch_id', $batchOption)
                ->orWhere('id', $batchOption)
                ->first();

            if (!$batch) {
                $this->error("Batch not found: {$batchOption}");
                return self::FAILURE;
            }

            $query->where('payment_batch_id', $batch->id);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->info('No processing payment items with a populated receipt were found.');
            return self::SUCCESS;
        }

        $this->info("Found {$items->count()} payment item(s) to complete.");

        $dryRun = $this->option('dry-run');
        $completed = 0;
        $skipped = 0;
        $affectedBatchIds = [];

        foreach ($items as $item) {
            // Guard against double-processing if a transaction was already recorded.
            if (SuccessfulTransaction::where('payment_item_id', $item->id)->exists()) {
                $this->warn("Skipping item #{$item->id} ({$item->employee_name}) - transaction already recorded.");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("Would complete item #{$item->id} - {$item->employee_name} ({$item->normalized_phone}) receipt {$item->receipt}");
                $affectedBatchIds[$item->payment_batch_id] = true;
                $completed++;
                continue;
            }

            DB::transaction(function () use ($item) {
                $item->update([
                    'status' => PaymentItemStatus::SUCCESSFUL,
                    'mpesa_transaction_receipt' => $item->receipt,
                    'mpesa_result_code' => '0',
                    'mpesa_result_description' => 'Completed manually via receipt reconciliation',
                    'processed_at' => now(),
                ]);

                $this->recordContactAndTransaction($item);

                AuditLog::record(
                    'payment_completed_manually',
                    $item,
                    null,
                    [
                        'status' => $item->status->value,
                        'transaction_receipt' => $item->receipt,
                        'phone' => $item->normalized_phone,
                        'amount' => $item->amount,
                    ],
                );
            });

            $affectedBatchIds[$item->payment_batch_id] = true;
            $completed++;
            $this->line("Completed item #{$item->id} - {$item->employee_name} ({$item->normalized_phone}) receipt {$item->receipt}");
        }

        if (!$dryRun) {
            foreach (array_keys($affectedBatchIds) as $batchId) {
                $batch = PaymentBatch::find($batchId);
                if ($batch) {
                    app(UpdateBatchAggregateStatus::class)->execute($batch);
                }
            }
        }

        $this->newLine();
        $this->info("Completed: {$completed}" . ($skipped ? ", Skipped: {$skipped}" : '') . ($dryRun ? ' (dry run - nothing saved)' : ''));

        return self::SUCCESS;
    }

    private function recordContactAndTransaction(PaymentItem $item): void
    {
        $phone = $item->normalized_phone;
        $mpesaName = $item->employee_name ?: 'Unknown';

        $contact = Contact::updateOrCreate(
            ['phone_number' => $phone],
            ['mpesa_name' => $mpesaName],
        );

        SuccessfulTransaction::create([
            'contact_id' => $contact->id,
            'payment_item_id' => $item->id,
            'payment_batch_id' => $item->payment_batch_id,
            'transaction_receipt' => $item->receipt,
            'phone_number' => $phone,
            'amount' => $item->amount,
            'mpesa_result_description' => 'Completed manually via receipt reconciliation',
            'paid_at' => now(),
        ]);

        $contact->refreshTotals();
    }
}
