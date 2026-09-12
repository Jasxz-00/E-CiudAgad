@extends('layouts.guest')

@section('title', 'Admin Login')

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
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                    Admin Login
                </h1>
            </div>

            <form method="POST"
                  action="{{ route('admin.login') }}"
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