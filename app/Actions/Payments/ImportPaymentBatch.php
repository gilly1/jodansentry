<?php

namespace App\Actions\Payments;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportPaymentBatch
{
    public function __construct(private ValidatePaymentRows $validator) {}

    public function execute(UploadedFile $file, int $userId): PaymentBatch
    {
        $rows = $this->readFile($file);

        $batch = PaymentBatch::create([
            'batch_id' => $this->generateBatchId(),
            'uploaded_by' => $userId,
            'status' => PaymentBatchStatus::UPLOADED,
            'original_filename' => $file->getClientOriginalName(),
            'file_checksum' => md5_file($file->getRealPath()),
            'mpesa_account' => config('mpesa.default_account'),
        ]);

        $storedPath = $file->store('batches', 'local');
        $batch->update(['stored_filepath' => $storedPath]);

        $validCount = 0;
        $invalidCount = 0;
        $totalAmount = 0;

        foreach ($rows as $row) {
            $result = $this->validator->validateRow($row);

            $status = empty($result['errors'])
                ? PaymentItemStatus::VALIDATED
                : PaymentItemStatus::INVALID;

            if ($status === PaymentItemStatus::VALIDATED) {
                $validCount++;
                $totalAmount += $result['amount'];
            } else {
                $invalidCount++;
            }

            PaymentItem::create([
                'payment_batch_id' => $batch->id,
                'employee_name' => $result['employee_name'],
                'employee_code' => $result['employee_code'],
                'phone_number_raw' => $result['phone_raw'],
                'normalized_phone' => $result['normalized_phone'],
                'amount' => $result['amount'],
                'narration' => $result['narration'],
                'status' => $status,
                'validation_errors' => $result['errors'] ?: null,
            ]);
        }

        $batch->update([
            'total_records' => count($rows),
            'valid_records' => $validCount,
            'invalid_records' => $invalidCount,
            'total_amount' => $totalAmount,
        ]);

        AuditLog::record('batch_uploaded', $batch, null, [
            'total_records' => count($rows),
            'valid_records' => $validCount,
            'invalid_records' => $invalidCount,
            'total_amount' => $totalAmount,
        ]);

        Log::info('Payment batch imported', ['batch_id' => $batch->batch_id, 'records' => count($rows)]);

        return $batch;
    }

    private function readFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        $data = Excel::toArray(null, $file);

        if (empty($data) || empty($data[0])) {
            return [];
        }

        $sheet = $data[0];
        $headers = array_map(fn($h) => strtolower(trim($h ?? '')), $sheet[0]);

        for ($i = 1; $i < count($sheet); $i++) {
            $row = $sheet[$i];
            if (empty(array_filter($row))) continue;

            $mapped = [];
            foreach ($headers as $index => $header) {
                $mapped[$header] = $row[$index] ?? null;
            }
            $rows[] = $mapped;
        }

        return $rows;
    }

    private function generateBatchId(): string
    {
        return 'SAL-' . now()->format('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    }
}
