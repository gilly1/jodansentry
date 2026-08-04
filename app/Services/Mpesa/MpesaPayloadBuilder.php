<?php

namespace App\Services\Mpesa;

class MpesaPayloadBuilder
{
    public function __construct(
        private MpesaAccountResolver $accountResolver,
        private MpesaSecurityCredential $securityCredential,
    ) {}

    public function buildB2CPayload(string $phone, float $amount, string $remarks, string $occasion = ''): array
    {
        $account = config('mpesa.default_account');
        $config = $this->accountResolver->resolve($account);

        return [
            'OriginatorConversationID' => $this->generateOriginatorId(),
            'InitiatorName' => $config['initiator_name'],
            'SecurityCredential' => $this->securityCredential->generate($account),
            'CommandID' => 'BusinessPayment',
            'Amount' => (int) $amount,
            'PartyA' => $config['shortcode'],
            'PartyB' => $phone,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $config['timeout_url'],
            'ResultURL' => $config['result_url'],
            'Occasion' => $occasion,
        ];
    }

    public function buildBalancePayload(): array
    {
        $account = config('mpesa.default_account');
        $config = $this->accountResolver->resolve($account);

        return [
            'Initiator' => $config['initiator_name'],
            'SecurityCredential' => $this->securityCredential->generate($account),
            'CommandID' => 'AccountBalance',
            'PartyA' => $config['shortcode'],
            'IdentifierType' => '4',
            'Remarks' => 'Account balance query',
            'QueueTimeOutURL' => config('mpesa.callbacks.account_balance_timeout'),
            'ResultURL' => config('mpesa.callbacks.account_balance_result'),
        ];
    }

    public function buildTransactionStatusPayload(string $transactionId): array
    {
        $account = config('mpesa.default_account');
        $config = $this->accountResolver->resolve($account);

        return [
            'Initiator' => $config['initiator_name'],
            'SecurityCredential' => $this->securityCredential->generate($account),
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $transactionId,
            'PartyA' => $config['shortcode'],
            'IdentifierType' => '4',
            'Remarks' => 'Transaction status query',
            'QueueTimeOutURL' => config('mpesa.callbacks.transaction_status_timeout'),
            'ResultURL' => config('mpesa.callbacks.transaction_status_result'),
            'Occasion' => '',
        ];
    }

    private function generateOriginatorId(): string
    {
        return 'SAL-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }
}
