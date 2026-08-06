@props([
    'title' => '',
    'description' => '',
    'actionUrl' => null,
    'actionLabel' => null,
    'actionIcon' => null,
    'backUrl' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        @if($backUrl)
            <a href="{{ $backUrl }}" class="btn-ghost btn-sm mb-2">&larr; Back</a>
        @endif
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $title }}</h1>
        @if($description)
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $description }}</p>
        @endif
    </div>
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn-primary whitespace-nowrap">
            @if($actionIcon)
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $actionIcon }}"/></svg>
            @endif
            {{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>
