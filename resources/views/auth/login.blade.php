@extends('layouts.guest')

@section('title', 'Resident Login')

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
                    <svg class="w-8 h-8 text-primary-700 dark:text-primary-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Welcome Back
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Resident Login
                </p>
            </div>

            <form method="POST"
                  action="{{ route('resident.login') }}"
                  class="space-y-5">
                @csrf

                <x-input
                    name="tracking_number"
                    label="Tracking Number"
                    required
                    placeholder="e.g., EC-ABC-123"
                    class="uppercase-input"
                    autocomplete="off"
                    :error="$errors->first('tracking_number')"
                />

                <x-input
                    name="pin"
                    label="PIN"
                    type="password"
                    required
                    placeholder="Enter your 6-digit PIN"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="current-password"
                    :error="$errors->first('pin')"
                />

                <x-checkbox
                    name="remember"
                    label="Remember me"
                    :checked="old('remember')"
                />

                <button type="submit"
                        class="btn-primary w-full">
                    Log In
                </button>

                <div class="text-center">
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-primary-700 dark:text-primary-400 font-medium hover:underline">
                        Forgot PIN?
                    </a>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Don't have an account yet?
                <a href="{{ route('register') }}"
                   class="text-primary-700 dark:text-primary-400 font-medium hover:underline">
                    Register here
                </a>
            </p>

        </x-card>
    </div>
</div>

<script>
    const pinInput = document.querySelector('input[name="pin"]');

    if (pinInput) {
        pinInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }
</script>
@endsection