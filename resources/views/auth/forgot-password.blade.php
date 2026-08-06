@extends('layouts.guest')

@section('title', __('auth.forgot_password_title'))

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <x-card>
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-accent-50 dark:bg-accent-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-accent-700 dark:text-accent-300 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('auth.forgot_password_title') }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">{{ __('auth.forgot_password_desc') }}</p>
            </div>

            @if(session('status'))
                <x-alert type="success" dismissible>{{ session('status') }}</x-alert>
            @endif

            @if(session('phone_otp_sent'))
                <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 border border-success/20 rounded-xl text-sm">
                    {{ session('status') ?? __('auth.reset_code_sent') }}
                    <form method="POST" action="{{ route('password.verify-phone') }}" class="mt-3 space-y-3">
                        @csrf
                        <input type="hidden" name="contact_number" value="{{ session('phone_reset_contact') }}">
                        <x-input name="otp" label="{{ __('auth.otp_code') }}" required
                            placeholder="000000" maxlength="6"
                            autocomplete="one-time-code"
                            :error="$errors->first('otp')" />
                        <button type="submit" class="btn-primary w-full">{{ __('auth.verify_otp') }}</button>
                    </form>
                    <p class="mt-2 text-xs opacity-75">Didn't receive a code? <a href="{{ route('password.request') }}" class="underline">Resend</a></p>
                </div>
            @else
                <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6" id="channel-tabs">
                    <button type="button" data-tab="email" class="tab-btn active flex-1">
                        {{ __('auth.email_path') }}
                    </button>
                    <button type="button" data-tab="phone" class="tab-btn flex-1">
                        {{ __('auth.phone_path') }}
                    </button>
                </div>

                <div data-tab-content="email" class="tab-content">
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="channel" value="email">

                        <x-input name="email" label="{{ __('auth.email') }}" type="email" required
                            placeholder="{{ __('auth.enter_email') }}"
                            autocomplete="email"
                            :error="$errors->first('email')" />

                        <button type="submit" class="btn-primary w-full">{{ __('auth.send_reset_link') }}</button>
                    </form>
                </div>

                <div data-tab-content="phone" class="tab-content" style="display:none;">
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="channel" value="phone">

                        <div>
                            <label for="contact_number" class="label">{{ __('auth.phone') }} <span class="text-red-600 dark:text-red-400">*</span></label>
                            <input id="contact_number" type="tel" name="contact_number" value="{{ old('contact_number') }}" required
                                class="input-field phone-mask @error('contact_number') input-error @enderror"
                                placeholder="{{ __('auth.enter_phone') }}" maxlength="13" autocomplete="tel">
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Format: 09XX-XXX-XXXX</p>
                            @error('contact_number') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full">{{ __('auth.send_reset_code') }}</button>
                    </form>
                </div>
            @endif

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ __('auth.remember_me') }}?
                <a href="{{ route('login') }}" wire:navigate class="text-primary-700 dark:text-primary-400 font-medium hover:underline">{{ __('auth.login') }}</a>
            </p>
        </x-card>
    </div>
</div>

<script>
    (function () {
        var tabBtns = document.querySelectorAll('#channel-tabs .tab-btn');
        var tabContents = document.querySelectorAll('[data-tab-content]');

        tabBtns.forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === 'email');
        });
        tabContents.forEach(function(content) {
            content.style.display = content.getAttribute('data-tab-content') === 'email' ? 'block' : 'none';
        });
    })();
</script>
@endsection
