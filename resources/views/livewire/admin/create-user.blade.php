<div>
    <div class="mb-6">
        <a href="{{ route('admin.users') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to Users</a>
        <h1 class="text-xl font-semibold text-slate-900 mt-2">Create User</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-lg">
        <form wire:submit="create" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input wire:model="name" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input wire:model="email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input wire:model="password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                <input wire:model="password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Roles</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50 transition">
                        <input wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700">{{ $role->name }}</span>
                    </label>
                    @endforeach
                </div>
                @error('selectedRoles') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">Create User</button>
                <a href="{{ route('admin.users') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
