<?php

return [
    'default' => env('MPESA_DEFAULT_ACCOUNT', 'salary'),

    'accounts' => [
        'salary' => [
            'environment' => env('MPESA_ENV', 'sandbox'),
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'shortcode' => env('MPESA_B2C_SHORTCODE'),
            'initiator_name' => env('MPESA_INITIATOR_NAME'),
            'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
            'command_id' => env('MPESA_COMMAND_ID', 'BusinessPayment'),
            'result_url' => env('MPESA_RESULT_URL'),
            'timeout_url' => env('MPESA_TIMEOUT_URL'),
        ],
    ],

    'base_urls' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],

    'paths' => [
        'oauth' => '/oauth/v1/generate?grant_type=client_credentials',
        'b2c' => '/mpesa/b2c/v1/paymentrequest',
        'account_balance' => '/mpesa/accountbalance/v1/query',
    ],

    'timeouts' => [
        'request' => (int) env('MPESA_REQUEST_TIMEOUT', 60),
        'connect' => (int) env('MPESA_CONNECT_TIMEOUT', 15),
    ],

    'callback_secret' => env('MPESA_CALLBACK_SECRET'),

    'max_retries' => (int) env('MPESA_MAX_RETRIES', 3),
];
