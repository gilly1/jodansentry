<?php

namespace App\Services\Mpesa;

class MpesaAccountResolver
{
    public function resolve(string $account): array
    {
        $config = config("mpesa.accounts.{$account}");

        if (!$config) {
            throw new \InvalidArgumentException("M-Pesa account '{$account}' not configured.");
        }

        return $config;
    }

    public function baseUrl(string $account): string
    {
        $env = config('mpesa.env', 'sandbox');
        return config("mpesa.base_urls.{$env}");
    }
}
