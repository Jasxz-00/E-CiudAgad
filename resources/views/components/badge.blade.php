@props([
    'status' => 'pending',
    'pulse' => false,
])

<span class="badge badge-{{ $status }} {{ $pulse ? 'animate-pulse-soft' : '' }}" {{ $attributes }}>
    {{ $slot ?? ucfirst($status) }}
</span>
