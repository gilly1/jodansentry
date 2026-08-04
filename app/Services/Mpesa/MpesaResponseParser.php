<?php

namespace App\Services\Mpesa;

class MpesaResponseParser
{
    public function __construct(private array $data) {}

    public function originatorConversationId(): ?string
    {
        return $this->data['OriginatorConversationID']
            ?? $this->data['Result']['OriginatorConversationID']
            ?? null;
    }

    public function conversationId(): ?string
    {
        return $this->data['ConversationID']
            ?? $this->data['Result']['ConversationID']
            ?? null;
    }

    public function responseCode(): ?string
    {
        return $this->data['ResponseCode'] ?? null;
    }

    public function responseDescription(): ?string
    {
        return $this->data['ResponseDescription'] ?? null;
    }

    public function resultCode(): ?string
    {
        return $this->data['Result']['ResultCode'] ?? $this->data['ResultCode'] ?? null;
    }

    public function resultDescription(): ?string
    {
        return $this->data['Result']['ResultDesc'] ?? $this->data['ResultDesc'] ?? null;
    }

    public function transactionReceipt(): ?string
    {
        return $this->resultParameter('TransactionReceipt');
    }

    public function receiverPartyPublicName(): ?string
    {
        $value = $this->resultParameter('ReceiverPartyPublicName');

        if (!$value) {
            return null;
        }

        // Format is typically "254XXXXXXXXX - Name", extract just the name
        if (str_contains($value, ' - ')) {
            return trim(explode(' - ', $value, 2)[1]);
        }

        return trim($value);
    }

    public function transactionAmount(): ?string
    {
        return $this->resultParameter('TransactionAmount');
    }

    private function resultParameter(string $key): ?string
    {
        $params = $this->data['Result']['ResultParameters']['ResultParameter'] ?? [];

        foreach ($params as $param) {
            if (($param['Key'] ?? '') === $key) {
                return $param['Value'] ?? null;
            }
        }

        return null;
    }

    public function isSuccessful(): bool
    {
        return $this->responseCode() === '0' || $this->resultCode() === '0';
    }

    public function raw(): array
    {
        return $this->data;
    }
}
