<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\ApprovePaymentBatch;
use App\Actions\Payments\RejectPaymentBatch;
use App\Models\PaymentBatch;
use Livewire\Component;

class PendingApprovals extends Component
{
    public bool $showRejectModal = false;
    public ?int $rejectingBatchId = null;
    public string $rejectionReason = '';

    public function approve(int $batchId)
    {
        $batch = PaymentBatch::findOrFail($batchId);
        app(ApprovePaymentBatch::class)->execute($batch);
        session()->flash('success', 'Batch approved successfully.');
    }

    public function openRejectModal(int $batchId)
    {
        $this->rejectingBatchId = $batchId;
        $this->showRejectModal = true;
    }

    public function reject()
    {
        $this->validate(['rejectionReason' => 'required|min:5']);

        $batch = PaymentBatch::findOrFail($this->rejectingBatchId);
        app(RejectPaymentBatch::class)->execute($batch, $this->rejectionReason);

        $this->showRejectModal = false;
        $this->rejectingBatchId = null;
        $this->rejectionReason = '';
        session()->flash('success', 'Batch rejected.');
    }

    public function render()
    {
        $batches = PaymentBatch::with('uploader')
            ->where('status', 'pending_approval')
            ->latest()
            ->get();

        return view('livewire.payments.pending-approvals', compact('batches'));
    }
}
