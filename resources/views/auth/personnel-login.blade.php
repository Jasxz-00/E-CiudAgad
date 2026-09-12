@extends('layouts.guest')

@section('title', 'Personnel Login')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <x-card>

            <div class="text-center mb-7">
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

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Personnel Portal
                </p>
            </div>

            <form method="POST"
                  action="{{ route('personnel.login') }}"
                  class="space-y-5">
                @csrf

                <x-input
                    name="login"
                    label="Username or Email"
                    required
                    placeholder="Enter your username or email"
                    autocomplete="username"
                    :error="$errors->first('login')"
                />

                <x-input
                    name="password"
                    label="Password"
                    type="password"
                    required
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    :error="$errors->first('password')"
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
            </form>

        </x-card>
    </div>
</div>
@endsection