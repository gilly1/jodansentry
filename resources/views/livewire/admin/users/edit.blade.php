<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit User: {{ $user->name }}</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-xl">
        <form wire:submit="update">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
                <input wire:model="password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input wire:model="password_confirmation" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                @foreach($roles as $role)
                <label class="inline-flex items-center mr-4 mb-2">
                    <input wire:model="selectedRoles" type="checkbox" value="{{ $role->name }}" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                </label>
                @endforeach
                @error('selectedRoles') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">Update User</button>
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
