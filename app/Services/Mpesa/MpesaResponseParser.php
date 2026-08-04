<?php

namespace App\Services\Mpesa;

class MpesaResponseParser
{
    public function __construct(private array $data) {}

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
        $params = $this->data['Result']['ResultParameters']['ResultParameter'] ?? [];

        foreach ($params as $param) {
            if ($param['Key'] === 'TransactionReceipt') {
                return $param['Value'];
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
