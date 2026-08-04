<?php

namespace App\Console\Commands;

use App\Services\Mpesa\MpesaAccountResolver;
use App\Services\Mpesa\MpesaException;
use App\Services\Mpesa\MpesaPayloadBuilder;
use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaResponseParser;
use App\Services\Mpesa\MpesaSecurityCredential;
use App\Services\Mpesa\MpesaTokenService;
use Illuminate\Console\Command;

class TestMpesaPayment extends Command
{
    protected $signature = 'mpesa:test-payment';

    protected $description = 'Send a test B2C payment of KES 10 to 0757882231';

    public function handle(
        MpesaClient $client,
        MpesaAccountResolver $accountResolver,
        MpesaSecurityCredential $credential,
        MpesaTokenService $tokenService,
        MpesaPayloadBuilder $payloadBuilder,
    ): int {
        $phone = '254757882231';
        $amount = 10;
        $account = config('mpesa.default_account');

        $this->info("=== M-Pesa B2C Test Payment ===");
        $this->info("Phone: {$phone}");
        $this->info("Amount: KES {$amount}");
        $this->info("Account: {$account}");
        $this->info("Environment: " . config('mpesa.env'));
        $this->newLine();

        // Step 1: Resolve config
        $this->info('[1/4] Resolving account configuration...');
        try {
            $config = $accountResolver->resolve($account);
            $this->info("  Shortcode: {$config['shortcode']}");
            $this->info("  Initiator: {$config['initiator_name']}");
            $this->info("  Result URL: {$config['result_url']}");
            $this->info("  Timeout URL: {$config['timeout_url']}");
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
        $this->newLine();

        // Step 2: Generate security credential
        $this->info('[2/4] Generating security credential...');
        try {
            $securityCredential = $credential->generate($account);
            $this->info("  Credential: " . substr($securityCredential, 0, 20) . '...');
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
        $this->newLine();

        // Step 3: Get OAuth token
        $this->info('[3/4] Fetching OAuth token...');
        try {
            $tokenService->clearToken($account);
            $token = $tokenService->token($account);
            if (empty($token)) {
                $this->error("  FAILED: Token is empty — check consumer key/secret.");
                return self::FAILURE;
            }
            $this->info("  Token: " . substr($token, 0, 20) . '...');
            $this->info("  Base URL: " . $accountResolver->baseUrl($account));
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
        $this->newLine();

        // Step 4: Send B2C request
        $this->info('[4/4] Sending B2C payment request...');
        $baseUrl = $accountResolver->baseUrl($account);
        $endpoint = $baseUrl . config('mpesa.api_paths.b2c');
        $this->info("  Endpoint: {$endpoint}");

        try {
            $payload = $payloadBuilder->buildB2CPayload($phone, $amount, 'Test Payment');
            $this->line("  Payload: " . json_encode($payload, JSON_PRETTY_PRINT));
            $this->newLine();

            $response = $client->sendB2CRequest($payload);
            $parser = new MpesaResponseParser($response);

            if ($parser->isSuccessful()) {
                $this->info('  SUCCESS!');
                $this->info("  Conversation ID: " . $parser->conversationId());
                $this->info("  Response: " . $parser->responseDescription());
                return self::SUCCESS;
            } else {
                $this->error("  FAILED!");
                $this->error("  Response Code: " . $parser->responseCode());
                $this->error("  Description: " . $parser->responseDescription());
                return self::FAILURE;
            }
        } catch (MpesaException $e) {
            $this->newLine();
            $this->error("  FAILED: {$e->getMessage()}");
            $this->error("  Response Data: " . json_encode($e->getResponseData(), JSON_PRETTY_PRINT));
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
