@extends('layouts.guest')

@section('title', 'Session Expired')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <h1 class="text-8xl font-bold text-primary-700 dark:text-primary-400 mb-4">419</h1>
        <div class="w-20 h-20 bg-accent-50 dark:bg-accent-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-accent-700 dark:text-accent-300 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Session Expired</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Your session has expired. Please refresh the page and try again.</p>
        <a href="{{ url('/') }}" class="btn-primary">Go Home</a>
    </div>
</div>
@endsection
