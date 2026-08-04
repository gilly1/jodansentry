<?php

namespace App\Services\Mpesa;

use Exception;

class MpesaException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        private array $responseData = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
