@extends('layouts.app')

@section('title', $request->queue_number)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('resident.requests') }}" class="btn-ghost mb-4">&larr; {{ __('common.back_to_requests') }}</a>

    <div class="card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 break-words">{{ $request->queue_number }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->documentType?->name }}</p>
            </div>
            <span class="badge-{{ $request->status }} text-sm px-3 py-1">{{ ucfirst($request->status) }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6 sm:gap-4 text-sm">
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('common.purpose') }}</span>
                <p class="font-medium">{{ $request->purpose?->name }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('common.queue_position') }}</span>
                @if($request->queue_position && in_array($request->status, ['pending', 'reviewing']))
                    <p class="font-medium">{{ $request->queue_position }}</p>
                @else
                    <p class="font-medium">{{ __('common.queue_position_na') }}</p>
                @endif
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('registration.processing_date') }}</span>
                <p class="font-medium">{{ $request->service_date ? $request->service_date->format('M d, Y') : '—' }}</p>
            </div>
            @if($request->scheduled_after_cutoff)
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('registration.scheduled_after_cutoff_short') }}</span>
                <p class="font-medium text-accent-700 dark:text-accent-300">{{ __('registration.scheduled_after_cutoff_yes') }}</p>
            </div>
            @endif
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('common.date') }}</span>
                <p class="font-medium">{{ $request->created_at->format('M d, Y h:i A') }}</p>
            </div>
            @if($request->completed_at)
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('common.completed') }}</span>
                <p class="font-medium">{{ $request->completed_at->format('M d, Y h:i A') }}</p>
            </div>
            @endif
        </div>

        @if($request->rejection_reason)
        <div class="mt-6 p-4 bg-red-600/10 rounded-xl">
            <p class="font-medium text-red-600 dark:text-red-400">{{ __('common.reason') }}:</p>
            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $request->rejection_reason }}</p>
        </div>
        @endif

        @if($request->remarks)
        <div class="mt-4 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-xl">
            <p class="font-medium text-primary-700 dark:text-primary-400">{{ __('common.remarks') }}:</p>
            <p class="text-sm text-primary-700 dark:text-primary-400 mt-1">{{ $request->remarks }}</p>
        </div>
        @endif

        @if($request->qr_code)
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('registration.qr_code_label') }}</p>
            <div class="inline-block p-4 bg-white rounded-2xl">
                {!! QrCode::size(180)->generate(route('verification.show', ['token' => $request->verification_token])) !!}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
