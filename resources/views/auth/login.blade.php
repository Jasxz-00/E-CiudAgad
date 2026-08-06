@extends('layouts.guest')

@section('title', 'Log In')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <x-card>
            @if(session('insistence_submitted'))
                <div class="mb-4 p-3 bg-accent-50 dark:bg-accent-900/20 text-accent-700 dark:text-accent-300 rounded-xl text-sm">
                    Your claim has been submitted. Barangay Staff will review your case and contact you.
                </div>
            @endif

            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary-50 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Welcome Back</h1>
            </div>

            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6" id="login-tabs">
                <button type="button" data-tab="resident" class="tab-btn active flex-1">
                    Resident Login
                </button>
                <button type="button" data-tab="staff" class="tab-btn flex-1">
                    Staff Login
                </button>
            </div>

            <div data-tab-content="resident" class="tab-content">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="login_type" value="resident">

                    <x-input name="tracking_number" label="Tracking Number" required
                        placeholder="e.g., EC-ABC-123"
                        class="uppercase-input"
                        autocomplete="off"
                        :error="$errors->first('tracking_number')" />

                    <x-input name="pin" label="PIN" type="password" required
                        placeholder="Enter your 6-digit PIN"
                        maxlength="6" inputmode="numeric"
                        autocomplete="current-password"
                        :error="$errors->first('pin')" />

                    <x-checkbox name="remember" label="Remember me" :checked="old('remember')" />

                    <button type="submit" class="btn-primary w-full">Log In</button>

                    <div class="text-center">
                        <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-primary-700 dark:text-primary-400 font-medium hover:underline">Forgot PIN?</a>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account yet?
                    <a href="{{ route('register') }}" wire:navigate class="text-primary-700 dark:text-primary-400 font-medium hover:underline">Register here</a>
                </p>
            </div>

            <div data-tab-content="staff" class="tab-content" style="display:none;">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="login_type" value="staff">

                    <x-input name="login" label="Username or Email" required
                        placeholder="Enter your username or email"
                        autocomplete="username"
                        :error="$errors->first('login')" />

                    <x-input name="password" label="Password" type="password" required
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        :error="$errors->first('password')" />

                    <x-checkbox name="remember" label="Remember me" :checked="old('remember')" />

                    <button type="submit" class="btn-primary w-full">Log In</button>
                </form>
            </div>
        </x-card>
    </div>
</div>

<script>
    (function () {
        const pinInput = document.querySelector('input[name="pin"]');
        if (pinInput) {
            pinInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        }

        var activeTab = '{{ old('login_type') === 'staff' ? 'staff' : 'resident' }}';
        var tabBtns = document.querySelectorAll('.tab-btn');
        var tabContents = document.querySelectorAll('[data-tab-content]');

        tabBtns.forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === activeTab);
        });
        tabContents.forEach(function(content) {
            content.style.display = content.getAttribute('data-tab-content') === activeTab ? 'block' : 'none';
        });
    })();
</script>
@endsection
