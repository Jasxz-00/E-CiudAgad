@extends('layouts.guest')

@section('title', 'Page Not Found')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <h1 class="text-8xl font-bold text-primary-700 dark:text-primary-400 mb-4">404</h1>
        <div class="w-20 h-20 bg-accent-50 dark:bg-accent-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-accent-700 dark:text-accent-300 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Page Not Found</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8">The page you're looking for doesn't exist or has been moved.</p>
        <div class="flex gap-3 justify-center">
            <a href="{{ url()->previous() }}" class="btn-ghost">Go Back</a>
            <a href="{{ url('/') }}" class="btn-primary">Go Home</a>
        </div>
    </div>
</div>
@endsection
