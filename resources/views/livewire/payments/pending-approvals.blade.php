<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Pending Approvals</h1>
        <p class="text-sm text-slate-500 mt-0.5">Batches awaiting your approval</p>
    </div>

    @if($batches->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="mt-3 text-sm text-slate-500">No batches pending approval.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($batches as $batch)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <a href="{{ route('payments.batch.show', $batch) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">{{ $batch->batch_id }}</a>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ $batch->uploader->name }} &middot; {{ $batch->valid_records }} records &middot; KES {{ number_format($batch->total_amount, 0) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Submitted {{ $batch->submitted_at?->diffForHumans() ?? $batch->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="approve({{ $batch->id }})" wire:confirm="Approve this batch?" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700 transition">Approve</button>
                    <button wire:click="openRejectModal({{ $batch->id }})" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 transition">Reject</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Reject Modal --}}
    @if($showRejectModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Reject Batch</h3>
            <textarea wire:model="rejectionReason" rows="3" placeholder="Reason for rejection..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"></textarea>
            @error('rejectionReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="$set('showRejectModal', false)" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button wire:click="reject" class="rounded-lg bg-red-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-red-700">Reject</button>
            </div>
        </div>
    </div>
    @endif
</div>
