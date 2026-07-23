<?php

namespace App\Services\Mpesa;

use App\Models\PaymentItem;

class MpesaPayloadBuilder
{
    public function __construct(
        protected MpesaAccountResolver $accountResolver,
    ) {}

    public function buildB2CPayload(PaymentItem $paymentItem, ?string $account = null): array
    {
        $config = $this->accountResolver->resolve($account);

        return [
            'InitiatorName' => $config['initiator_name'],
            'SecurityCredential' => $config['security_credential'],
            'CommandID' => $config['command_id'],
            'Amount' => (int) $paymentItem->amount,
            'PartyA' => $config['shortcode'],
            'PartyB' => $paymentItem->normalized_phone,
            'Remarks' => $paymentItem->narration ?: 'Salary Payment',
            'QueueTimeOutURL' => $config['timeout_url'],
            'ResultURL' => $config['result_url'],
            'Occasion' => $paymentItem->batch->batch_id,
        ];
    }
}
