@props([
    'title' => '',
    'description' => '',
    'number' => null,
    'class' => '',
])

<div class="mb-6 {{ $class }}">
    @if($number)
        <div class="flex items-center gap-3 mb-3">
            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-700 text-white text-sm font-bold shrink-0">{{ $number }}</span>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
        </div>
    @else
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">{{ $title }}</h3>
    @endif
    @if($description)
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
