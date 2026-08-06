@extends('layouts.guest')

@section('title', 'Server Error')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <h1 class="text-8xl font-bold text-primary-700 dark:text-primary-400 mb-4">500</h1>
        <div class="w-20 h-20 bg-red-600/10 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Server Error</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Something went wrong on our end. Please try again later.</p>
        <a href="{{ url('/') }}" class="btn-primary">Go Home</a>
    </div>
</div>
@endsection
