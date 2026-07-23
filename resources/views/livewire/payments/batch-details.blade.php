<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 break-all">Batch: {{ $batch->batch_id }}</h1>
            <p class="text-sm text-gray-500">Uploaded by {{ $batch->uploader->name ?? 'N/A' }} on {{ $batch->created_at->format('M d, Y H:i') }}</p>
        </div>
        <a href="{{ route('payments.batches') }}" class="text-sm text-gray-500 hover:text-gray-700 whitespace-nowrap">&larr; Back to Batches</a>
    </div>

    {{-- Batch Info --}}
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full
                    @switch($batch->status->color())
                        @case('green') bg-green-100 text-green-800 @break
                        @case('red') bg-red-100 text-red-800 @break
                        @case('yellow') bg-yellow-100 text-yellow-800 @break
                        @case('blue') bg-blue-100 text-blue-800 @break
                        @case('orange') bg-orange-100 text-orange-800 @break
                        @case('indigo') bg-indigo-100 text-indigo-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch
                ">{{ $batch->status->label() }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-500">Records</p>
                <p class="text-lg font-bold">{{ $batch->valid_record_count }} valid / {{ $batch->record_count }} total</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Amount</p>
                <p class="text-lg font-bold">KES {{ number_format($batch->total_amount, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">File</p>
                <p class="text-sm">{{ $batch->source_file_name }}</p>
            </div>
        </div>

        @if($batch->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
            <p class="text-sm font-medium text-red-800">Rejection Reason:</p>
            <p class="text-sm text-red-700">{{ $batch->rejection_reason }}</p>
        </div>
        @endif

        @if($batch->self_approved)
        <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
            <p class="text-sm text-blue-700">This batch was self-approved.</p>
        </div>
        @endif

        @if($batch->scheduled_at)
        <div class="bg-indigo-50 border border-indigo-200 rounded p-3 mb-4">
            <p class="text-sm text-indigo-700">Scheduled for: {{ $batch->scheduled_at->format('M d, Y H:i') }}</p>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-wrap gap-2">
            @can('submit', $batch)
                <button
                    wire:click="submitForApproval"
                    wire:confirm="Submit this batch for approval?"
                    wire:loading.attr="disabled"
                    wire:target="submitForApproval"
                    class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="submitForApproval">Submit for Approval</span>
                    <span wire:loading wire:target="submitForApproval">Submitting...</span>
                </button>
            @endcan

            @can('approve', $batch)
                <button wire:click="approve" wire:confirm="Are you sure you want to approve this batch?" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">Approve</button>
            @endcan

            @can('reject', $batch)
                <button wire:click="openRejectModal" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">Reject</button>
            @endcan

            @can('schedule', $batch)
                <button wire:click="executeNow" wire:confirm="Execute this batch immediately?" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">Execute Now</button>
                <button wire:click="openScheduleModal" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Schedule</button>
            @endcan

            @can('cancel', $batch)
                <button wire:click="cancelBatch" wire:confirm="Cancel this batch? This cannot be undone." class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">Cancel Batch</button>
            @endcan

            <button wire:click="downloadOriginalFile" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">Download Original File</button>
        </div>
    </div>

    {{-- Reject Modal --}}
    @if($showRejectModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Reject Batch</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                <textarea wire:model="rejectionReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500" placeholder="Provide a reason..."></textarea>
                @error('rejectionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end space-x-2">
                <button wire:click="$set('showRejectModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                <button wire:click="reject" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">Reject</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Schedule Modal --}}
    @if($showScheduleModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Schedule Batch</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input wire:model="scheduledDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                <input wire:model="scheduledTime" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex justify-end space-x-2">
                <button wire:click="$set('showScheduleModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                <button wire:click="schedule" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Schedule</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Payment Items --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Payment Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $item->row_number }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->employee_name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->normalized_phone ?? $item->phone_number_raw }}</td>
                        <td class="px-4 py-3 text-sm">KES {{ $item->amount ? number_format($item->amount, 2) : '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @switch($item->status->color())
                                    @case('green') bg-green-100 text-green-800 @break
                                    @case('red') bg-red-100 text-red-800 @break
                                    @case('yellow') bg-yellow-100 text-yellow-800 @break
                                    @case('blue') bg-blue-100 text-blue-800 @break
                                    @case('orange') bg-orange-100 text-orange-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">{{ $item->status->label() }}</span>
                            @if($item->validation_errors)
                                <div class="mt-1">
                                    @foreach($item->validation_errors as $error)
                                        <p class="text-xs text-red-500">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->mpesa_transaction_receipt ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if(in_array($item->status->value, ['failed', 'timeout']))
                                @can('retry', $item)
                                <button wire:click="retryItem({{ $item->id }})" class="text-blue-600 hover:text-blue-800 text-xs">Retry</button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t">
            {{ $items->links() }}
        </div>
    </div>
</div>
