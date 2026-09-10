@extends('layouts.app')

@section('title', 'Request Submitted')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <x-card>
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('registration.request_submitted') }}</h1>
            </div>

            <div class="space-y-4 mb-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('registration.control_number') }}</p>
                    <p class="text-lg font-bold text-primary-700 dark:text-primary-400 font-mono">{{ $documentRequest->control_number }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('registration.queue_number') }}</p>
                    <p class="text-lg font-bold text-primary-700 dark:text-primary-400 font-mono">{{ $documentRequest->queue_number }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('common.status') }}</p>
                    <p class="text-lg font-medium capitalize"><x-badge :status="$documentRequest->status" /></p>
                </div>
                @if($documentRequest->qr_code)
                    <div class="text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ __('registration.qr_code_label') }}</p>
                        {!! QrCode::size(200)->generate($documentRequest->control_number) !!}
                    </div>
                @endif
            </div>

            <div class="p-4 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 rounded-xl text-sm text-primary-700 dark:text-primary-400">
                <p class="font-medium">{{ __('registration.keep_credentials') }}</p>
                <p class="mt-1 text-xs">{{ __('registration.keep_credentials_hint') }}</p>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('resident.dashboard') }}" wire:navigate class="btn-primary inline-block">{{ __('common.go_to_dashboard') }}</a>
            </div>
        </x-card>
    </div>
</div>
@endsection
