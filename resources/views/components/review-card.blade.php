@props([
    'title' => '',
    'icon' => null,
    'editUrl' => null,
    'class' => '',
])

<div class="p-5 bg-gray-50 dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-700 mb-4 {{ $class }}">
    <div class="flex items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-3">
            @if($icon)
                <span class="w-8 h-8 bg-primary-50 dark:bg-primary-900/30 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                </span>
            @endif
            <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ $title }}</h4>
        </div>
        @if($editUrl)
            <a href="{{ $editUrl }}" class="text-xs text-primary-700 dark:text-primary-400 hover:underline font-medium">Edit</a>
        @endif
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
        {{ $slot }}
    </div>
</div>
