<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'M-Pesa Salary System' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    {{-- Navigation --}}
    <nav class="bg-white shadow-sm border-b" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-green-600 whitespace-nowrap">M-Pesa Salary</a>
                    <div class="hidden lg:flex space-x-4">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Dashboard</a>

                        @can('payment-batches.upload')
                        <a href="{{ route('payments.upload') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('payments.upload') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Upload</a>
                        @endcan

                        <a href="{{ route('payments.batches') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('payments.batches*') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Batches</a>

                        @can('payment-batches.approve')
                        <a href="{{ route('payments.approvals') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('payments.approvals') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Approvals</a>
                        @endcan

                        @can('reports.view')
                        <a href="{{ route('reports.payments') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.*') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Reports</a>
                        @endcan

                        @can('audit-logs.view')
                        <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('audit-logs.*') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Audit Logs</a>
                        @endcan

                        @role('Admin')
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.*') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700' }}">Users</a>
                        @endrole
                    </div>
                </div>
                <div class="hidden lg:flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">{{ auth()->user()->roles->pluck('name')->join(', ') }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                    </form>
                </div>
                {{-- Mobile menu button --}}
                <div class="flex items-center lg:hidden">
                    <button @click="mobileOpen = !mobileOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                        <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-transition class="lg:hidden border-t" style="display:none;">
            <div class="px-4 pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('dashboard') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Dashboard</a>

                @can('payment-batches.upload')
                <a href="{{ route('payments.upload') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('payments.upload') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Upload</a>
                @endcan

                <a href="{{ route('payments.batches') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('payments.batches*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Batches</a>

                @can('payment-batches.approve')
                <a href="{{ route('payments.approvals') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('payments.approvals') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Approvals</a>
                @endcan

                @can('reports.view')
                <a href="{{ route('reports.payments') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('reports.*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Reports</a>
                @endcan

                @can('audit-logs.view')
                <a href="{{ route('audit-logs.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('audit-logs.*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Audit Logs</a>
                @endcan

                @role('Admin')
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">Users</a>
                @endrole
            </div>
            <div class="border-t px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="max-w-7xl mx-auto py-4 px-3 sm:py-6 sm:px-6 lg:px-8">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
