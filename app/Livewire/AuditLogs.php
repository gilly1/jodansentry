<?php

namespace App\Livewire;

use App\Models\AuditLog as AuditLogModel;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLogModel::with('user')
            ->when($this->search, fn($q) => $q->where('action', 'like', "%{$this->search}%")
                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")))
            ->when($this->actionFilter, fn($q) => $q->where('action', $this->actionFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $actions = AuditLogModel::distinct()->pluck('action');

        return view('livewire.audit-logs', compact('logs', 'actions'));
    }
}
