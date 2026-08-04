<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Upload Payment Batch</h1>
        <p class="text-sm text-slate-500 mt-0.5">Upload an Excel or CSV file with payment details</p>
    </div>

    @if(!$batch)
    {{-- Upload area --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-900">File Upload</h2>
            <button wire:click="downloadTemplate" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Template
            </button>
        </div>

        <div class="relative border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-slate-400 transition-colors">
            <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            <p class="mt-3 text-sm text-slate-600">Drag and drop your file here, or click to browse</p>
            <p class="mt-1 text-xs text-slate-500">Supports .xlsx, .xls, .csv (max 10MB)</p>
            <input wire:model="file" type="file" accept=".xlsx,.xls,.csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
        </div>

        @error('file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

        <div wire:loading wire:target="file" class="mt-4 text-center">
            <p class="text-sm text-slate-600">Processing file...</p>
        </div>
    </div>

    {{-- Template info --}}
    <div class="mt-4 bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-2">Expected Format</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-slate-600">employee_name</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600">employee_code</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600">phone_number</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600">amount</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600">narration</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 text-slate-500">John Doe</td>
                        <td class="px-3 py-2 text-slate-500">EMP001</td>
                        <td class="px-3 py-2 text-slate-500">0712345678</td>
                        <td class="px-3 py-2 text-slate-500">50000</td>
                        <td class="px-3 py-2 text-slate-500">Salary June 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @else
    {{-- Batch preview --}}
    <div class="space-y-4">
        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-xs font-medium text-slate-500 uppercase">Total</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">{{ $batch->total_records }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-200 p-4 text-center">
                <p class="text-xs font-medium text-emerald-600 uppercase">Valid</p>
                <p class="mt-1 text-lg font-semibold text-emerald-700">{{ $batch->valid_records }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 p-4 text-center">
                <p class="text-xs font-medium text-red-600 uppercase">Invalid</p>
                <p class="mt-1 text-lg font-semibold text-red-700">{{ $batch->invalid_records }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-200 p-4 text-center">
                <p class="text-xs font-medium text-blue-600 uppercase">Amount</p>
                <p class="mt-1 text-lg font-semibold text-blue-700">KES {{ number_format($batch->total_amount, 0) }}</p>
            </div>
        </div>

        {{-- Invalid items --}}
        @if($batch->invalid_records > 0)
        <div class="bg-white rounded-xl border border-red-200">
            <div class="px-5 py-3 border-b border-red-100 bg-red-50 rounded-t-xl">
                <h3 class="text-sm font-semibold text-red-800">Invalid Records ({{ $batch->invalid_records }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Phone</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Errors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($batch->invalidItems as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $item->employee_name ?: '-' }}</td>
                            <td class="px-4 py-2">{{ $item->phone_number_raw }}</td>
                            <td class="px-4 py-2">{{ number_format($item->amount, 0) }}</td>
                            <td class="px-4 py-2">
                                @foreach($item->validation_errors ?? [] as $error)
                                    <span class="inline-block text-xs text-red-600">{{ $error }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Valid items --}}
        @if($batch->valid_records > 0)
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900">Valid Records ({{ $batch->valid_records }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Code</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Phone</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Narration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($batch->validItems as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 font-medium text-slate-900">{{ $item->employee_name }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $item->employee_code ?: '-' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item->normalized_phone }}</td>
                            <td class="px-4 py-2 font-medium text-slate-900">{{ number_format($item->amount, 0) }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $item->narration }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button wire:click="submit" wire:confirm="Submit this batch for approval?" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
                Submit for Approval
            </button>
            <a href="{{ route('payments.upload') }}" wire:navigate class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                Upload New File
            </a>
        </div>
    </div>
    @endif
</div>
