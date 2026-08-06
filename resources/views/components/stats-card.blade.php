@props([
    'label' => '',
    'value' => '',
    'icon' => null,
    'color' => 'primary',
    'trend' => null,
    'trendUp' => true,
])

@php
    $colors = [
        'primary' => 'text-primary-700',
        'blue' => 'text-primary-700',
        'green' => 'text-green-600 dark:text-green-400',
        'yellow' => 'text-accent-700 dark:text-accent-400',
        'red' => 'text-red-600 dark:text-red-400',
        'purple' => 'text-primary-700',
    ];
    $bgColors = [
        'primary' => 'bg-primary-50 dark:bg-primary-900/30',
        'blue' => 'bg-primary-50 dark:bg-primary-900/30',
        'green' => 'bg-green-600/10',
        'yellow' => 'bg-accent-50 dark:bg-accent-900/20',
        'red' => 'bg-red-600/10',
        'purple' => 'bg-primary-50 dark:bg-primary-900/30',
    ];
    $iconColors = [
        'primary' => 'text-primary-700',
        'blue' => 'text-primary-700',
        'green' => 'text-green-600 dark:text-green-400',
        'yellow' => 'text-accent-700 dark:text-accent-400',
        'red' => 'text-red-600 dark:text-red-400',
        'purple' => 'text-primary-700',
    ];
@endphp

<div class="stats-card" {{ $attributes }}>
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <p class="stats-label">{{ $label }}</p>
            <p class="stats-value {{ $colors[$color] ?? $colors['primary'] }}">{{ $value }}</p>
            @if($trend)
                <p class="text-xs mt-1 {{ $trendUp ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    <svg class="w-3 h-3 inline mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trendUp ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
                    </svg>
                    {{ $trend }}
                </p>
            @endif
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-xl {{ $bgColors[$color] ?? $bgColors['primary'] }} flex items-center justify-center shrink-0 ml-3">
                <svg class="w-5 h-5 {{ $iconColors[$color] ?? $iconColors['primary'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
        @endif
    </div>
</div>
