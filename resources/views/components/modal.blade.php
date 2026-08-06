@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md',
    'submitLabel' => null,
    'cancelLabel' => 'Cancel',
])

@php
    $maxWidths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl', '3xl' => 'max-w-3xl', '4xl' => 'max-w-4xl', '5xl' => 'max-w-5xl'];
    $maxWidth = $maxWidths[$size] ?? $maxWidths['md'];
@endphp

<div x-data="{ open: false }" x-id="['modal-{{ $id }}']" {{ $attributes->whereStartsWith('wire:key') }}>
    {{ $trigger ?? '' }}

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-overlay"
         @@keydown.escape.window="open = false"
         @@click.self="open = false"
         role="dialog"
         :aria-labelledby="$id('modal-{{ $id }}', 'title')"
         aria-modal="true">

        <div class="modal-content {{ $maxWidth }}"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @@click.away="open = false">

            @if($title)
                <div class="modal-header">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100" :id="$id('modal-{{ $id }}', 'title')">{{ $title }}</h2>
                    <button type="button" @@click="open = false" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if($submitLabel || $cancelLabel)
                <div class="modal-footer">
                    <button type="button" @@click="open = false" class="btn-ghost">{{ $cancelLabel }}</button>
                    @if($submitLabel)
                        <button type="button" @@click="open = false" class="btn-primary">{{ $submitLabel }}</button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
