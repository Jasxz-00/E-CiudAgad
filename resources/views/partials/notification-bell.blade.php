@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $recentNotifications = auth()->user()->notifications()->take(6)->get();
@endphp
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" @@click="open = !open" class="btn-ghost p-2 relative" aria-label="Notifications">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-600 text-white text-xs font-bold rounded-full flex items-center justify-center">{{ $unreadCount }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ __('common.notifications') }}</h3>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-primary-700 dark:text-primary-400 hover:underline font-medium">Mark all read</button>
                </form>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse($recentNotifications as $notification)
                @php $data = $notification->data; @endphp
                <a href="{{ route('notifications.read', $notification->id) }}" @click.prevent="
                    fetch('{{ route('notifications.read', $notification->id) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } }).then(() => window.location.href = '{{ $data['url'] ?? route('notifications.index') }}');
                " class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-800 transition-colors {{ $notification->read_at ? 'opacity-70' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $notification->read_at ? 'bg-gray-300 dark:bg-gray-600' : 'bg-primary-600' }}"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $data['title'] ?? '' }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 break-words">{{ $data['message'] ?? '' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-8 px-4">{{ __('common.no_data') }}</p>
            @endforelse
        </div>

        <div class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <a href="{{ route('notifications.index') }}" wire:navigate class="text-xs text-primary-700 dark:text-primary-400 hover:underline font-medium">View all notifications</a>
        </div>
    </div>
</div>