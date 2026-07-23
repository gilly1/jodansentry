<?php

namespace App\Services\Mpesa;

use App\Models\MpesaApiLog;
use App\Services\Mpesa\Exceptions\MpesaException;
use Illuminate\Support\Facades\Http;

class MpesaClient
{
    public function __construct(
        protected MpesaTokenService $tokenService,
        protected MpesaAccountResolver $accountResolver,
    ) {}

    public function sendB2CRequest(array $payload, ?string $account = null, ?int $paymentItemId = null, ?int $paymentBatchId = null): MpesaResponseParser
    {
        $account = $account ?? config('mpesa.default');
        $baseUrl = $this->accountResolver->baseUrl($account);
        $endpoint = $baseUrl . config('mpesa.paths.b2c');

        $maskedPayload = $this->maskSensitiveFields($payload);

        MpesaApiLog::create([
            'payment_item_id' => $paymentItemId,
            'payment_batch_id' => $paymentBatchId,
            'direction' => 'request',
            'endpoint' => $endpoint,
            'payload' => $payload,
            'masked_payload' => $maskedPayload,
        ]);

        try {
            $token = $this->tokenService->token($account);

            $response = Http::withToken($token)
                ->timeout(config('mpesa.timeouts.request'))
                ->connectTimeout(config('mpesa.timeouts.connect'))
                ->post($endpoint, $payload);

            $responseData = $response->json() ?? [];

            MpesaApiLog::create([
                'payment_item_id' => $paymentItemId,
                'payment_batch_id' => $paymentBatchId,
                'direction' => 'response',
                'endpoint' => $endpoint,
                'http_status' => $response->status(),
                'payload' => $responseData,
                'masked_payload' => $this->maskSensitiveFields($responseData),
            ]);

            if (! $response->successful()) {
                throw new MpesaException(
                    'M-Pesa B2C request failed: ' . ($responseData['errorMessage'] ?? $response->body()),
                    $response->status(),
                    responseData: $responseData,
                );
            }

            return new MpesaResponseParser($responseData);
        } catch (MpesaException $e) {
            throw $e;
        } catch (\Throwable $e) {
            MpesaApiLog::create([
                'payment_item_id' => $paymentItemId,
                'payment_batch_id' => $paymentBatchId,
                'direction' => 'response',
                'endpoint' => $endpoint,
                'error_message' => $e->getMessage(),
            ]);

            throw new MpesaException('M-Pesa B2C request error: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function maskSensitiveFields(array $data): array
    {
        $sensitiveKeys = ['SecurityCredential', 'consumer_secret', 'consumer_key', 'InitiatorPassword'];

        $masked = $data;
        foreach ($sensitiveKeys as $key) {
            if (isset($masked[$key])) {
                $masked[$key] = '***MASKED***';
            }
        }

        return $masked;
    }
}
