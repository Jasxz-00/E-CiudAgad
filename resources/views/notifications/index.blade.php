@extends('layouts.app')

@section('title', __('common.notifications'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('common.notifications') }}</h1>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn-outline text-sm">Mark all as read</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/10 text-green-600 dark:text-green-400 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="card p-0 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="p-8 text-center text-sm text-gray-600 dark:text-gray-400">{{ __('common.no_data') }}</div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($notifications as $notification)
                    @php $data = $notification->data; @endphp
                    <a href="{{ route('notifications.read', $notification->id) }}" wire:navigate
                       class="flex items-start gap-3 px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $notification->read_at ? 'opacity-70' : '' }}">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $notification->read_at ? 'bg-gray-300 dark:bg-gray-600' : 'bg-primary-600' }}"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $data['title'] ?? '' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5 break-words">{{ $data['message'] ?? '' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
@endsection