<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Roles & Permissions</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage access control</p>
        </div>
        <button wire:click="$set('showCreateModal', true)" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Role
        </button>
    </div>

    {{-- Roles list --}}
    <div class="space-y-3">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">{{ $role->name }}</h3>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach($role->permissions as $permission)
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $permission->name }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mt-2">{{ $role->users()->count() }} user(s) assigned</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="editRole({{ $role->id }})" class="text-xs font-medium text-blue-600 hover:text-blue-700">Edit</button>
                    @if($role->users()->count() === 0)
                    <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role?" class="text-xs font-medium text-red-600 hover:text-red-700">Delete</button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- All permissions reference --}}
    <div class="mt-6 bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Available Permissions</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($permissions as $permission)
            <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ $permission->name }}</span>
            @endforeach
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $editingRoleId ? 'Edit Role' : 'Create Role' }}</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role Name</label>
                    <input wire:model="newRoleName" type="text" placeholder="e.g. finance_manager" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('newRoleName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Permissions</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto">
                        @foreach($permissions as $permission)
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50 transition">
                            <input wire:model="selectedPermissions" type="checkbox" value="{{ $permission->name }}" class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-slate-700">{{ $permission->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('selectedPermissions') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showCreateModal', false)" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                @if($editingRoleId)
                    <button wire:click="updateRole" class="rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800">Update Role</button>
                @else
                    <button wire:click="createRole" class="rounded-lg bg-slate-900 px-3.5 py-2 text-sm font-medium text-white hover:bg-slate-800">Create Role</button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
