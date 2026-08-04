<?php

namespace App\Livewire\Mpesa;

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

    public function query()
    {
        $this->validate(['transactionId' => 'required|string|min:5']);

        $this->loading = true;
        $this->error = null;
        $this->statusResult = null;

        try {
            $builder = app(MpesaPayloadBuilder::class);
            $client = app(MpesaClient::class);

            $payload = $builder->buildTransactionStatusPayload($this->transactionId);
            $response = $client->sendTransactionStatusRequest($payload);

            if (($response['ResponseCode'] ?? '') === '0') {
                $this->statusResult = $response;
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

    public function render()
    {
        // Also show local transaction records
        $localRecord = null;
        if ($this->transactionId) {
            $localRecord = PaymentItem::where('mpesa_transaction_receipt', $this->transactionId)->first();
        }

        return view('livewire.mpesa.transaction-status', compact('localRecord'));
    }
}
