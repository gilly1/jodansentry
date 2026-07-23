<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\ApprovePaymentBatch;
use App\Actions\Payments\RejectPaymentBatch;
use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;
use Livewire\Component;

class PendingApprovals extends Component
{
    public string $rejectionReason = '';
    public ?int $rejectingBatchId = null;

    public function approve(int $batchId): void
    {
        $batch = PaymentBatch::findOrFail($batchId);
        $this->authorize('approve', $batch);

        app(ApprovePaymentBatch::class)->handle($batch, auth()->user());

        session()->flash('success', "Batch {$batch->batch_id} approved.");
    }

    public function openRejectModal(int $batchId): void
    {
        $this->rejectingBatchId = $batchId;
        $this->rejectionReason = '';
    }

    public function reject(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:5']);

        $batch = PaymentBatch::findOrFail($this->rejectingBatchId);
        $this->authorize('reject', $batch);

        app(RejectPaymentBatch::class)->handle($batch, auth()->user(), $this->rejectionReason);

        $this->rejectingBatchId = null;
        $this->rejectionReason = '';

        session()->flash('success', "Batch {$batch->batch_id} rejected.");
    }

    public function render()
    {
        return view('livewire.payments.pending-approvals', [
            'batches' => PaymentBatch::with('uploader')
                ->where('status', PaymentBatchStatus::PENDING_APPROVAL)
                ->latest()
                ->get(),
        ])->layout('components.layouts.app');
    }
}
