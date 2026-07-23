<?php

namespace App\Actions\Mpesa;

use App\Models\PaymentItem;
use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaPayloadBuilder;
use App\Services\Mpesa\MpesaResponseParser;

class SendB2CPayment
{
    public function __construct(
        protected MpesaClient $client,
        protected MpesaPayloadBuilder $payloadBuilder,
    ) {}

    public function handle(PaymentItem $item, ?string $account = null): MpesaResponseParser
    {
        $payload = $this->payloadBuilder->buildB2CPayload($item, $account);

        return $this->client->sendB2CRequest(
            $payload,
            $account,
            $item->id,
            $item->payment_batch_id,
        );
    }
}
