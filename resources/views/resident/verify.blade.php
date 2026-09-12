@extends('layouts.app')

@section('title', __('registration.verify_request'))

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <x-card>
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('registration.verify_request') }}</h1>
            </div>

            <div class="space-y-4 mb-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('registration.queue_number') }}</p>
                    <p class="text-lg font-bold text-primary-700 dark:text-primary-400 font-mono">{{ $documentRequest->queue_number }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.document') }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $documentRequest->documentType?->name ?? '-' }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.purpose') }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $documentRequest->purpose?->name ?? $documentRequest->purpose_other ?? '-' }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.status') }}</p>
                    <p class="text-lg font-medium"><x-badge :status="$documentRequest->status" /></p>
                </div>
                @if($documentRequest->service_date)
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('registration.processing_date') }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $documentRequest->service_date->format('M d, Y') }}</p>
                </div>
                @endif
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="btn-ghost inline-block">{{ __('common.back_to_home') }}</a>
            </div>
        </x-card>
    </div>
</div>
@endsection