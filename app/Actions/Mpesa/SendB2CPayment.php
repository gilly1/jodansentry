<?php

namespace App\Actions\Mpesa;

use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaPayloadBuilder;
use App\Services\Mpesa\MpesaResponseParser;

class SendB2CPayment
{
    public function __construct(
        private MpesaClient $client,
        private MpesaPayloadBuilder $payloadBuilder,
    ) {}

    public function execute(string $phone, float $amount, string $remarks, ?int $batchId = null, ?int $itemId = null): MpesaResponseParser
    {
        $payload = $this->payloadBuilder->buildB2CPayload($phone, $amount, $remarks);

        $response = $this->client->sendB2CRequest($payload, $batchId, $itemId);

        return new MpesaResponseParser($response);
    }
}
