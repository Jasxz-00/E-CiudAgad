@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <x-section-header title="Announcements" actionUrl="{{ route('admin.announcements.create') }}" actionLabel="New Announcement"
        actionIcon="M12 4v16m8-8H4" />

    @if(session('success'))
        <x-alert type="success" :message="session('success')" dismissible />
    @endif

    <x-card>
        @forelse($announcements as $a)
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $a->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 break-words">{{ Str::limit($a->content, 150) }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">By {{ $a->createdBy?->username ?? 'System' }} &middot; {{ $a->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @if($a->is_published)
                            <span class="badge bg-green-600/10 text-green-600 dark:text-green-400">Published</span>
                        @else
                            <span class="badge bg-gray-50 dark:bg-gray-950 text-gray-600 dark:text-gray-400">Draft</span>
                        @endif
                        <a href="{{ route('admin.announcements.edit', $a) }}" class="text-primary-700 dark:text-primary-400 hover:underline text-sm inline-flex items-center min-h-[36px]">Edit</a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center py-8 text-gray-600 dark:text-gray-400">No announcements yet.</p>
        @endforelse
        @if($announcements->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $announcements->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
