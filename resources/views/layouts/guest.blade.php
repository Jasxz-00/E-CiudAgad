<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="E-CiudAgad Home">
                    <div class="w-10 h-10 flex items-center justify-center" style="background-color:#1E40AF;border-radius:0.75rem;">
                        <span class="text-white font-bold text-lg">EC</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-primary-800 dark:text-primary-400">E-CiudAgad</span>
                    </div>
                </a>

                <button id="theme-toggle" type="button" class="btn-ghost p-2" aria-label="Toggle dark mode">
                    <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
