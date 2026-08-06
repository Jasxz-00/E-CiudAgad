<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="languageManager()" x-init="init">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            var t = localStorage.getItem('theme');
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <title>{{ config('app.name', 'E-CiudAgad') }} | @yield('title', 'Barangay Document Request System')</title>
    <meta name="description" content="@yield('meta_description', 'E-CiudAgad - Online Barangay Document Request and Processing Management System')">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex flex-col" x-data="{ showMobileMenu: false }">
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Main navigation">
            <div class="flex items-center justify-between h-16">
                @php
                    $homeRoute = auth()->check()
                        ? (auth()->user()->isAdmin() ? route('admin.dashboard')
                            : (auth()->user()->isPersonnel() ? route('personnel.dashboard')
                            : route('resident.dashboard')))
                        : url('/');
                @endphp
                <a href="{{ $homeRoute }}" wire:navigate class="flex items-center gap-3 min-w-0" aria-label="E-CiudAgad Home">
                    <div class="w-10 h-10 flex items-center justify-center shrink-0" style="background-color:#1E40AF;border-radius:0.75rem;">
                        <span class="text-white font-bold text-lg">EC</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-primary-800 dark:text-primary-400">E-CiudAgad</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400 block -mt-1 max-sm:hidden">Barangay Document System</span>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <button id="theme-toggle" type="button" class="btn-ghost p-2" aria-label="Toggle dark mode">
                        <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    @auth
                        <div class="hidden md:flex items-center gap-3">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn-ghost text-sm">Admin</a>
                            @elseif(auth()->user()->isPersonnel())
                                <a href="{{ route('personnel.dashboard') }}" wire:navigate class="btn-ghost text-sm">Dashboard</a>
                            @else
                                <a href="{{ route('resident.profile') }}" wire:navigate class="btn-ghost text-sm">{{ __('common.profile') }}</a>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                            @csrf
                            <button type="submit" class="btn-ghost text-sm">Log Out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="btn-ghost text-sm hidden sm:inline-flex">Log In</a>
                        <a href="{{ route('register') }}" wire:navigate class="btn-primary text-sm hidden sm:inline-flex">Get Started</a>
                    @endauth

                    @auth
                    <button type="button" class="md:hidden btn-ghost p-2" @@click="showMobileMenu = !showMobileMenu" aria-label="Toggle menu" aria-expanded="false">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    @endauth
                </div>
            </div>

            <div x-show="showMobileMenu" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="md:hidden pb-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex flex-col gap-2">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="px-3 py-2">
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium uppercase tracking-wider mb-2">Navigation</p>
                                <div class="space-y-0.5">
                                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                        Dashboard
                                    </a>
                                    <div x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.accounts.*') || request()->routeIs('admin.staff.*') ? 'true' : 'false' }} }">
                                        <button type="button" @@click="open = !open" class="sidebar-link w-full flex items-center justify-between text-sm {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.accounts.*') || request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                            <span class="flex items-center gap-3">
                                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                                Users
                                            </span>
                                            <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" class="ml-8 mt-0.5 space-y-0.5">
                                            <a href="{{ route('admin.users.index') }}" wire:navigate class="sidebar-link text-xs block {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.accounts.*') && !request()->routeIs('admin.staff.*') ? 'active' : '' }}">All Users</a>
                                            <a href="{{ route('admin.accounts.index') }}" wire:navigate class="sidebar-link text-xs block {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">Admin Accounts</a>
                                            <a href="{{ route('admin.staff.index') }}" wire:navigate class="sidebar-link text-xs block {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">Staff Accounts</a>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.duplicate-claims.index') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.duplicate-claims.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                                        Duplicate Claims
                                    </a>
                                    <a href="{{ route('admin.requests.index') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Requests
                                    </a>
                                    <a href="{{ route('admin.wfq.index') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.wfq.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                        WFQ
                                    </a>
                                    <a href="{{ route('admin.document-types.index') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                        Master Data
                                    </a>
                                    <a href="{{ route('admin.announcements.index') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                        Announcements
                                    </a>
                                    <a href="{{ route('admin.reports.index') }}" wire:navigate class="sidebar-link w-full flex items-center gap-3 text-sm {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Reports
                                    </a>
                                </div>
                            </div>
                            <hr class="border-gray-200 dark:border-gray-700 mx-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-ghost justify-start w-full text-left">Log Out</button>
                            </form>
                        @elseif(auth()->user()->isPersonnel())
                            <a href="{{ route('personnel.dashboard') }}" wire:navigate class="btn-ghost justify-start">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-ghost justify-start w-full text-left">Log Out</button>
                            </form>
                        @else
                            <a href="{{ route('resident.dashboard') }}" wire:navigate class="btn-ghost justify-start">{{ __('common.dashboard') }}</a>
                            <a href="{{ route('resident.profile') }}" wire:navigate class="btn-ghost justify-start">{{ __('common.profile') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-ghost justify-start w-full text-left">Log Out</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @auth
            @if(auth()->user()->isAdmin())
            <div class="flex">
                <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 min-h-[calc(100vh-4rem)] shrink-0 hidden lg:block">
                    <nav class="p-4 space-y-1 text-sm" aria-label="Sidebar navigation">
                        <a href="{{ route('admin.dashboard') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            Dashboard
                        </a>
                        <div x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.accounts.*') || request()->routeIs('admin.staff.*') ? 'true' : 'false' }} }">
                            <button type="button" @@click="open = !open" class="sidebar-link w-full flex items-center justify-between {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.accounts.*') || request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                <span class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                    Users
                                </span>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" class="ml-6 mt-1 space-y-1">
                                <a href="{{ route('admin.users.index') }}" wire:navigate class="sidebar-link text-xs {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.accounts.*') && !request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                    All Users
                                </a>
                                <a href="{{ route('admin.accounts.index') }}" wire:navigate class="sidebar-link text-xs {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                                    Admin Accounts
                                </a>
                                <a href="{{ route('admin.staff.index') }}" wire:navigate class="sidebar-link text-xs {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                    Staff Accounts
                                </a>
                            </div>
                        </div>
                        <a href="{{ route('admin.duplicate-claims.index') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.duplicate-claims.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                            Duplicate Claims
                        </a>
                        <a href="{{ route('admin.requests.index') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Requests
                        </a>
                        <a href="{{ route('admin.wfq.index') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.wfq.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            WFQ
                        </a>
                        <a href="{{ route('admin.document-types.index') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.document-types.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                            Master Data
                        </a>
                        <a href="{{ route('admin.announcements.index') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                            Announcements
                        </a>
                        <a href="{{ route('admin.reports.index') }}" wire:navigate class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Reports
                        </a>
                    </nav>
                </aside>
                <div class="flex-1 min-w-0">
            @endif
        @endauth
        @yield('content')
        @auth
            @if(auth()->user()->isAdmin())
                </div>
            </div>
            @endif
        @endauth
    </main>

    <footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8" style="background-color:#1E40AF;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
                            <span class="text-white font-bold text-xs">EC</span>
                        </div>
                        <span class="font-bold text-primary-800 dark:text-primary-400">E-CiudAgad</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Online Barangay Document Request and Processing Management System.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li><a href="{{ url('/') }}" wire:navigate class="hover:text-primary-700 dark:hover:text-primary-400 transition-colors">Home</a></li>
                        <li><a href="{{ route('register') }}" wire:navigate class="hover:text-primary-700 dark:hover:text-primary-400 transition-colors">Request Document</a></li>
                        <li><a href="{{ route('login') }}" wire:navigate class="hover:text-primary-700 dark:hover:text-primary-400 transition-colors">Track Request</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li>Barangay Ciudad de Strike</li>
                        <li>Bacoor City, Cavite</li>
                        <li>Philippines</li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800 text-center text-sm text-gray-600 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} E-CiudAgad. All rights reserved. Barangay Document Request System.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
