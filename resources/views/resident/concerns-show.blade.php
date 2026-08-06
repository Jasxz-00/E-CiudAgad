@extends('layouts.app')

@section('title', $concern->subject)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('resident.concerns') }}" class="btn-ghost mb-4">&larr; Back to Concerns</a>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/15 text-green-600 dark:text-green-400 border border-success/30 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div class="min-w-0 flex-1 break-words">
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $concern->subject }}</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $concern->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <x-badge :status="$concern->status" />
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
            <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $concern->message }}</p>
        </div>

        @if($concern->admin_notes)
            <div class="mt-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
                <p class="text-xs text-accent-700 dark:text-accent-300 font-medium mb-1">Staff Response</p>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $concern->admin_notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
