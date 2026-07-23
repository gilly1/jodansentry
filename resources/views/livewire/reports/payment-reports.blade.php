<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Payment Reports</h1>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Report Type</label>
                <select wire:model.live="reportType" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="batch_summary">Batch Summary</option>
                    <option value="employee_detail">Employee Payment Detail</option>
                    <option value="failed_payments">Failed Payments</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Name, phone, receipt..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input wire:model.live="dateFrom" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input wire:model.live="dateTo" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div class="flex items-end">
                <button wire:click="export" class="w-full bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">Export CSV</button>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            @if($reportType === 'batch_summary')
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($results as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('payments.batches.show', $batch) }}" class="text-green-600 hover:text-green-800">{{ $batch->batch_id }}</a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @switch($batch->status->color())
                                    @case('green') bg-green-100 text-green-800 @break
                                    @case('red') bg-red-100 text-red-800 @break
                                    @case('yellow') bg-yellow-100 text-yellow-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">{{ $batch->status->label() }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $batch->uploader->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $batch->record_count }}</td>
                        <td class="px-6 py-4 text-sm">KES {{ number_format($batch->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $batch->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No results found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @else
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($results as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $item->batch->batch_id ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $item->employee_name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $item->normalized_phone }}</td>
                        <td class="px-6 py-4 text-sm">KES {{ number_format($item->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @switch($item->status->color())
                                    @case('green') bg-green-100 text-green-800 @break
                                    @case('red') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">{{ $item->status->label() }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->mpesa_transaction_receipt ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No results found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @endif
        </div>
        <div class="px-6 py-3 border-t">
            {{ $results->links() }}
        </div>
    </div>
</div>
