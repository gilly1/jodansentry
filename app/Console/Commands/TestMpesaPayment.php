<?php

namespace App\Console\Commands;

use App\Services\Mpesa\MpesaAccountResolver;
use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaTokenService;
use Illuminate\Console\Command;

class TestMpesaPayment extends Command
{
    protected $signature = 'mpesa:test-payment';

    protected $description = 'Send a test B2C payment of KES 1 to 0757882231';

    public function handle(MpesaClient $client, MpesaAccountResolver $accountResolver): int
    {
        $phone = '254757882231';
        $amount = 1;
        $account = config('mpesa.default');

        $this->info("=== M-Pesa B2C Test Payment ===");
        $this->info("Phone: {$phone}");
        $this->info("Amount: KES {$amount}");
        $this->info("Account: {$account}");
        $this->info("Environment: " . config("mpesa.accounts.{$account}.environment"));
        $this->newLine();

        // Step 1: Resolve config
        $this->info('[1/3] Resolving account configuration...');
        try {
            $config = $accountResolver->resolve($account);
            $this->info("  Shortcode: {$config['shortcode']}");
            $this->info("  Initiator: {$config['initiator_name']}");
            $this->info("  Command:   {$config['command_id']}");
            $this->info("  Result URL: {$config['result_url']}");
            $this->info("  Timeout URL: {$config['timeout_url']}");
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
        $this->newLine();

        // Step 2: Get OAuth token
        $this->info('[2/3] Fetching OAuth token...');
        try {
            $tokenService = app(MpesaTokenService::class);
            $tokenService->clearToken($account);
            $token = $tokenService->token($account);
            $this->info("  Token: " . substr($token, 0, 20) . '...');
        } catch (\Throwable $e) {
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
        $this->newLine();

        // Step 3: Send B2C request
        $this->info('[3/3] Sending B2C payment request...');
        $payload = [
            'InitiatorName' => $config['initiator_name'],
            'SecurityCredential' => $config['security_credential'],
            'CommandID' => $config['command_id'],
            'Amount' => $amount,
            'PartyA' => $config['shortcode'],
            'PartyB' => $phone,
            'Remarks' => 'Test Payment',
            'QueueTimeOutURL' => $config['timeout_url'],
            'ResultURL' => $config['result_url'],
            'Occasion' => 'TEST-' . now()->format('YmdHis'),
        ];

        $this->line("  Payload: " . json_encode(array_diff_key($payload, ['SecurityCredential' => '']), JSON_PRETTY_PRINT));
        $this->newLine();

        try {
            $response = $client->sendB2CRequest($payload, $account);
            $this->newLine();
            $this->info('  SUCCESS!');
            $this->info("  Conversation ID: " . $response->conversationId());
            $this->info("  Originator Conversation ID: " . $response->originatorConversationId());
            $this->info("  Response Description: " . $response->responseDescription());
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("  FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
