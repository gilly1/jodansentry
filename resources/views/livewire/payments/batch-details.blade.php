<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('payments.batches') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back</a>
            </div>
            <h1 class="text-xl font-semibold text-slate-900">{{ $batch->batch_id }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">Uploaded by {{ $batch->uploader->name }} on {{ $batch->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div>@include('components.status-badge', ['status' => $batch->status])</div>
    </div>

    {{-- Info cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total Records</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $batch->total_records }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Valid</p>
            <p class="mt-1 text-lg font-semibold text-emerald-600">{{ $batch->valid_records }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Successful</p>
            <p class="mt-1 text-lg font-semibold text-emerald-600">{{ $batch->successful_records }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Failed</p>
            <p class="mt-1 text-lg font-semibold text-red-600">{{ $batch->failed_records }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total Amount</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">KES {{ number_format($batch->total_amount, 0) }}</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if($batch->rejection_reason)
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        <strong>Rejected:</strong> {{ $batch->rejection_reason }}
    </div>
    @endif

    @if($batch->self_approved)
    <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
        This batch was self-approved.
    </div>
    @endif

    @if($batch->scheduled_at)
    <div class="mb-4 rounded-lg bg-indigo-50 border border-indigo-200 px-4 py-3 text-sm text-indigo-800">
        Scheduled for: {{ $batch->scheduled_at->format('d M Y, H:i') }}
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if($batch->status->value === 'uploaded')
            <button wire:click="submit" wire:confirm="Submit this batch for approval?" class="rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">Submit</button>
        @endif

        @if($batch->status->value === 'pending_approval' && auth()->user()->can('approve payments'))
            <button wire:click="approve" wire:confirm="Approve this batch?" class="rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition">Approve</button>
            <button wire:click="$set('showRejectModal', true)" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-red-700 transition">Reject</button>
        @endif

        @if($batch->status->value === 'approved')
            <button wire:click="execute" wire:confirm="Execute payments now?" class="rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Execute Now</button>
            <button wire:click="$set('showScheduleModal', true)" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Schedule</button>
        @endif

        @if($batch->isCancellable())
            <button wire:click="cancel" wire:confirm="Cancel this batch?" class="rounded-lg border border-red-300 px-3.5 py-2 text-sm font-medium text-red-700 hover:bg-red-50 transition">Cancel</button>
        @endif
    </div>

    {{-- Payment items table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200">
            <h2 class="text-sm font-semibold text-slate-900">Payment Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Employee</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Phone</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Amount</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Reason</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 text-slate-500">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                        <td class="px-4 py-2.5 font-medium text-slate-900">{{ $item->employee_name }}</td>
                        <td class="px-4 py-2.5 text-slate-600">{{ $item->normalized_phone ?? $item->phone_number_raw }}</td>
                        <td class="px-4 py-2.5 font-medium">KES {{ number_format($item->amount, 0) }}</td>
                        <td class="px-4 py-2.5">@include('components.status-badge', ['status' => $item->status])</td>
                        <td class="px-4 py-2.5 text-xs text-slate-600 max-w-xs">
                            @if($item->status->value === 'invalid' && $item->validation_errors)
                                <span class="text-red-600">{{ implode(', ', $item->validation_errors) }}</span>
                            @elseif($item->status->value === 'failed')
                                <span class="text-red-600">{{ $item->mpesa_result_description ?? $item->mpesa_response_description ?? 'Payment failed' }}</span>
                            @elseif($item->status->value === 'timeout')
                                <span class="text-amber-600">Request timed out - no response received</span>
                            @elseif($item->status->value === 'successful')
                                <span class="text-emerald-600">{{ $item->mpesa_result_description ?? 'Completed successfully' }}</span>
                            @elseif(in_array($item->status->value, ['queued', 'processing']))
                                <span class="text-blue-600">Awaiting M-Pesa response</span>
                            @elseif($item->status->value === 'validated')
                                <span class="text-slate-500">Ready for processing</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-500 text-xs font-mono">{{ $item->mpesa_transaction_receipt ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-5 py-3 border-t border-slate-200">
            {{ $items->links() }}
        </div>
        @endif
    </div>

    {{-- Reject Modal --}}
    @if($showRejectModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Reject Batch</h3>
            <textarea wire:model="rejectionReason" rows="3" placeholder="Reason for rejection (min 5 characters)..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"></textarea>
            @error('rejectionReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="$set('showRejectModal', false)" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button wire:click="reject" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-red-700">Reject</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Schedule Modal --}}
    @if($showScheduleModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Schedule Execution</h3>
            <input wire:model="scheduledAt" type="datetime-local" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            @error('scheduledAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="$set('showScheduleModal', false)" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button wire:click="schedule" class="rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800">Schedule</button>
            </div>
        </div>
    </div>
    @endif
</div>
