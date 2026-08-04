<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\ApprovePaymentBatch;
use App\Actions\Payments\RejectPaymentBatch;
use App\Actions\Payments\SchedulePaymentBatch;
use App\Actions\Payments\SubmitPaymentBatch;
use App\Enums\PaymentBatchStatus;
use App\Jobs\ProcessPaymentBatchJob;
use App\Models\AuditLog;
use App\Models\PaymentBatch;
use Livewire\Component;
use Livewire\WithPagination;

class BatchDetails extends Component
{
    use WithPagination;

    public PaymentBatch $batch;
    public bool $showRejectModal = false;
    public bool $showScheduleModal = false;
    public string $rejectionReason = '';
    public string $scheduledAt = '';

    public function mount(PaymentBatch $batch)
    {
        $this->batch = $batch;
    }

    public function submit()
    {
        app(SubmitPaymentBatch::class)->execute($this->batch);
        $this->batch->refresh();
        session()->flash('success', 'Batch submitted for approval.');
    }

    public function approve()
    {
        app(ApprovePaymentBatch::class)->execute($this->batch);
        $this->batch->refresh();
        session()->flash('success', 'Batch approved.');
    }

    public function reject()
    {
        $this->validate(['rejectionReason' => 'required|min:5']);

        app(RejectPaymentBatch::class)->execute($this->batch, $this->rejectionReason);
        $this->batch->refresh();
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        session()->flash('success', 'Batch rejected.');
    }

    public function schedule()
    {
        $this->validate(['scheduledAt' => 'required|date|after:now']);

        app(SchedulePaymentBatch::class)->execute($this->batch, $this->scheduledAt);
        $this->batch->refresh();
        $this->showScheduleModal = false;
        session()->flash('success', 'Batch scheduled.');
    }

    public function execute()
    {
        ProcessPaymentBatchJob::dispatch($this->batch->id);
        $this->batch->update(['status' => PaymentBatchStatus::PROCESSING, 'processing_started_at' => now()]);
        $this->batch->refresh();
        session()->flash('success', 'Batch processing started.');
    }

    public function cancel()
    {
        $this->batch->update(['status' => PaymentBatchStatus::CANCELLED]);
        AuditLog::record('batch_cancelled', $this->batch);
        $this->batch->refresh();
        session()->flash('success', 'Batch cancelled.');
    }

    public function render()
    {
        $items = $this->batch->items()->paginate(20);

        return view('livewire.payments.batch-details', compact('items'));
    }
}
