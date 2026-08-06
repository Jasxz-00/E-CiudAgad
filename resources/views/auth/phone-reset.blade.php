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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('auth.reset_pin_title', [], 'en') }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">{{ __('auth.reset_pin_desc_phone', [], 'en') }}</p>
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
                    Phone number verified. Set your new 6-digit PIN below.
                </div>

                <x-input name="pin" label="{{ __('auth.pin') }}" type="password" required
                    placeholder="Enter a new 6-digit PIN"
                    maxlength="6" inputmode="numeric"
                    autocomplete="new-password"
                    :error="$errors->first('pin')" />

                <x-input name="pin_confirmation" label="{{ __('auth.confirm_pin') }}" type="password" required
                    placeholder="Re-enter new PIN"
                    maxlength="6" inputmode="numeric"
                    autocomplete="new-password"
                    :error="$errors->first('pin_confirmation')" />

                <button type="submit" class="btn-primary w-full">{{ __('auth.reset_pin_title', [], 'en') }}</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('login') }}" wire:navigate class="text-primary-700 dark:text-primary-400 font-medium hover:underline">{{ __('auth.login') }}</a>
            </p>
        </x-card>
    </div>
</div>

<script>
    (function () {
        var pinInput = document.querySelector('input[name="pin"]');
        var pinConfirm = document.querySelector('input[name="pin_confirmation"]');

        if (pinInput) {
            pinInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
                if (pinConfirm && pinConfirm.value) {
                    validateMatch();
                }
            });
        }

        if (pinConfirm) {
            pinConfirm.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
                validateMatch();
            });
        }

        function validateMatch() {
            if (pinConfirm && pinConfirm.value !== pinInput.value) {
                pinConfirm.classList.add('input-error');
            } else if (pinConfirm) {
                pinConfirm.classList.remove('input-error');
            }
        }
    })();
</script>
@endsection
