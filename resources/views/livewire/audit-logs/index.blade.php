<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Audit Logs</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="User, action, IP..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
                <select wire:model.live="actionFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}">{{ str_replace('_', ' ', ucfirst($action)) }}</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ class_basename($log->auditable_type ?? '') }} #{{ $log->auditable_id }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($log->new_values)
                                <details class="cursor-pointer">
                                    <summary class="text-blue-600 text-xs">View Changes</summary>
                                    <div class="mt-2 text-xs bg-gray-50 p-2 rounded max-w-xs overflow-auto">
                                        <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </details>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No audit logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t">
            {{ $logs->links() }}
        </div>
    </div>

    {{-- Mobile card view --}}
    <div class="sm:hidden space-y-3">
        @forelse($logs as $log)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    {{ str_replace('_', ' ', $log->action) }}
                </span>
                <span class="text-xs text-gray-400">{{ $log->created_at->format('M d, H:i') }}</span>
            </div>
            <p class="text-sm font-medium text-gray-700">{{ $log->user->name ?? 'System' }}</p>
            <p class="text-xs text-gray-500">{{ class_basename($log->auditable_type ?? '') }} #{{ $log->auditable_id }}</p>
            @if($log->ip_address)
                <p class="text-xs text-gray-400 mt-1">IP: {{ $log->ip_address }}</p>
            @endif
            @if($log->new_values)
                <details class="mt-2 cursor-pointer">
                    <summary class="text-blue-600 text-xs">View Changes</summary>
                    <div class="mt-1 text-xs bg-gray-50 p-2 rounded overflow-auto max-h-32">
                        <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </details>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">No audit logs found.</div>
        @endforelse
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
