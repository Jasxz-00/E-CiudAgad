@extends('layouts.app')

@section('title', 'Personnel Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Personnel Dashboard</h1>

    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('personnel.registrations.create') }}" class="btn-primary text-sm">Register Resident</a>
        <a href="{{ route('personnel.resident-requests.create') }}" class="btn-outline text-sm">File Request for Resident</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <x-stats-card label="Total Requests" :value="$stats['total']" color="primary" icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        <x-stats-card label="Pending" :value="$stats['pending']" color="yellow" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stats-card label="Under Review" :value="$stats['reviewing']" color="blue" icon="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <x-stats-card label="Completed Today" :value="$stats['completed_today']" color="green" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    <div class="card p-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Queue Distribution</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            <div class="flex items-center gap-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                <span class="badge badge-pending">{{ __('common.pending') }}</span>
                <span class="font-bold text-yellow-800 dark:text-yellow-300">{{ $stats['pending'] ?? \App\Models\DocumentRequest::where('status', 'pending')->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                <span class="badge badge-reviewing">{{ __('common.reviewing') }}</span>
                <span class="font-bold text-blue-800 dark:text-blue-300">{{ $stats['reviewing'] ?? \App\Models\DocumentRequest::where('status', 'reviewing')->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                <span class="badge badge-on_hold">{{ __('common.processing') }}</span>
                <span class="font-bold text-purple-800 dark:text-purple-300">{{ \App\Models\DocumentRequest::where('status', 'on_hold')->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                <span class="badge badge-on_hold">{{ __('common.on_hold') }}</span>
                <span class="font-bold text-amber-800 dark:text-amber-300">{{ \App\Models\DocumentRequest::where('status', 'on_hold')->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                <span class="badge badge-approved">{{ __('common.ready_for_pickup') }}</span>
                <span class="font-bold text-green-800 dark:text-green-300">{{ $stats['approved'] ?? \App\Models\DocumentRequest::where('status', 'approved')->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                <span class="badge badge-completed">{{ __('common.completed_released') }}</span>
                <span class="font-bold text-emerald-800 dark:text-emerald-300">{{ \App\Models\DocumentRequest::whereIn('status', ['completed', 'released'])->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <span class="badge badge-rejected">{{ __('common.rejected') }}</span>
                <span class="font-bold text-red-800 dark:text-red-300">{{ \App\Models\DocumentRequest::where('status', 'rejected')->count() }}</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-gray-100 dark:bg-gray-800 rounded-xl">
                <span class="badge" style="background-color:#E5E7EB;color:#374151;">{{ __('common.cancelled') }}</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ \App\Models\DocumentRequest::where('status', 'cancelled')->count() }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Queue</h2>
            @if($queue->isEmpty())
                <p class="text-gray-600 dark:text-gray-400 text-sm py-4 text-center">No pending requests in queue.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-2 font-medium">#</th>
                                <th class="pb-2 font-medium">Resident</th>
                                <th class="pb-2 font-medium">Document</th>
                                <th class="pb-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queue as $q)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="py-2 font-mono">{{ $q->queue_position }}</td>
                                <td class="py-2">{{ $q->resident?->full_name ?? 'N/A' }}</td>
                                <td class="py-2">{{ $q->documentType?->name }}</td>
                                <td class="py-2"><x-badge :status="$q->status" /></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('personnel.requests') }}" class="text-sm text-primary-700 dark:text-primary-400 hover:underline mt-3 inline-block">View All Requests</a>
            @endif
        </x-card>

        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Requests</h2>
            @if($recentRequests->isEmpty())
                <p class="text-gray-600 dark:text-gray-400 text-sm py-4 text-center">No requests yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($recentRequests as $req)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-950 rounded-xl">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $req->resident?->full_name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $req->documentType?->name }} &middot; {{ $req->queue_number }}</p>
                        </div>
                        <x-badge :status="$req->status" />
                    </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection