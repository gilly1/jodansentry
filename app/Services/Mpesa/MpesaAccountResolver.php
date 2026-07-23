<?php

namespace App\Services\Mpesa;

class MpesaAccountResolver
{
    public function resolve(?string $account = null): array
    {
        $account = $account ?? config('mpesa.default');

        $config = config("mpesa.accounts.{$account}");

        if (! $config) {
            throw new \InvalidArgumentException("M-Pesa account [{$account}] is not configured.");
        }

        return $config;
    }

    public function baseUrl(?string $account = null): string
    {
        $config = $this->resolve($account);
        $environment = $config['environment'] ?? 'sandbox';

        return config("mpesa.base_urls.{$environment}");
    }
}
