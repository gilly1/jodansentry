<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportPaymentBatch
{
    public function handle(UploadedFile $file, User $user): PaymentBatch
    {
        return DB::transaction(function () use ($file, $user) {
            $rows = $this->readExcel($file);

            $fileChecksum = hash_file('sha256', $file->getRealPath());
            $storedPath = $file->store('payment-batches', 'local');

            $batch = PaymentBatch::create([
                'batch_id' => $this->generateBatchId(),
                'uploaded_by' => $user->id,
                'status' => PaymentBatchStatus::UPLOADED,
                'source_file_path' => $storedPath,
                'source_file_name' => $file->getClientOriginalName(),
                'file_checksum' => $fileChecksum,
            ]);

            $validator = app(ValidatePaymentRows::class);

            foreach ($rows as $index => $row) {
                $validation = $validator->validate($row);

                PaymentItem::create([
                    'payment_batch_id' => $batch->id,
                    'row_number' => $index + 2,
                    'employee_name' => trim($row['employee_name'] ?? ''),
                    'employee_code' => trim($row['employee_code'] ?? ''),
                    'phone_number_raw' => trim($row['mpesa_phone_number'] ?? ''),
                    'normalized_phone' => $validation['normalized_phone'],
                    'amount' => $validation['amount'],
                    'narration' => trim($row['narration'] ?? ''),
                    'status' => empty($validation['errors'])
                        ? PaymentItemStatus::VALIDATED
                        : PaymentItemStatus::INVALID,
                    'validation_errors' => $validation['errors'] ?: null,
                ]);
            }

            $batch->refreshTotals();

            AuditLog::record('batch_uploaded', $batch, $user);

            return $batch->fresh();
        });
    }

    protected function readExcel(UploadedFile $file): array
    {
        $rows = [];
        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return [];
        }

        $sheet = $data[0];
        $headers = array_map(fn ($h) => strtolower(str_replace(' ', '_', trim($h ?? ''))), $sheet[0]);

        for ($i = 1; $i < count($sheet); $i++) {
            $row = $sheet[$i];

            if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $mapped = [];
            foreach ($headers as $colIndex => $header) {
                $mapped[$header] = $row[$colIndex] ?? null;
            }
            $rows[] = $mapped;
        }

        return $rows;
    }

    protected function generateBatchId(): string
    {
        $date = now()->format('Ymd');
        $prefix = "SAL-{$date}-";

        $lastBatch = PaymentBatch::where('batch_id', 'like', "{$prefix}%")
            ->orderByDesc('batch_id')
            ->lockForUpdate()
            ->first();

        if ($lastBatch) {
            $lastSequence = (int) substr($lastBatch->batch_id, -6);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 6, '0', STR_PAD_LEFT);
    }
}
