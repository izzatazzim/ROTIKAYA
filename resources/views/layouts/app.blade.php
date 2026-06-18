<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Rotikaya Media') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-black text-white font-sans min-h-screen" x-data="{ sidebarOpen: false }">

    {{-- Mobile top header bar — hidden on desktop --}}
    <header class="lg:hidden fixed top-0 left-0 right-0 z-30 h-14 flex items-center justify-between px-4 bg-[#0a0a0a] border-b border-white/[0.06]">
        <button @click="sidebarOpen = true"
                class="flex items-center justify-center w-10 h-10 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition"
                aria-label="Open menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <img src="{{ asset('images/rotikaya-logo.png') }}" alt="Rotikaya Media" class="h-8 w-auto object-contain">
        @php
            $headerParts = explode(' ', trim(auth()->user()?->name ?? 'U'));
            $headerInitials = strtoupper(count($headerParts) >= 2
                ? substr($headerParts[0], 0, 1) . substr(end($headerParts), 0, 1)
                : substr($headerParts[0], 0, 1));
        @endphp
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/[0.08] text-xs font-semibold text-gray-200">
            {{ $headerInitials }}
        </div>
    </header>

    {{-- Mobile backdrop — hidden md+ --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="md:hidden fixed inset-0 z-40 bg-black/50"></div>

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="fixed left-0 top-0 h-screen z-50 border-r border-white/[0.06] bg-[#0a0a0a] flex flex-col
                      transition-transform duration-300 will-change-transform
                      w-60 md:w-[60px] lg:w-60
                      md:translate-x-0"
               :class="{ '-translate-x-full': !sidebarOpen }">

            {{-- Logo bar --}}
            <div class="h-14 flex items-center px-3 shrink-0 md:justify-center lg:justify-start">
                <img src="{{ asset('images/rotikaya-logo.png') }}" alt="Rotikaya Media"
                     class="h-10 w-auto object-contain md:hidden lg:block shrink-0">
                <img src="{{ asset('images/rotikaya-logo.png') }}" alt="Rotikaya Media"
                     class="hidden md:block lg:hidden h-8 w-auto object-contain shrink-0">
                <button @click="sidebarOpen = false"
                        class="md:hidden ml-auto p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition"
                        aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 min-h-0 overflow-y-auto px-2 pb-2 space-y-1">
                @php $role = auth()->user()?->role?->name; @endphp

                <a href="{{ route('dashboard') }}" title="Dashboard"
                   class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                          {{ request()->routeIs('dashboard') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="inline md:hidden lg:inline truncate">Dashboard</span>
                </a>

                @if ($role === 'admin')
                    <a href="{{ route('users.index') }}" title="Users"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('users.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Users</span>
                    </a>
                    <a href="{{ route('permissions.index') }}" title="Roles &amp; Access"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('permissions.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Roles &amp; Access</span>
                    </a>
                    <a href="{{ route('settings.index') }}" title="Settings"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('settings.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Settings</span>
                    </a>
                @elseif ($role === 'accountant')
                    <a href="{{ route('invoices.index') }}" title="Invoices"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('invoices.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Invoices</span>
                    </a>
                    <a href="{{ route('payments.index') }}" title="Payments"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('payments.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Payments</span>
                    </a>
                    <a href="{{ route('reports.index') }}" title="Reports"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('reports.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Reports</span>
                    </a>
                @else
                    {{-- Sales Staff --}}
                    <a href="{{ route('invoices.index') }}" title="Invoices"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('invoices.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Invoices</span>
                    </a>
                    <a href="{{ route('clients.index') }}" title="Customers"
                       class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium transition md:justify-center lg:justify-start
                              {{ request()->routeIs('clients.*') || request()->routeIs('sales.*') ? 'bg-red-600/10 text-red-400 border-l-2 border-red-500' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="inline md:hidden lg:inline truncate">Customers</span>
                    </a>
                @endif
            </nav>

            {{-- User Info Block --}}
            <div class="shrink-0 border-t border-white/[0.06] p-3">
                @php
                    $sidebarParts = explode(' ', trim(auth()->user()?->name ?? 'U'));
                    $sidebarInitials = strtoupper(count($sidebarParts) >= 2
                        ? substr($sidebarParts[0], 0, 1) . substr(end($sidebarParts), 0, 1)
                        : substr($sidebarParts[0], 0, 1));
                @endphp

                {{-- Full block: mobile drawer + desktop --}}
                <div class="flex items-center gap-3 mb-3 md:hidden lg:flex">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/[0.08] text-xs font-semibold text-gray-200">
                        {{ $sidebarInitials }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ auth()->user()?->name }}</p>
                        <p class="text-xs text-gray-400">{{ ucwords(str_replace('_', ' ', auth()->user()?->role?->name ?? '')) }}</p>
                    </div>
                </div>

                {{-- Avatar only: tablet icons mode --}}
                <div class="hidden md:flex lg:hidden justify-center mb-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/[0.08] text-xs font-semibold text-gray-200"
                         title="{{ auth()->user()?->name }}">
                        {{ $sidebarInitials }}
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    {{-- Text: mobile drawer only (desktop sign-out lives in the top bar) --}}
                    <button type="submit" class="text-xs text-gray-500 hover:text-white transition md:hidden w-full text-left py-1">
                        Sign out
                    </button>
                    {{-- Icon: tablet --}}
                    <button type="submit" title="Sign out"
                            class="hidden md:flex lg:hidden justify-center w-full p-2 rounded-lg text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex flex-col flex-1 min-w-0 pt-14 lg:pt-0 ml-0 md:ml-[60px] lg:ml-60 bg-[#0a0a0a] min-h-screen">
            @php
                $rtkSection = match (true) {
                    request()->routeIs('dashboard') => 'Dashboard',
                    request()->routeIs('invoices.*') => 'Invoices',
                    request()->routeIs('payments.*') => 'Payments',
                    request()->routeIs('reports.*') => 'Reports',
                    request()->routeIs('sales.*'), request()->routeIs('clients.*') => 'Customers',
                    request()->routeIs('users.*') => 'Users',
                    request()->routeIs('settings.*') => 'Settings',
                    request()->routeIs('permissions.*') => 'Roles & Access',
                    default => 'Home',
                };
            @endphp

            {{-- Desktop top bar: breadcrumb (left) + user menu (right) --}}
            <div class="hidden lg:flex sticky top-0 z-20 h-14 shrink-0 items-center justify-between gap-4 border-b border-white/[0.06] bg-[#0a0a0a] px-8">
                <nav class="flex items-center gap-2 text-sm" aria-label="Breadcrumb">
                    <span class="text-gray-500">Rotikaya Media</span>
                    <span class="text-gray-700">/</span>
                    <span class="font-medium text-gray-200">{{ $rtkSection }}</span>
                </nav>

                <div x-data="{ userMenu: false }" class="relative">
                    <button @click="userMenu = !userMenu" type="button"
                            class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-300 transition hover:bg-white/5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/[0.08] text-xs font-semibold text-gray-200">{{ $sidebarInitials }}</span>
                        <span class="hidden max-w-[140px] truncate xl:inline">{{ auth()->user()?->name }}</span>
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="userMenu" x-cloak @click.outside="userMenu = false" x-transition.opacity
                         class="absolute right-0 mt-2 w-56 overflow-hidden rounded-lg border border-white/10 bg-[#1c1c1c] shadow-2xl">
                        <div class="border-b border-white/[0.06] px-4 py-3">
                            <p class="truncate text-sm font-medium text-white">{{ auth()->user()?->name }}</p>
                            <p class="truncate text-xs text-gray-500">{{ auth()->user()?->email }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', auth()->user()?->role?->name ?? '')) }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-left text-sm text-gray-300 transition hover:bg-white/5">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Page content well: black surface against the lighter chrome = contained "app" feel --}}
            <div class="flex-1 bg-black px-6 lg:px-8 pt-8 lg:pt-10 pb-10 lg:rounded-tl-2xl">
                <div class="max-w-7xl mx-auto">
                    <x-flash-messages />
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
