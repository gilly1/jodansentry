<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Pending Approvals</h1>

    @if($batches->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        No batches pending approval.
    </div>
    @else
    <div class="space-y-4">
        @foreach($batches as $batch)
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('payments.batches.show', $batch) }}" class="text-lg font-semibold text-green-600 hover:text-green-800">{{ $batch->batch_id }}</a>
                    <p class="text-sm text-gray-500 mt-1">
                        Uploaded by <span class="font-medium">{{ $batch->uploader->name ?? 'N/A' }}</span>
                        on {{ $batch->created_at->format('M d, Y H:i') }}
                    </p>
                </div>
                <div class="sm:text-right mt-2 sm:mt-0">
                    <p class="text-sm text-gray-500">{{ $batch->valid_record_count }} valid / {{ $batch->record_count }} total records</p>
                    <p class="text-lg font-bold text-gray-800">KES {{ number_format($batch->total_amount, 2) }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 justify-end mt-4 pt-4 border-t">
                <button wire:click="approve({{ $batch->id }})" wire:confirm="Approve batch {{ $batch->batch_id }}?" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">Approve</button>
                <button wire:click="openRejectModal({{ $batch->id }})" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">Reject</button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Reject Modal --}}
    @if($rejectingBatchId)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Reject Batch</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                <textarea wire:model="rejectionReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500" placeholder="Provide a reason..."></textarea>
                @error('rejectionReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end space-x-2">
                <button wire:click="$set('rejectingBatchId', null)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                <button wire:click="reject" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">Reject</button>
            </div>
        </div>
    </div>
    @endif
</div>
