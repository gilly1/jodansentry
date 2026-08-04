<?php

namespace App\Livewire\Dashboard;

use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use App\Services\Mpesa\MpesaClient;
use App\Services\Mpesa\MpesaPayloadBuilder;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    public ?array $balanceData = null;
    public bool $loadingBalance = false;
    public ?string $balanceError = null;

    public function queryBalance()
    {
        $this->loadingBalance = true;
        $this->balanceError = null;
        $this->balanceData = null;

        try {
            $builder = app(MpesaPayloadBuilder::class);
            $client = app(MpesaClient::class);

            $payload = $builder->buildBalancePayload();
            $response = $client->sendAccountBalanceRequest($payload);

            if (($response['ResponseCode'] ?? '') === '0') {
                $this->balanceData = $response;
            } else {
                $this->balanceError = $response['ResponseDescription'] ?? 'Failed to query balance';
            }
        } catch (\Exception $e) {
            $this->balanceError = $e->getMessage();
            Log::channel('mpesa')->error('Balance query failed', ['error' => $e->getMessage()]);
        } finally {
            $this->loadingBalance = false;
        }
    }

    public function render()
    {
        $stats = [
            'total_batches' => PaymentBatch::count(),
            'pending_approval' => PaymentBatch::where('status', PaymentBatchStatus::PENDING_APPROVAL)->count(),
            'processing' => PaymentBatch::where('status', PaymentBatchStatus::PROCESSING)->count(),
            'successful_today' => PaymentItem::where('status', 'successful')
                ->whereDate('processed_at', today())->count(),
            'failed_today' => PaymentItem::where('status', 'failed')
                ->whereDate('failed_at', today())->count(),
            'total_paid_today' => PaymentItem::where('status', 'successful')
                ->whereDate('processed_at', today())->sum('amount'),
            'total_paid_all' => PaymentItem::where('status', 'successful')->sum('amount'),
        ];

        $recentBatches = PaymentBatch::with('uploader')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.dashboard.index', compact('stats', 'recentBatches'));
    }
}
