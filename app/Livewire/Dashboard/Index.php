<?php

namespace App\Livewire\Dashboard;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.dashboard.index', [
            'totalSuccessful' => PaymentItem::where('status', PaymentItemStatus::SUCCESSFUL)->count(),
            'totalFailed' => PaymentItem::where('status', PaymentItemStatus::FAILED)->count(),
            'totalAmountPaid' => PaymentItem::where('status', PaymentItemStatus::SUCCESSFUL)->sum('amount'),
            'pendingApprovalCount' => PaymentBatch::where('status', PaymentBatchStatus::PENDING_APPROVAL)->count(),
            'totalBatchesProcessed' => PaymentBatch::whereIn('status', [
                PaymentBatchStatus::SUCCESSFUL,
                PaymentBatchStatus::PARTIALLY_SUCCESSFUL,
                PaymentBatchStatus::FAILED,
            ])->count(),
            'todaySuccessfulAmount' => PaymentItem::where('status', PaymentItemStatus::SUCCESSFUL)
                ->whereDate('processed_at', today())
                ->sum('amount'),
            'todayFailedCount' => PaymentItem::where('status', PaymentItemStatus::FAILED)
                ->whereDate('failed_at', today())
                ->count(),
            'recentBatches' => PaymentBatch::with('uploader')
                ->latest()
                ->take(10)
                ->get(),
            'processingBatches' => PaymentBatch::with('uploader')
                ->where('status', PaymentBatchStatus::PROCESSING)
                ->get(),
        ])->layout('components.layouts.app');
    }
}
