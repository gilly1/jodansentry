<?php

return [
    'env' => env('MPESA_ENV', 'sandbox'),
    'default_account' => env('MPESA_DEFAULT_ACCOUNT', 'salary'),

    'accounts' => [
        'salary' => [
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'shortcode' => env('MPESA_B2C_SHORTCODE'),
            'initiator_name' => env('MPESA_INITIATOR_NAME'),
            'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),
            'result_url' => env('MPESA_RESULT_URL'),
            'timeout_url' => env('MPESA_TIMEOUT_URL'),
        ],
    ],

    'certificates' => [
        'sandbox' => storage_path('app/mpesa/SandboxCertificate.cer'),
        'production' => storage_path('app/mpesa/ProductionCertificate.cer'),
    ],

    'base_urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],

    'api_paths' => [
        'oauth' => '/oauth/v1/generate?grant_type=client_credentials',
        'b2c' => '/mpesa/b2c/v3/paymentrequest',
        'account_balance' => '/mpesa/accountbalance/v1/query',
        'transaction_status' => '/mpesa/transactionstatus/v1/query',
        'reversal' => '/mpesa/reversal/v1/request',
    ],

    'callbacks' => [
        'account_balance_result' => env('MPESA_BALANCE_RESULT_URL'),
        'account_balance_timeout' => env('MPESA_BALANCE_TIMEOUT_URL'),
        'transaction_status_result' => env('MPESA_TXN_STATUS_RESULT_URL'),
        'transaction_status_timeout' => env('MPESA_TXN_STATUS_TIMEOUT_URL'),
        'reversal_result' => env('MPESA_REVERSAL_RESULT_URL'),
        'reversal_timeout' => env('MPESA_REVERSAL_TIMEOUT_URL'),
    ],

    'timeouts' => [
        'request' => 60,
        'connect' => 15,
    ],

    'max_retries' => env('MPESA_MAX_RETRIES', 3),
];
