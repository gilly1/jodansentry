<div>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Audit Logs</h1>
        <p class="text-sm text-slate-500 mt-0.5">System activity history</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            <select wire:model.live="actionFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}">{{ $action }}</option>
                @endforeach
            </select>
            <input wire:model.live="dateFrom" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            <input wire:model.live="dateTo" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Date</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">User</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Action</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Subject</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">IP</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition-colors" x-data="{ expanded: false }">
                        <td class="px-5 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-3 text-slate-700">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $log->action }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-600 text-xs">
                            @if($log->auditable_type)
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs font-mono">{{ $log->ip_address }}</td>
                        <td class="px-5 py-3">
                            @if($log->new_values || $log->metadata)
                            <button @click="expanded = !expanded" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                <span x-show="!expanded">View</span>
                                <span x-show="expanded" x-cloak>Hide</span>
                            </button>
                            @else
                            <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @if($log->new_values || $log->metadata)
                    <tr x-show="expanded" x-cloak>
                        <td colspan="6" class="px-5 py-3 bg-slate-50">
                            <pre class="text-xs text-slate-600 overflow-x-auto whitespace-pre-wrap max-h-40">{{ json_encode($log->new_values ?? $log->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No audit logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
