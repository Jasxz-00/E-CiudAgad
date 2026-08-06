@props([
    'value' => 0,
    'color' => 'primary',
    'animated' => true,
    'label' => null,
    'showValue' => true,
])

@php
    $colors = [
        'primary' => 'bg-primary-600',
        'accent' => 'bg-accent-500',
        'green' => 'bg-green-600 hover:bg-green-700',
        'red' => 'bg-red-600 hover:bg-red-700',
        'blue' => 'bg-primary-50 dark:bg-primary-900/300',
    ];
    $barColor = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes }}>
    @if($label || $showValue)
        <div class="flex items-center justify-between mb-1">
            @if($label)
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
            @endif
            @if($showValue)
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $value }}%</span>
            @endif
        </div>
    @endif
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500 ease-out {{ $barColor }} {{ $animated ? 'animate-shimmer' : '' }}"
             style="width: {{ min(100, max(0, $value)) }}%">
        </div>
    </div>
</div>
