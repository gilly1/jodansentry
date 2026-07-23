<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Total Amount Paid</p>
            <p class="text-2xl font-bold text-green-600">KES {{ number_format($totalAmountPaid, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Successful Transactions</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalSuccessful) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Failed Transactions</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($totalFailed) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($pendingApprovalCount) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Today's Successful Amount</p>
            <p class="text-xl font-bold text-green-600">KES {{ number_format($todaySuccessfulAmount, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Today's Failed Count</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($todayFailedCount) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">Total Batches Processed</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalBatchesProcessed) }}</p>
        </div>
    </div>

    {{-- Processing Batches --}}
    @if($processingBatches->isNotEmpty())
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-8">
        <h3 class="text-lg font-semibold text-orange-800 mb-3">Currently Processing</h3>
        <div class="space-y-2">
            @foreach($processingBatches as $batch)
            <div class="flex items-center justify-between bg-white rounded p-3">
                <div>
                    <span class="font-medium">{{ $batch->batch_id }}</span>
                    <span class="text-sm text-gray-500 ml-2">by {{ $batch->uploader->name ?? 'N/A' }}</span>
                </div>
                <span class="text-sm text-orange-600">{{ $batch->valid_record_count }} records</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Batches --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Recent Batches</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentBatches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('payments.batches.show', $batch) }}" class="text-green-600 hover:text-green-800 font-medium">{{ $batch->batch_id }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $batch->uploader->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $batch->valid_record_count }}/{{ $batch->record_count }}</td>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No batches found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
