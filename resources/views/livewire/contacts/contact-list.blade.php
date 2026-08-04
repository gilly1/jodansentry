<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Contacts</h1>
            <p class="text-sm text-slate-500 mt-0.5">Phonebook of all staff paid via M-Pesa</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name or phone number..." class="w-full sm:w-80 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Name (M-Pesa)</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Phone Number</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Total Payments</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Total Amount</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Last Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contacts as $contact)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('contacts.show', $contact) }}" class="font-medium text-blue-600 hover:text-blue-700">{{ $contact->mpesa_name }}</a>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $contact->phone_number }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ number_format($contact->total_transactions) }}</td>
                        <td class="px-5 py-3 font-medium text-slate-900">KES {{ number_format($contact->total_amount, 0) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $contact->last_paid_at?->format('M d, Y H:i') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                            No contacts found. Contacts are created automatically from successful M-Pesa payments.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contacts->hasPages())
        <div class="border-t border-slate-200 px-5 py-3">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>
</div>
