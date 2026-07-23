<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Payment Batches</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Batch ID, name, phone..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input wire:model.live="dateFrom" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input wire:model.live="dateTo" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
        </div>
    </div>

    {{-- Table (desktop) --}}
    <div class="bg-white rounded-lg shadow overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="{{ route('payments.batches.show', $batch) }}" class="text-green-600 hover:text-green-800">{{ $batch->batch_id }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $batch->uploader->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="text-green-600">{{ $batch->valid_record_count }}</span> / {{ $batch->record_count }}
                        </td>
                        <td class="px-6 py-4 text-sm">KES {{ number_format($batch->total_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
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
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $batch->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('payments.batches.show', $batch) }}" class="text-green-600 hover:text-green-800">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t">
            {{ $batches->links() }}
        </div>
    </div>

    {{-- Mobile card view --}}
    <div class="sm:hidden space-y-3">
        @forelse($batches as $batch)
        <a href="{{ route('payments.batches.show', $batch) }}" class="block bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-green-600">{{ $batch->batch_id }}</span>
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
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
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ $batch->uploader->name ?? 'N/A' }}</span>
                <span class="font-medium">KES {{ number_format($batch->total_amount, 2) }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                <span><span class="text-green-600">{{ $batch->valid_record_count }}</span>/{{ $batch->record_count }} records</span>
                <span>{{ $batch->created_at->format('M d, Y') }}</span>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">No batches found.</div>
        @endforelse
        <div class="mt-3">
            {{ $batches->links() }}
        </div>
    </div>
</div>
