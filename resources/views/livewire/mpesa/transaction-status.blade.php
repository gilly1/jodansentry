<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Transaction Status</h1>
        <p class="text-sm text-slate-500 mt-0.5">Query M-Pesa transaction status by receipt number</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Transaction ID / Receipt</label>
                <input wire:model="transactionId" type="text" placeholder="e.g. SHQ1234ABC" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            </div>
            <button wire:click="query" wire:loading.attr="disabled" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50 transition">
                <span wire:loading.remove wire:target="query">Query</span>
                <span wire:loading wire:target="query">Querying...</span>
            </button>
        </div>
        @error('transactionId') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Local record --}}
    @if($localRecord)
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-4">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Local Record</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-500">Employee</p>
                <p class="font-medium text-slate-900">{{ $localRecord->employee_name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Phone</p>
                <p class="text-slate-700">{{ $localRecord->normalized_phone }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Amount</p>
                <p class="font-medium text-slate-900">KES {{ number_format($localRecord->amount, 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Status</p>
                <p>@include('components.status-badge', ['status' => $localRecord->status])</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Batch</p>
                <a href="{{ route('payments.batch.show', $localRecord->batch) }}" class="text-blue-600 hover:text-blue-700">{{ $localRecord->batch->batch_id }}</a>
            </div>
            <div>
                <p class="text-xs text-slate-500">Processed</p>
                <p class="text-slate-700">{{ $localRecord->processed_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- API Response --}}
    @if($statusResult)
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">M-Pesa Response</h3>
        <div class="rounded-lg bg-slate-50 border border-slate-200 p-4">
            <pre class="text-xs text-slate-700 overflow-x-auto whitespace-pre-wrap">{{ json_encode($statusResult, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif

    @if($error)
    <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ $error }}
    </div>
    @endif
</div>
