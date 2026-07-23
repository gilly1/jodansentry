<?php

namespace App\Actions\Payments;

class ValidatePaymentRows
{
    public function validate(array $row): array
    {
        $errors = [];
        $normalizedPhone = null;
        $amount = null;

        // Employee name
        $employeeName = trim($row['employee_name'] ?? '');
        if (empty($employeeName)) {
            $errors[] = 'Employee name is required.';
        } elseif (strlen($employeeName) > 255) {
            $errors[] = 'Employee name must not exceed 255 characters.';
        }

        // Phone number
        $phone = trim($row['mpesa_phone_number'] ?? '');
        if (empty($phone)) {
            $errors[] = 'MPesa Phone Number is required.';
        } else {
            $normalizedPhone = $this->normalizePhone($phone);
            if (! $normalizedPhone) {
                $errors[] = 'Invalid MPesa Phone Number format.';
            }
        }

        // Amount
        $rawAmount = $row['payment_amount'] ?? null;
        if ($rawAmount === null || $rawAmount === '') {
            $errors[] = 'Payment amount is required.';
        } elseif (! is_numeric($rawAmount)) {
            $errors[] = 'Payment amount must be numeric.';
        } elseif ((float) $rawAmount <= 0) {
            $errors[] = 'Payment amount must be greater than zero.';
        } else {
            $amount = (float) $rawAmount;

            if (config('payments.decimal_amount_policy') === 'reject' && floor($amount) != $amount) {
                $errors[] = 'Decimal amounts are not allowed.';
                $amount = null;
            }

            if ($amount !== null) {
                $maxSingle = config('payments.max_single_payment_amount');
                if ($maxSingle && $amount > $maxSingle) {
                    $errors[] = "Payment amount exceeds the maximum allowed ({$maxSingle}).";
                }
            }
        }

        return [
            'errors' => $errors,
            'normalized_phone' => $normalizedPhone,
            'amount' => $amount,
        ];
    }

    public function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // 07XXXXXXXX -> 2547XXXXXXXX
        if (preg_match('/^0(7\d{8}|1\d{8})$/', $phone)) {
            $phone = '254' . substr($phone, 1);
        }

        // +2547XXXXXXXX -> 2547XXXXXXXX
        if (preg_match('/^(\+)?254(7\d{8}|1\d{8})$/', $phone)) {
            $phone = preg_replace('/^\+/', '', $phone);
        }

        // 7XXXXXXXX -> 2547XXXXXXXX
        if (preg_match('/^7\d{8}$/', $phone)) {
            $phone = '254' . $phone;
        }

        $regex = config('payments.phone_regex', '/^(2547|2541)\d{8}$/');

        if (preg_match($regex, $phone)) {
            return $phone;
        }

        return null;
    }
}
