<?php

namespace App\Actions\Payments;

class ValidatePaymentRows
{
    public function validateRow(array $row): array
    {
        $errors = [];

        $employeeName = trim($row['employee_name'] ?? $row['name'] ?? '');
        $employeeCode = trim($row['employee_code'] ?? $row['code'] ?? $row['id'] ?? '');
        $phoneRaw = trim($row['phone_number'] ?? $row['phone'] ?? $row['mobile'] ?? '');
        $amount = $row['amount'] ?? $row['salary'] ?? 0;
        $narration = trim($row['narration'] ?? $row['remarks'] ?? $row['description'] ?? '');

        // Validate employee name
        if (empty($employeeName)) {
            $errors[] = 'Employee name is required';
        }

        // Validate phone number
        $normalizedPhone = $this->normalizePhone($phoneRaw);
        if (empty($phoneRaw)) {
            $errors[] = 'Phone number is required';
        } elseif (!$normalizedPhone) {
            $errors[] = 'Invalid phone number format';
        }

        // Validate amount
        $amount = is_numeric($amount) ? (float) $amount : 0;
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than zero';
        }

        $maxAmount = config('payments.max_single_payment_amount', 150000);
        if ($amount > $maxAmount) {
            $errors[] = "Amount exceeds maximum allowed ({$maxAmount})";
        }

        $decimalPolicy = config('payments.decimal_amount_policy', 'reject');
        if (floor($amount) != $amount && $decimalPolicy === 'reject') {
            $errors[] = 'Decimal amounts are not allowed';
        } elseif (floor($amount) != $amount && $decimalPolicy === 'round') {
            $amount = round($amount);
        }

        return [
            'employee_name' => $employeeName,
            'employee_code' => $employeeCode,
            'phone_raw' => $phoneRaw,
            'normalized_phone' => $normalizedPhone,
            'amount' => $amount,
            'narration' => $narration ?: 'Salary Payment',
            'errors' => $errors,
        ];
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Remove leading +
        $phone = ltrim($phone, '+');

        // Handle various formats
        if (preg_match('/^254[17]\d{8}$/', $phone)) {
            return $phone; // Already normalized 254XXXXXXXXX
        }

        if (preg_match('/^0([17]\d{8})$/', $phone, $matches)) {
            return '254' . $matches[1]; // 07/01 -> 254...
        }

        if (preg_match('/^([17]\d{8})$/', $phone, $matches)) {
            return '254' . $matches[1]; // 7... or 1... -> 254...
        }

        return null;
    }
}
