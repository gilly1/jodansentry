<?php

return [
    'allow_self_approval' => env('PAYMENTS_ALLOW_SELF_APPROVAL', false),
    'max_batch_amount' => env('PAYMENTS_MAX_BATCH_AMOUNT', 5000000),
    'max_single_payment_amount' => env('PAYMENTS_MAX_SINGLE_AMOUNT', 150000),
    'phone_regex' => '/^(254|0|\\+254)?[17]\d{8}$/',
    'decimal_amount_policy' => env('PAYMENTS_DECIMAL_POLICY', 'reject'), // reject or round
];
