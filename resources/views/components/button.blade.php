@props([
    'type' => 'primary',
    'size' => null,
    'href' => null,
    'disabled' => false,
    'icon' => null,
    'class' => '',
])

@php
    $base = "inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 ease min-h-[44px] min-w-[44px]";
    if ($disabled) $base .= " opacity-50 cursor-not-allowed";
    $sizes = ['sm' => 'px-4 py-2 text-sm min-h-[36px] min-w-[36px]', 'lg' => 'px-8 py-4 text-lg min-h-[52px]'];
    $base .= $size && isset($sizes[$size]) ? " {$sizes[$size]}" : " px-6 py-3";
    $variants = [
        'primary' => 'btn-primary',
        'accent' => 'btn-accent',
        'outline' => 'btn-outline',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
    ];
    $base .= " " . ($variants[$type] ?? $variants['primary']);
    $base .= " " . $class;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
        @if($icon)<svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $base, 'disabled' => $disabled]) }}>
        @if($icon)<svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>@endif
        {{ $slot }}
    </button>
@endif
