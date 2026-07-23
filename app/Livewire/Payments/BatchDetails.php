<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\ApprovePaymentBatch;
use App\Actions\Payments\RejectPaymentBatch;
use App\Actions\Payments\RetryFailedPaymentItem;
use App\Actions\Payments\SchedulePaymentBatch;
use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class BatchDetails extends Component
{
    use WithPagination;

    public PaymentBatch $batch;
    public string $rejectionReason = '';
    public string $scheduledDate = '';
    public string $scheduledTime = '';
    public bool $showRejectModal = false;
    public bool $showScheduleModal = false;

    public function mount(PaymentBatch $batch): void
    {
        $this->authorize('view', $batch);
        $this->batch = $batch;
    }

    public function submitForApproval(): void
    {
        $this->authorize('submit', $this->batch);

        app(\App\Actions\Payments\SubmitPaymentBatch::class)->handle($this->batch, auth()->user());
        $this->batch->refresh();

        session()->flash('success', "Batch {$this->batch->batch_id} submitted. Status: {$this->batch->status->label()}");
    }

    public function approve(): void
    {
        $this->authorize('approve', $this->batch);

        app(ApprovePaymentBatch::class)->handle($this->batch, auth()->user());
        $this->batch->refresh();

        session()->flash('success', 'Batch approved successfully.');
    }

    public function openRejectModal(): void
    {
        $this->showRejectModal = true;
    }

    public function reject(): void
    {
        $this->authorize('reject', $this->batch);

        $this->validate(['rejectionReason' => 'required|string|min:5']);

        app(RejectPaymentBatch::class)->handle($this->batch, auth()->user(), $this->rejectionReason);
        $this->batch->refresh();
        $this->showRejectModal = false;

        session()->flash('success', 'Batch rejected.');
    }

    public function openScheduleModal(): void
    {
        $this->showScheduleModal = true;
    }

    public function schedule(): void
    {
        $this->authorize('schedule', $this->batch);

        $scheduledAt = null;
        if ($this->scheduledDate && $this->scheduledTime) {
            $scheduledAt = Carbon::parse("{$this->scheduledDate} {$this->scheduledTime}");
        }

        app(SchedulePaymentBatch::class)->handle($this->batch, auth()->user(), $scheduledAt);
        $this->batch->refresh();
        $this->showScheduleModal = false;

        session()->flash('success', 'Batch scheduled successfully.');
    }

    public function executeNow(): void
    {
        $this->authorize('schedule', $this->batch);

        app(SchedulePaymentBatch::class)->handle($this->batch, auth()->user());
        $this->batch->refresh();

        session()->flash('success', 'Batch scheduled for immediate processing.');
    }

    public function cancelBatch(): void
    {
        $this->authorize('cancel', $this->batch);

        $this->batch->update([
            'status' => PaymentBatchStatus::CANCELLED,
        ]);

        $this->batch->items()
            ->whereNotIn('status', [PaymentItemStatus::SUCCESSFUL])
            ->update(['status' => PaymentItemStatus::SKIPPED]);

        $this->batch->refresh();

        \App\Models\AuditLog::record('batch_cancelled', $this->batch, auth()->user());

        session()->flash('success', 'Batch cancelled.');
    }

    public function retryItem(int $itemId): void
    {
        $item = PaymentItem::findOrFail($itemId);
        $this->authorize('retry', $item);

        try {
            app(RetryFailedPaymentItem::class)->handle($item, auth()->user());
            session()->flash('success', "Payment item #{$item->row_number} queued for retry.");
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function downloadOriginalFile()
    {
        if ($this->batch->source_file_path && \Illuminate\Support\Facades\Storage::exists($this->batch->source_file_path)) {
            return \Illuminate\Support\Facades\Storage::download(
                $this->batch->source_file_path,
                $this->batch->source_file_name,
            );
        }

        session()->flash('error', 'Original file not found.');
    }

    public function render()
    {
        return view('livewire.payments.batch-details', [
            'items' => $this->batch->items()->paginate(20),
        ])->layout('components.layouts.app');
    }
}
