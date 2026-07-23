<?php

namespace App\Services\Mpesa;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MpesaTokenService
{
    public function __construct(
        protected MpesaAccountResolver $accountResolver,
    ) {}

    public function token(?string $account = null): string
    {
        $account = $account ?? config('mpesa.default');
        $config = $this->accountResolver->resolve($account);
        $baseUrl = $this->accountResolver->baseUrl($account);
        $cacheKey = "mpesa_token_{$account}";

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($config, $baseUrl) {
            $response = Http::withBasicAuth($config['consumer_key'], $config['consumer_secret'])
                ->timeout(config('mpesa.timeouts.request'))
                ->connectTimeout(config('mpesa.timeouts.connect'))
                ->get($baseUrl . config('mpesa.paths.oauth'));

            if (! $response->successful()) {
                throw new Exceptions\MpesaException(
                    'Unable to retrieve M-Pesa access token: ' . $response->body()
                );
            }

            return $response->json('access_token');
        });
    }

    public function clearToken(?string $account = null): void
    {
        $account = $account ?? config('mpesa.default');
        Cache::forget("mpesa_token_{$account}");
    }
}
