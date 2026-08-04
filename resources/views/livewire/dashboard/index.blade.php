<div>
    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">Overview of your payment operations</p>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Paid</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">KES {{ number_format($stats['total_paid_all'], 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Today's Payments</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">KES {{ number_format($stats['total_paid_today'], 0) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $stats['successful_today'] }} transactions</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Pending Approval</p>
            <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $stats['pending_approval'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Failed Today</p>
            <p class="mt-2 text-2xl font-semibold text-red-600">{{ $stats['failed_today'] }}</p>
        </div>
    </div>

    {{-- Balance Query --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-slate-900">Account Balance</h2>
            <button wire:click="queryBalance" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 transition">
                <svg wire:loading.class="animate-spin" wire:target="queryBalance" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span wire:loading.remove wire:target="queryBalance">Query Balance</span>
                <span wire:loading wire:target="queryBalance">Querying...</span>
            </button>
        </div>
        @if($balanceData)
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs text-slate-500">Response received. Balance information will be delivered via callback.</p>
                <pre class="mt-2 text-xs text-slate-700 overflow-x-auto">{{ json_encode($balanceData, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @elseif($balanceError)
            <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">{{ $balanceError }}</div>
        @else
            <p class="text-sm text-slate-500">Click "Query Balance" to check your M-Pesa account balance.</p>
        @endif
    </div>

    {{-- Recent batches --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Recent Batches</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Batch ID</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Uploaded By</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Records</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Amount</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentBatches as $batch)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('payments.batch.show', $batch) }}" class="font-medium text-blue-600 hover:text-blue-700">{{ $batch->batch_id }}</a>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $batch->uploader->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $batch->valid_records }}/{{ $batch->total_records }}</td>
                        <td class="px-5 py-3 text-slate-900 font-medium">KES {{ number_format($batch->total_amount, 0) }}</td>
                        <td class="px-5 py-3">
                            @include('components.status-badge', ['status' => $batch->status])
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ $batch->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">No batches yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
