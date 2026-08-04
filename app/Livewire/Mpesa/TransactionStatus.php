<?php

namespace App\Livewire\Mpesa;

use App\Models\MpesaApiLog;
use App\Models\PaymentItem;
use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaPayloadBuilder;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TransactionStatus extends Component
{
    public string $transactionId = '';
    public ?array $statusResult = null;
    public ?string $error = null;
    public bool $loading = false;
    public ?string $conversationId = null;
    public ?array $callbackResult = null;

    public function query()
    {
        $this->validate(['transactionId' => 'required|string|min:5']);

        $this->loading = true;
        $this->error = null;
        $this->statusResult = null;
        $this->callbackResult = null;
        $this->conversationId = null;

        try {
            $builder = app(MpesaPayloadBuilder::class);
            $client = app(MpesaClient::class);

            $payload = $builder->buildTransactionStatusPayload($this->transactionId);
            $response = $client->sendTransactionStatusRequest($payload);

            if (($response['ResponseCode'] ?? '') === '0') {
                $this->statusResult = $response;
                $this->conversationId = $response['ConversationID'] ?? null;
            } else {
                $this->error = $response['ResponseDescription'] ?? 'Query failed';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Log::channel('mpesa')->error('Transaction status query failed', ['error' => $e->getMessage()]);
        } finally {
            $this->loading = false;
        }
    }

    public function checkCallback()
    {
        if (!$this->transactionId) {
            return;
        }

        // Look for callback log matching this transaction
        $callbackLog = MpesaApiLog::where('direction', 'callback')
            ->where('endpoint', '/transaction-status/result')
            ->where(function ($query) {
                $query->where('payload->Result->TransactionID', $this->transactionId)
                    ->orWhere('payload', 'like', '%"ReceiptNo"%' . $this->transactionId . '%');
            })
            ->latest()
            ->first();

        // Fallback: search by ConversationID from the initial response
        if (!$callbackLog && $this->conversationId) {
            $callbackLog = MpesaApiLog::where('direction', 'callback')
                ->where('endpoint', '/transaction-status/result')
                ->where('payload->Result->ConversationID', $this->conversationId)
                ->latest()
                ->first();
        }

        if ($callbackLog) {
            $result = $callbackLog->payload['Result'] ?? $callbackLog->payload;
            $this->callbackResult = $this->parseCallbackResult($result);
        }
    }

    private function parseCallbackResult(array $result): array
    {
        $parsed = [
            'result_code' => (string) ($result['ResultCode'] ?? ''),
            'result_desc' => $result['ResultDesc'] ?? '',
            'conversation_id' => $result['ConversationID'] ?? '',
            'transaction_id' => $result['TransactionID'] ?? '',
        ];

        $params = $result['ResultParameters']['ResultParameter'] ?? [];
        foreach ($params as $param) {
            $key = $param['Key'] ?? '';
            $value = $param['Value'] ?? null;

            match ($key) {
                'ReceiptNo' => $parsed['receipt_no'] = $value,
                'Amount' => $parsed['amount'] = $value,
                'TransactionStatus' => $parsed['transaction_status'] = $value,
                'FinalisedTime' => $parsed['finalised_time'] = $value,
                'InitiatedTime' => $parsed['initiated_time'] = $value,
                'CreditPartyName' => $parsed['credit_party'] = $value,
                'DebitPartyName' => $parsed['debit_party'] = $value,
                'DebitPartyCharges' => $parsed['charges'] = $value,
                default => null,
            };
        }

        return $parsed;
    }

    public function render()
    {
        $localRecord = null;
        if ($this->transactionId) {
            $localRecord = PaymentItem::where('mpesa_transaction_receipt', $this->transactionId)->first();
        }

        return view('livewire.mpesa.transaction-status', compact('localRecord'));
    }
}
