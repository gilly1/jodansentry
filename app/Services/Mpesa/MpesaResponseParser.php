<?php

namespace App\Services\Mpesa;

class MpesaResponseParser
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function originatorConversationId(): ?string
    {
        return $this->data['OriginatorConversationID'] ?? null;
    }

    public function conversationId(): ?string
    {
        return $this->data['ConversationID'] ?? null;
    }

    public function responseCode(): ?string
    {
        return $this->data['ResponseCode'] ?? null;
    }

    public function responseDescription(): ?string
    {
        return $this->data['ResponseDescription'] ?? null;
    }

    public function isSuccessful(): bool
    {
        return ($this->data['ResponseCode'] ?? null) === '0';
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public static function fromCallbackResult(array $payload): self
    {
        return new self(data_get($payload, 'Result', []));
    }

    public function resultCode(): ?int
    {
        return isset($this->data['ResultCode']) ? (int) $this->data['ResultCode'] : null;
    }

    public function resultDescription(): ?string
    {
        return $this->data['ResultDesc'] ?? null;
    }

    public function transactionReceipt(): ?string
    {
        $params = data_get($this->data, 'ResultParameters.ResultParameter', []);

        return collect($params)->firstWhere('Key', 'TransactionReceipt')['Value'] ?? null;
    }
}
