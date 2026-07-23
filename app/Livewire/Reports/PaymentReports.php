<?php

namespace App\Livewire\Reports;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentReports extends Component
{
    use WithPagination;

    public string $reportType = 'batch_summary';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $statusFilter = '';
    public string $search = '';

    public function updatingReportType(): void
    {
        $this->resetPage();
    }

    public function export()
    {
        $data = $this->getReportData();

        $filename = "{$this->reportType}_" . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        if ($this->reportType === 'batch_summary') {
            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Batch ID', 'Status', 'Uploaded By', 'Records', 'Total Amount', 'Created At']);
                foreach ($data as $batch) {
                    fputcsv($file, [
                        $batch->batch_id,
                        $batch->status->label(),
                        $batch->uploader->name ?? 'N/A',
                        $batch->record_count,
                        $batch->total_amount,
                        $batch->created_at->format('Y-m-d H:i'),
                    ]);
                }
                fclose($file);
            };
        } else {
            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Batch ID', 'Employee', 'Phone', 'Amount', 'Status', 'Receipt', 'Processed At']);
                foreach ($data as $item) {
                    fputcsv($file, [
                        $item->batch->batch_id ?? 'N/A',
                        $item->employee_name,
                        $item->normalized_phone,
                        $item->amount,
                        $item->status->label(),
                        $item->mpesa_transaction_receipt ?? '',
                        $item->processed_at?->format('Y-m-d H:i') ?? '',
                    ]);
                }
                fclose($file);
            };
        }

        return response()->stream($callback, 200, $headers);
    }

    protected function getReportData()
    {
        if ($this->reportType === 'batch_summary') {
            return PaymentBatch::with('uploader')
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->latest()
                ->get();
        }

        $query = PaymentItem::with('batch');

        if ($this->reportType === 'failed_payments') {
            $query->whereIn('status', [PaymentItemStatus::FAILED, PaymentItemStatus::TIMEOUT]);
        }

        return $query
            ->when($this->search, fn ($q) => $q->where('employee_name', 'like', "%{$this->search}%")
                ->orWhere('normalized_phone', 'like', "%{$this->search}%")
                ->orWhere('mpesa_transaction_receipt', 'like', "%{$this->search}%"))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->get();
    }

    public function render()
    {
        if ($this->reportType === 'batch_summary') {
            $query = PaymentBatch::with('uploader')
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->latest();

            $results = $query->paginate(15);
        } else {
            $query = PaymentItem::with('batch');

            if ($this->reportType === 'failed_payments') {
                $query->whereIn('status', [PaymentItemStatus::FAILED, PaymentItemStatus::TIMEOUT]);
            }

            $results = $query
                ->when($this->search, fn ($q) => $q->where('employee_name', 'like', "%{$this->search}%")
                    ->orWhere('normalized_phone', 'like', "%{$this->search}%")
                    ->orWhere('mpesa_transaction_receipt', 'like', "%{$this->search}%"))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->latest()
                ->paginate(15);
        }

        return view('livewire.reports.payment-reports', [
            'results' => $results,
            'batchStatuses' => PaymentBatchStatus::cases(),
        ])->layout('components.layouts.app');
    }
}
