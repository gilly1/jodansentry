<?php

namespace App\Livewire\AuditLogs;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                        ->orWhere('action', 'like', "%{$this->search}%")
                        ->orWhere('ip_address', 'like', "%{$this->search}%");
                });
            })
            ->when($this->actionFilter, fn ($q) => $q->where('action', $this->actionFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $actions = AuditLog::distinct()->pluck('action');

        return view('livewire.audit-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
        ])->layout('components.layouts.app');
    }
}
