@extends('layouts.guest')

@section('title', 'Account Created')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <x-card class="text-center">
            <div class="w-20 h-20 bg-green-600/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Welcome to E-CiudAgad!</h1>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Your account has been created successfully. Please save your credentials below.</p>

            <div class="bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl p-6 mb-6 text-left">
                <h2 class="font-semibold text-accent-700 dark:text-accent-300 mb-4">Your Account Credentials</h2>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">Tracking Number</label>
                        <p class="text-lg font-mono font-bold text-gray-900 dark:text-gray-100 break-all" id="tracking_number">{{ $tracking_number ?? session('credentials.tracking_number') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">PIN</label>
                        <p class="text-lg font-mono font-bold text-gray-900 dark:text-gray-100 break-all" id="pin">{{ $pin ?? session('credentials.pin') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">Email</label>
                        <p class="text-lg font-mono text-gray-900 dark:text-gray-100 break-all">{{ $email ?? session('credentials.email') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">Queue Number</label>
                        <p class="text-lg font-mono font-bold text-primary-700 dark:text-primary-400">{{ $queue_number ?? session('credentials.queue_number') }}</p>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-accent-50 dark:bg-accent-900/20 rounded-lg">
                    <p class="text-sm text-accent-700 dark:text-accent-300">
                        <strong>IMPORTANT:</strong> Please save these credentials. You will need them to log in and track your document request. These credentials will not be shown again.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="window.print()" class="btn-accent">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Print
                </button>
                <a href="{{ route('login') }}" wire:navigate class="btn-primary">Proceed to Log In</a>
            </div>
        </x-card>
    </div>
</div>

<style>
    @@media print {
        header, footer, .btn-accent, .btn-primary:not(.btn-primary-print) { display: none !important; }
        .card { box-shadow: none !important; border: 2px solid #000 !important; }
        body { background: white !important; }
    }
</style>
@endsection
