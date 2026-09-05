<?php

namespace App\Console\Commands;

use App\Actions\Mpesa\UpdateBatchAggregateStatus;
use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\MpesaApiLog;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use App\Models\SuccessfulTransaction;
use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaPayloadBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteProcessingPayments extends Command
{
    protected $signature = 'payments:complete-processing
        {--batch= : Limit to a specific batch (batch_id or numeric id)}
        {--timeout=120 : Seconds to wait for each transaction status result callback}
        {--poll-interval=5 : Seconds between callback polls}
        {--dry-run : List the items that would be queried without contacting M-Pesa}';

    protected $description = 'Reconcile processing payment items by querying the M-Pesa Transaction Status API using the populated receipt, waiting for the result callback, and only then completing the record and saving it to the phonebook.';

    public function handle(
        MpesaPayloadBuilder $builder,
        MpesaClient $client,
    ): int {
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

        $this->info("Found {$items->count()} payment item(s) to reconcile.");

        if ($this->option('dry-run')) {
            foreach ($items as $item) {
                $this->line("Would query TransactionID {$item->receipt} for item #{$item->id} - {$item->employee_name} ({$item->normalized_phone})");
            }
            $this->info('Dry run - no requests were sent.');
            return self::SUCCESS;
        }

        $timeout = max(0, (int) $this->option('timeout'));
        $pollInterval = max(1, (int) $this->option('poll-interval'));

        $completed = 0;
        $failed = 0;
        $pending = 0;
        $affectedBatchIds = [];

        foreach ($items as $item) {
            $this->line("Querying transaction status for item #{$item->id} - {$item->employee_name} (receipt {$item->receipt})...");

            // Baseline so we only match callbacks that arrive after this query.
            $sinceLogId = (int) MpesaApiLog::max('id');

            try {
                $payload = $builder->buildTransactionStatusPayload($item->receipt);
                $response = $client->sendTransactionStatusRequest($payload, $item->payment_batch_id);
            } catch (\Throwable $e) {
                $this->error("  Request failed: {$e->getMessage()}");
                $pending++;
                continue;
            }

            if (($response['ResponseCode'] ?? '') !== '0') {
                $this->warn('  Query not accepted: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
                $pending++;
                continue;
            }

            $conversationId = $response['ConversationID'] ?? null;
            $originatorId = $response['OriginatorConversationID'] ?? ($payload['OriginatorConversationID'] ?? null);

            // Persist the correlation IDs so the incoming callback can be matched.
            $item->update([
                'mpesa_conversation_id' => $conversationId ?: $item->mpesa_conversation_id,
                'mpesa_originator_conversation_id' => $originatorId ?: $item->mpesa_originator_conversation_id,
            ]);

            $callback = $this->waitForCallback($conversationId, $originatorId, $sinceLogId, $timeout, $pollInterval);

            if (!$callback) {
                $this->warn("  No result callback received within {$timeout}s. Item left as processing.");
                $pending++;
                continue;
            }

            $result = $callback['Result'] ?? $callback;
            $resultCode = (string) ($result['ResultCode'] ?? '');
            $resultDesc = $result['ResultDesc'] ?? '';
            $parsed = $this->parseResultParameters($result['ResultParameters']['ResultParameter'] ?? []);
            $txnStatus = $parsed['TransactionStatus'] ?? null;

            $isCompleted = $resultCode === '0' && strcasecmp((string) $txnStatus, 'Completed') === 0;

            if ($isCompleted) {
                DB::transaction(function () use ($item, $parsed, $resultDesc, $callback) {
                    $item->update([
                        'status' => PaymentItemStatus::SUCCESSFUL,
                        'mpesa_transaction_receipt' => $parsed['ReceiptNo'] ?? $item->receipt,
                        'mpesa_result_code' => '0',
                        'mpesa_result_description' => $resultDesc ?: 'Completed via transaction status reconciliation',
                        'callback_payload' => $callback,
                        'processed_at' => now(),
                    ]);

                    $this->recordContactAndTransaction($item, $parsed, $resultDesc);

                    AuditLog::record(
                        'payment_reconciled_via_status',
                        $item,
                        null,
                        [
                            'status' => $item->status->value,
                            'transaction_receipt' => $item->mpesa_transaction_receipt,
                            'transaction_status' => 'Completed',
                            'phone' => $item->normalized_phone,
                            'amount' => $item->amount,
                        ],
                    );
                });

                $affectedBatchIds[$item->payment_batch_id] = true;
                $completed++;
                $this->info("  Completed. Receipt {$item->mpesa_transaction_receipt} recorded to phonebook.");
                continue;
            }

            // Callback arrived but the transaction is not a completed payment.
            $item->update([
                'status' => PaymentItemStatus::FAILED,
                'mpesa_result_code' => $resultCode,
                'mpesa_result_description' => $resultDesc ?: ('Transaction status: ' . ($txnStatus ?? 'unknown')),
                'callback_payload' => $callback,
                'failed_at' => now(),
            ]);

            AuditLog::record(
                'payment_reconciled_via_status',
                $item,
                null,
                [
                    'status' => $item->status->value,
                    'transaction_status' => $txnStatus,
                    'result_code' => $resultCode,
                    'result_description' => $resultDesc,
                    'phone' => $item->normalized_phone,
                    'amount' => $item->amount,
                ],
            );

            $affectedBatchIds[$item->payment_batch_id] = true;
            $failed++;
            $this->warn('  Not completed (status: ' . ($txnStatus ?? 'unknown') . '). Item marked failed.');
        }

        foreach (array_keys($affectedBatchIds) as $batchId) {
            $batch = PaymentBatch::find($batchId);
            if ($batch) {
                app(UpdateBatchAggregateStatus::class)->execute($batch);
            }
        }

        $this->newLine();
        $this->info("Completed: {$completed}, Failed: {$failed}, Still pending: {$pending}");

        return self::SUCCESS;
    }

    private function waitForCallback(?string $conversationId, ?string $originatorId, int $sinceLogId, int $timeout, int $pollInterval): ?array
    {
        if (!$conversationId && !$originatorId) {
            return null;
        }

        $deadline = now()->addSeconds($timeout);

        do {
            $log = MpesaApiLog::query()
                ->where('direction', 'callback')
                ->where('endpoint', '/transaction-status/result')
                ->where('id', '>', $sinceLogId)
                ->where(function ($q) use ($conversationId, $originatorId) {
                    if ($originatorId) {
                        $q->where('payload->Result->OriginatorConversationID', $originatorId);
                    }
                    if ($conversationId) {
                        $q->orWhere('payload->Result->ConversationID', $conversationId);
                    }
                })
                ->latest('id')
                ->first();

            if ($log) {
                return $log->payload;
            }

            if (now()->greaterThanOrEqualTo($deadline)) {
                break;
            }

            sleep($pollInterval);
        } while (true);

        return null;
    }

    private function parseResultParameters(array $params): array
    {
        $mapped = [];

        foreach ($params as $param) {
            $key = $param['Key'] ?? '';
            if ($key !== '') {
                $mapped[$key] = $param['Value'] ?? null;
            }
        }

        return $mapped;
    }

    private function recordContactAndTransaction(PaymentItem $item, array $parsed, string $resultDesc): void
    {
        $phone = $item->normalized_phone;
        $mpesaName = $this->extractName($parsed['CreditPartyName'] ?? null) ?: ($item->employee_name ?: 'Unknown');

        $contact = Contact::updateOrCreate(
            ['phone_number' => $phone],
            ['mpesa_name' => $mpesaName],
        );

        SuccessfulTransaction::updateOrCreate(
            ['payment_item_id' => $item->id],
            [
                'contact_id' => $contact->id,
                'payment_batch_id' => $item->payment_batch_id,
                'transaction_receipt' => $item->mpesa_transaction_receipt,
                'phone_number' => $phone,
                'amount' => $item->amount,
                'mpesa_result_description' => $resultDesc ?: 'Completed via transaction status reconciliation',
                'paid_at' => now(),
            ],
        );

        $contact->refreshTotals();
    }

    private function extractName(?string $creditPartyName): ?string
    {
        if (!$creditPartyName) {
            return null;
        }

        // Typically "254XXXXXXXXX - Name"; keep only the name portion.
        if (str_contains($creditPartyName, ' - ')) {
            return trim(explode(' - ', $creditPartyName, 2)[1]);
        }

        return trim($creditPartyName);
    }
}
