<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'M-Pesa Bulk Payments' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex h-full">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:leave="transition-opacity ease-linear duration-200" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-200 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex w-64 flex-col bg-white shadow-lg h-full">
                @include('layouts.partials.sidebar-content')
            </div>
        </div>

        {{-- Desktop sidebar --}}
        <div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0">
            <div class="flex flex-col flex-grow bg-white border-r border-slate-200 overflow-y-auto">
                @include('layouts.partials.sidebar-content')
            </div>
        </div>

        {{-- Main content --}}
        <div class="flex flex-1 flex-col lg:pl-64">
            {{-- Top bar --}}
            <header class="sticky top-0 z-30 bg-white border-b border-slate-200">
                <div class="flex h-14 items-center justify-between px-4 sm:px-6">
                    <button @click="sidebarOpen = true" class="lg:hidden -ml-1 p-1.5 rounded-md text-slate-500 hover:text-slate-700 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <div class="flex items-center gap-3 ml-auto">
                        <span class="text-sm text-slate-500">{{ auth()->user()->name }}</span>
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                            {{ auth()->user()->roles->first()?->name ?? 'User' }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800" x-data="{ show: true }" x-show="show">
                        {{ session('error') }}
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
