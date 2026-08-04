<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Payment Batches</h1>
            <p class="text-sm text-slate-500 mt-0.5">All uploaded payment batches</p>
        </div>
        <a href="{{ route('payments.upload') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Upload
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search batch ID..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            <input wire:model.live="dateTo" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Batch ID</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Uploaded By</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Records</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Amount</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('payments.batch.show', $batch) }}" class="font-medium text-blue-600 hover:text-blue-700">{{ $batch->batch_id }}</a>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $batch->uploader->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $batch->valid_records }}/{{ $batch->total_records }}</td>
                        <td class="px-5 py-3 font-medium text-slate-900">KES {{ number_format($batch->total_amount, 0) }}</td>
                        <td class="px-5 py-3">@include('components.status-badge', ['status' => $batch->status])</td>
                        <td class="px-5 py-3 text-slate-500">{{ $batch->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
        <div class="px-5 py-3 border-t border-slate-200">
            {{ $batches->links() }}
        </div>
        @endif
    </div>
</div>
