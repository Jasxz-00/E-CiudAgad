@extends('layouts.guest')

@section('title', __('auth.reset_password'))

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <x-card>
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary-50 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('auth.reset_password') }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">Choose a new password for your account.</p>
            </div>

            @if($errors->any())
                <x-alert type="error" dismissible>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <x-input name="email" label="{{ __('auth.email') }}" type="email" required
                    value="{{ $email ?? old('email') }}"
                    placeholder="Enter your email address"
                    autocomplete="email"
                    :error="$errors->first('email')" />

                <x-input name="password" label="{{ __('auth.new_password') }}" type="password" required
                    placeholder="Minimum 8 characters"
                    autocomplete="new-password"
                    :error="$errors->first('password')" />

                <x-input name="password_confirmation" label="{{ __('auth.confirm_password') }}" type="password" required
                    placeholder="Re-enter new password"
                    autocomplete="new-password"
                    :error="$errors->first('password_confirmation')" />

                <button type="submit" class="btn-primary w-full">{{ __('auth.reset_password') }}</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('login') }}" wire:navigate class="text-primary-700 dark:text-primary-400 font-medium hover:underline">{{ __('auth.login') }}</a>
            </p>
        </x-card>
    </div>
</div>

<script>
    (function () {
        var pwd = document.querySelector('input[name="password"]');
        var pwdConfirm = document.querySelector('input[name="password_confirmation"]');

        if (pwd) {
            pwd.addEventListener('input', function() {
                if (pwdConfirm && pwdConfirm.value) {
                    validateMatch();
                }
            });
        }

        if (pwdConfirm) {
            pwdConfirm.addEventListener('input', validateMatch);
        }

        function validateMatch() {
            if (pwd.value !== pwdConfirm.value) {
                pwdConfirm.classList.add('input-error');
            } else {
                pwdConfirm.classList.remove('input-error');
            }
        }
    })();
</script>
@endsection
