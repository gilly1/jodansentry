<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">JodanPay</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to your account</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form wire:submit="login" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input wire:model="email" type="email" id="email" autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition" placeholder="you@company.com">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input wire:model="password" type="password" id="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition" placeholder="••••••••">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center">
                <input wire:model="remember" type="checkbox" id="remember" class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
            </div>

            <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50 transition">
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login">Signing in...</span>
            </button>
        </form>
    </div>
</div>
