<?php

namespace App\Services\Mpesa;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaTokenService
{
    public function __construct(private MpesaAccountResolver $accountResolver) {}

    public function token(string $account): string
    {
        $cacheKey = "mpesa_token_{$account}";

        return Cache::remember($cacheKey, 3300, function () use ($account) {
            $config = $this->accountResolver->resolve($account);
            $baseUrl = $this->accountResolver->baseUrl($account);
            $url = $baseUrl . config('mpesa.api_paths.oauth');

            $response = Http::withBasicAuth($config['consumer_key'], $config['consumer_secret'])
                ->get($url);

            if (!$response->successful()) {
                Log::channel('mpesa')->error('Token request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new MpesaException('Failed to get M-Pesa access token', $response->status(), []);
            }

            $data = $response->json();
            return $data['access_token'];
        });
    }

    public function clearToken(string $account): void
    {
        Cache::forget("mpesa_token_{$account}");
    }
}
