<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Upload Payment Batch</h1>
        <button wire:click="downloadTemplate" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm w-full sm:w-auto">
            Download Template
        </button>
    </div>

    {{-- Upload Form --}}
    @if(!$showPreview)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Excel File</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div wire:loading wire:target="file" class="mb-4 text-sm text-blue-600">
                Uploading file...
            </div>

            <button
                type="button"
                wire:click="validateBatch"
                wire:loading.attr="disabled"
                wire:target="file, validateBatch"
                class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="validateBatch">Upload & Validate</span>
                <span wire:loading wire:target="validateBatch">Processing...</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Preview --}}
    @if($showPreview && $batch)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Batch Summary: {{ $batch->batch_id }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 rounded p-3">
                <p class="text-xs text-gray-500">Total Records</p>
                <p class="text-lg font-bold">{{ $batch->record_count }}</p>
            </div>
            <div class="bg-green-50 rounded p-3">
                <p class="text-xs text-green-600">Valid Records</p>
                <p class="text-lg font-bold text-green-700">{{ $batch->valid_record_count }}</p>
            </div>
            <div class="bg-red-50 rounded p-3">
                <p class="text-xs text-red-600">Invalid Records</p>
                <p class="text-lg font-bold text-red-700">{{ $batch->invalid_record_count }}</p>
            </div>
            <div class="bg-blue-50 rounded p-3">
                <p class="text-xs text-blue-600">Total Amount</p>
                <p class="text-lg font-bold text-blue-700">KES {{ number_format($batch->total_amount, 2) }}</p>
            </div>
        </div>

        @if($batch->valid_record_count > 0)
        <button
            wire:click="submit"
            wire:loading.attr="disabled"
            wire:target="submit"
            class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 mr-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="submit">Submit for Approval</span>
            <span wire:loading wire:target="submit">Submitting...</span>
        </button>
        @endif
        <a href="{{ route('payments.batches.show', $batch) }}" class="text-green-600 hover:text-green-800 text-sm">View Details</a>
    </div>

    {{-- Invalid Items --}}
    @if($invalidItems && $invalidItems->isNotEmpty())
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b bg-red-50">
            <h3 class="text-md font-semibold text-red-800">Invalid Records ({{ $invalidItems->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Errors</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($invalidItems as $item)
                    <tr class="bg-red-50">
                        <td class="px-4 py-3 text-sm">{{ $item->row_number }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->employee_name ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->phone_number_raw ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->amount ? number_format($item->amount, 2) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-red-600">
                            @foreach($item->validation_errors ?? [] as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Valid Items --}}
    @if($validItems && $validItems->isNotEmpty())
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b bg-green-50">
            <h3 class="text-md font-semibold text-green-800">Valid Records ({{ $validItems->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Narration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($validItems as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $item->row_number }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->employee_name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->employee_code ?: '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->normalized_phone }}</td>
                        <td class="px-4 py-3 text-sm">KES {{ number_format($item->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm">{{ $item->narration ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>
