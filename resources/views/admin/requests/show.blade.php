@extends('layouts.app')

@section('title', 'Request ' . $request->queue_number)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.requests.index') }}" class="btn-ghost mb-4">&larr; Back to Requests</a>

    <div class="card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 break-words">{{ $request->queue_number }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->documentType?->name }}</p>
            </div>
            <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6 sm:gap-4 text-sm">
            <div><span class="text-gray-600 dark:text-gray-400">Resident</span><p class="font-medium">{{ $request->resident?->full_name }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Category</span><p class="font-medium capitalize">{{ $request->resident?->category }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Purpose</span><p class="font-medium">{{ $request->purpose?->name ?? $request->purpose_other }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Queue Position</span><p class="font-medium">{{ $request->queue_position ?? 'Waiting' }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Total Weight</span><p class="font-medium">{{ number_format((float) $request->total_weight, 2) }}</p></div>
            <div><span class="text-gray-600 dark:text-gray-400">Submitted</span><p class="font-medium">{{ $request->created_at->format('M d, Y h:i A') }}</p></div>
            @if($request->processing_started_at)
            <div><span class="text-gray-600 dark:text-gray-400">Processing Started</span><p class="font-medium">{{ $request->processing_started_at->format('M d, Y h:i A') }}</p></div>
            @endif
            @if($request->completed_at)
            <div><span class="text-gray-600 dark:text-gray-400">Completed</span><p class="font-medium">{{ $request->completed_at->format('M d, Y h:i A') }}</p></div>
            @endif
            @if($request->processedBy)
            <div><span class="text-gray-600 dark:text-gray-400">Processed By</span><p class="font-medium">{{ $request->processedBy->username }}</p></div>
            @endif
        </div>

        @if($request->rejection_reason)
        <div class="mt-6 p-4 bg-red-600/10 rounded-xl">
            <p class="font-medium text-red-600 dark:text-red-400">Rejection Reason:</p>
            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $request->rejection_reason }}</p>
        </div>
        @endif

        @if($request->remarks)
        <div class="mt-4 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-xl">
            <p class="font-medium text-primary-700 dark:text-primary-400">Remarks:</p>
            <p class="text-sm text-primary-700 dark:text-primary-400 mt-1">{{ $request->remarks }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
