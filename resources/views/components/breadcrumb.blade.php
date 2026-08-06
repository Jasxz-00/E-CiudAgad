@props(['items' => []])

<nav class="breadcrumb mb-4" aria-label="Breadcrumb">
    <a href="{{ url('/') }}" aria-label="Home" class="hover:text-primary-800 dark:hover:text-primary-400">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </a>
    @foreach($items as $item)
        <span class="breadcrumb-sep" aria-hidden="true">/</span>
        @if($item['url'] ?? false)
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
