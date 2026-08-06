@props([
    'hover' => false,
    'padding' => true,
    'class' => '',
])

@php
    $classes = $hover ? 'card-hover' : 'card';
    if (!$padding) $classes .= ' !p-0';
    $classes .= " " . $class;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
