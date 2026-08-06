@extends('layouts.app')

@section('title', __('common.dashboard'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('credentials'))
        <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-accent-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-accent-700 dark:text-accent-300 dark:text-accent-300">Account Created Successfully!</h3>
                    <p class="text-sm text-accent-700 dark:text-accent-300 dark:text-accent-400 mb-3">Please save your credentials below. They will not be shown again.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm font-mono bg-gray-50 dark:bg-gray-950 rounded-lg p-3">
                        <div class="sm:col-span-2"><span class="text-gray-600 dark:text-gray-400">Tracking Number:</span> <strong class="text-gray-900 dark:text-gray-100">{{ session('credentials.tracking_number') }}</strong></div>
                        <div class="sm:col-span-2"><span class="text-gray-600 dark:text-gray-400">PIN:</span> <strong class="text-gray-900 dark:text-gray-100">{{ session('credentials.pin') }}</strong></div>
                        <div><span class="text-gray-600 dark:text-gray-400">Email:</span> <span class="text-gray-600 dark:text-gray-400">{{ session('credentials.email') }}</span></div>
                        <div><span class="text-gray-600 dark:text-gray-400">Queue #:</span> <strong class="text-primary-700 dark:text-primary-400">{{ session('credentials.queue_number') }}</strong></div>
                    </div>
                </div>
                <button onclick="this.closest('.rounded-xl').remove()" class="text-accent-700 dark:text-accent-300 dark:text-accent-300 hover:text-accent-900" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    @endif

    <div class="mb-4">
        <button type="button" onclick="history.back()" class="btn-ghost inline-flex items-center gap-2 text-sm" aria-label="Go back">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('common.back') }}
        </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('common.welcome') }}, {{ $resident->first_name }}!</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('common.app_desc') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('resident.profile') }}" wire:navigate class="btn-ghost whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ __('common.profile') }}
            </a>
            <a href="{{ route('resident.new-request') }}" class="btn-primary whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('common.new_request') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <x-stats-card label="{{ __('common.pending') }}" :value="$pendingCount" color="primary" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stats-card label="{{ __('common.completed') }}" :value="$completedCount" color="green" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stats-card label="{{ __('common.category') }}" :value="ucfirst($resident->category)" color="yellow" icon="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.announcements') }}</h2>
            </div>

            @if($announcements->isEmpty())
                <x-empty-state
                    title="{{ __('common.no_announcements') }}"
                    description="{{ __('common.contact_barangay') }}" />
            @else
                <div class="space-y-4">
                    @foreach($announcements as $announcement)
                        <div class="p-3 bg-gray-50 dark:bg-gray-950 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $announcement->title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-xs mt-1">{{ Str::limit($announcement->content, 150) }}</p>
                            <p class="text-gray-600 dark:text-gray-400 text-xs mt-2">{{ $announcement->published_at?->format('M d, Y') ?? $announcement->created_at->format('M d, Y') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.notifications') }}</h2>
                <a href="{{ route('resident.requests') }}" class="text-sm text-primary-700 dark:text-primary-400 hover:underline">{{ __('common.view') }}</a>
            </div>

            @if($notifications->isEmpty())
                <x-empty-state
                    title="{{ __('common.no_data') }}"
                    description="{{ __('common.new_request') }}" />
            @else
                <div class="space-y-3">
                    @foreach($notifications as $notif)
                        <div class="flex items-start justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $notif->documentType?->name }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            <x-badge :status="$notif->status" class="shrink-0 ml-2" />
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.requests') }}</h2>
            <a href="{{ route('resident.requests') }}" class="text-sm text-primary-700 dark:text-primary-400 hover:underline">{{ __('common.view') }}</a>
        </div>

        @if($recentRequests->isEmpty())
            <x-empty-state
                title="{{ __('common.no_data') }}"
                description="{{ __('common.new_request') }}"
                actionUrl="{{ route('resident.new-request') }}"
                actionLabel="{{ __('common.new_request') }}" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 font-medium">{{ __('common.queue_number') }}</th>
                            <th class="pb-3 font-medium">{{ __('common.document') }}</th>
                            <th class="pb-3 font-medium">{{ __('common.status') }}</th>
                            <th class="pb-3 font-medium">{{ __('common.queue_position') }}</th>
                            <th class="pb-3 font-medium">{{ __('common.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRequests as $req)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-3 font-mono">{{ $req->queue_number }}</td>
                            <td class="py-3">{{ $req->documentType?->name }}</td>
                            <td class="py-3"><x-badge :status="$req->status" /></td>
                            <td class="py-3">{{ $req->queue_position ?? '-' }}</td>
                            <td class="py-3 text-gray-600 dark:text-gray-400">{{ $req->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <div class="mt-6 text-center">
        <a href="{{ route('resident.concerns') }}" class="btn-accent inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ __('common.submit_concern') }}
        </a>
    </div>
</div>
@endsection
