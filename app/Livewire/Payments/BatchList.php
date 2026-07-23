<?php

namespace App\Livewire\Payments;

use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;
use Livewire\Component;
use Livewire\WithPagination;

class BatchList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = PaymentBatch::with('uploader')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('batch_id', 'like', "%{$this->search}%")
                        ->orWhereHas('items', function ($q) {
                            $q->where('employee_name', 'like', "%{$this->search}%")
                                ->orWhere('normalized_phone', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        if (! auth()->user()->can('payment-batches.view-all')) {
            $query->where('uploaded_by', auth()->id());
        }

        return view('livewire.payments.batch-list', [
            'batches' => $query->latest()->paginate(15),
            'statuses' => PaymentBatchStatus::cases(),
        ])->layout('components.layouts.app');
    }
}
