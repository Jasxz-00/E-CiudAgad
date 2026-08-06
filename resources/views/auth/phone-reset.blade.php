@extends('layouts.guest')

@section('title', __('auth.reset_password'))

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <x-card>
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-600/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('auth.reset_password') }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">Phone verified successfully. Choose a new password.</p>
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
                <input type="hidden" name="token" value="{{ session('phone_reset_token') }}">
                <input type="hidden" name="email" value="{{ optional(\App\Models\User::find(session('phone_reset_user_id')))->email }}">

                <div class="p-3 bg-gray-50 dark:bg-gray-950 rounded-xl text-sm text-gray-600 dark:text-gray-400">
                    Phone number verified. Set your new password below.
                </div>

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
