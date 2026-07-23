<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 text-center">Create User</a>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name or email..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
    </div>

    {{-- Desktop table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @foreach($user->roles as $role)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-1">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                            <button wire:click="toggleActive({{ $user->id }})" class="text-{{ $user->is_active ? 'red' : 'green' }}-600 hover:text-{{ $user->is_active ? 'red' : 'green' }}-800">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Mobile card view --}}
    <div class="sm:hidden space-y-3">
        @forelse($users as $user)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-800">{{ $user->name }}</span>
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mb-2">{{ $user->email }}</p>
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($user->roles as $role)
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $role->name }}</span>
                @endforeach
            </div>
            <div class="flex items-center justify-between pt-2 border-t">
                <span class="text-xs text-gray-400">{{ $user->last_login_at?->format('M d, Y') ?? 'Never logged in' }}</span>
                <div class="space-x-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-blue-600 hover:text-blue-800">Edit</a>
                    <button wire:click="toggleActive({{ $user->id }})" class="text-xs text-{{ $user->is_active ? 'red' : 'green' }}-600">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">No users found.</div>
        @endforelse
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
