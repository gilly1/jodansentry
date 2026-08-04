<?php

namespace App\Services\Mpesa;

use App\Models\MpesaApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaClient
{
    public function __construct(
        private MpesaTokenService $tokenService,
        private MpesaAccountResolver $accountResolver,
    ) {}

    public function sendB2CRequest(array $payload, ?int $batchId = null, ?int $itemId = null): array
    {
        return $this->makeRequest('b2c', $payload, $batchId, $itemId);
    }

    public function sendAccountBalanceRequest(array $payload, ?int $batchId = null): array
    {
        return $this->makeRequest('account_balance', $payload, $batchId);
    }

    public function sendTransactionStatusRequest(array $payload, ?int $batchId = null): array
    {
        return $this->makeRequest('transaction_status', $payload, $batchId);
    }

    public function sendReversalRequest(array $payload, ?int $batchId = null): array
    {
        return $this->makeRequest('reversal', $payload, $batchId);
    }

    private function makeRequest(string $type, array $payload, ?int $batchId = null, ?int $itemId = null): array
    {
        $account = config('mpesa.default_account');
        $baseUrl = $this->accountResolver->baseUrl($account);
        $path = config("mpesa.api_paths.{$type}");
        $endpoint = $baseUrl . $path;

        $token = $this->tokenService->token($account);

        $this->logRequest($endpoint, $payload, $batchId, $itemId);

        try {
            $response = Http::timeout(config('mpesa.timeouts.request', 60))
                ->connectTimeout(config('mpesa.timeouts.connect', 15))
                ->withToken($token)
                ->post($endpoint, $payload);

            $responseData = $response->json() ?? [];

            $this->logResponse($endpoint, $responseData, $response->status(), $batchId, $itemId);

            if (!$response->successful()) {
                throw new MpesaException(
                    'M-Pesa API request failed: ' . ($responseData['errorMessage'] ?? 'Unknown error'),
                    $response->status(),
                    $responseData
                );
            }

            return $responseData;
        } catch (MpesaException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError($endpoint, $e->getMessage(), $batchId, $itemId);
            throw new MpesaException('M-Pesa API connection failed: ' . $e->getMessage(), 0, []);
        }
    }

    private function logRequest(string $endpoint, array $payload, ?int $batchId, ?int $itemId): void
    {
        MpesaApiLog::create([
            'payment_batch_id' => $batchId,
            'payment_item_id' => $itemId,
            'direction' => 'request',
            'endpoint' => $endpoint,
            'payload' => $payload,
            'masked_payload' => $this->maskSensitiveFields($payload),
        ]);

        Log::channel('mpesa')->info('M-Pesa API Request', [
            'endpoint' => $endpoint,
            'payload' => $this->maskSensitiveFields($payload),
        ]);
    }

    private function logResponse(string $endpoint, array $data, int $status, ?int $batchId, ?int $itemId): void
    {
        MpesaApiLog::create([
            'payment_batch_id' => $batchId,
            'payment_item_id' => $itemId,
            'direction' => 'response',
            'endpoint' => $endpoint,
            'http_status' => $status,
            'payload' => $data,
            'masked_payload' => $data,
        ]);

        Log::channel('mpesa')->info('M-Pesa API Response', [
            'endpoint' => $endpoint,
            'status' => $status,
            'response' => $data,
        ]);
    }

    private function logError(string $endpoint, string $error, ?int $batchId, ?int $itemId): void
    {
        MpesaApiLog::create([
            'payment_batch_id' => $batchId,
            'payment_item_id' => $itemId,
            'direction' => 'response',
            'endpoint' => $endpoint,
            'error_message' => $error,
        ]);

        Log::channel('mpesa')->error('M-Pesa API Error', [
            'endpoint' => $endpoint,
            'error' => $error,
        ]);
    }

    private function maskSensitiveFields(array $payload): array
    {
        $sensitiveKeys = ['SecurityCredential', 'Credential', 'Password', 'password'];
        $masked = $payload;

        foreach ($sensitiveKeys as $key) {
            if (isset($masked[$key])) {
                $masked[$key] = '***MASKED***';
            }
        }

        return $masked;
    }
}
