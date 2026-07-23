<?php

return [
    'allow_self_approval' => env('PAYMENTS_ALLOW_SELF_APPROVAL', false),
    'max_batch_amount' => env('PAYMENTS_MAX_BATCH_AMOUNT', 10000000),
    'max_single_payment_amount' => env('PAYMENTS_MAX_SINGLE_PAYMENT_AMOUNT', 500000),
    'phone_regex' => env('PAYMENTS_PHONE_REGEX', '/^(2547|2541)\d{8}$/'),
    'decimal_amount_policy' => env('PAYMENTS_DECIMAL_AMOUNT_POLICY', 'reject'), // reject or round
];
