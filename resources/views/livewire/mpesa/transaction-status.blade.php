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
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900">M-Pesa Response</h3>
            <button wire:click="checkCallback" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Check Callback
            </button>
        </div>
        <div class="rounded-lg bg-blue-50 border border-blue-200 p-3">
            <p class="text-sm text-blue-700">Request accepted. Results will arrive via callback.</p>
            @if($conversationId)
                <p class="text-xs text-blue-500 mt-1">Conversation ID: {{ $conversationId }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Callback Result --}}
    @if($callbackResult)
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-4">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Transaction Status Result</h3>
        @if($callbackResult['result_code'] === '0')
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                @if(!empty($callbackResult['receipt_no']))
                <div>
                    <p class="text-xs text-slate-500">Receipt No</p>
                    <p class="font-medium text-slate-900">{{ $callbackResult['receipt_no'] }}</p>
                </div>
                @endif
                @if(!empty($callbackResult['transaction_status']))
                <div>
                    <p class="text-xs text-slate-500">Status</p>
                    <p class="font-medium text-slate-900">{{ $callbackResult['transaction_status'] }}</p>
                </div>
                @endif
                @if(!empty($callbackResult['amount']))
                <div>
                    <p class="text-xs text-slate-500">Amount</p>
                    <p class="font-medium text-slate-900">KES {{ number_format($callbackResult['amount'], 2) }}</p>
                </div>
                @endif
                @if(!empty($callbackResult['debit_party']))
                <div>
                    <p class="text-xs text-slate-500">Debit Party</p>
                    <p class="text-slate-700">{{ $callbackResult['debit_party'] }}</p>
                </div>
                @endif
                @if(!empty($callbackResult['credit_party']))
                <div>
                    <p class="text-xs text-slate-500">Credit Party</p>
                    <p class="text-slate-700">{{ $callbackResult['credit_party'] }}</p>
                </div>
                @endif
                @if(!empty($callbackResult['charges']))
                <div>
                    <p class="text-xs text-slate-500">Charges</p>
                    <p class="text-slate-700">{{ $callbackResult['charges'] }}</p>
                </div>
                @endif
                @if(!empty($callbackResult['finalised_time']))
                <div>
                    <p class="text-xs text-slate-500">Finalised</p>
                    <p class="text-slate-700">{{ $callbackResult['finalised_time'] }}</p>
                </div>
                @endif
            </div>
        @else
            <div class="rounded-lg bg-red-50 border border-red-200 p-3">
                <p class="text-sm text-red-700">{{ $callbackResult['result_desc'] }}</p>
                <p class="text-xs text-red-500 mt-1">Result Code: {{ $callbackResult['result_code'] }}</p>
            </div>
        @endif
    </div>
    @endif

    @if($error)
    <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ $error }}
    </div>
    @endif
</div>
