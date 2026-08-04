<div>
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('contacts.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Back to Contacts
        </a>
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-slate-100">
                <svg class="h-6 w-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-slate-900">{{ $contact->mpesa_name }}</h1>
                <p class="text-sm text-slate-500">{{ $contact->phone_number }}</p>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Payments</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($contact->total_transactions) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Amount</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">KES {{ number_format($contact->total_amount, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Last Paid</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $contact->last_paid_at?->format('M d, Y') ?? '-' }}</p>
        </div>
    </div>

    {{-- Transactions table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Receipt</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Amount</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Batch</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Description</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Paid At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-slate-700">{{ $txn->transaction_receipt ?? '-' }}</td>
                        <td class="px-5 py-3 font-medium text-slate-900">KES {{ number_format($txn->amount, 0) }}</td>
                        <td class="px-5 py-3">
                            @if($txn->paymentBatch)
                                <a href="{{ route('payments.batch.show', $txn->payment_batch_id) }}" class="text-blue-600 hover:text-blue-700">{{ $txn->paymentBatch->batch_id }}</a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-600 max-w-xs truncate">{{ $txn->mpesa_result_description ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $txn->paid_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                            No transactions recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="border-t border-slate-200 px-5 py-3">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
