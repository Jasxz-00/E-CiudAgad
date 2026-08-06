@props([
    'name' => '',
    'label' => '',
    'checked' => false,
    'value' => '1',
    'error' => null,
    'required' => false,
])

<label class="flex items-start gap-3 cursor-pointer">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}"
           class="mt-1 w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700 focus:ring-primary-500"
           @if($checked) checked @endif
           @if($required) required @endif
           {{ $attributes }}>
    <span class="text-sm text-gray-900 dark:text-gray-100">
        {{ $label }}
        @if($required)<span class="text-red-600 dark:text-red-400">*</span>@endif
    </span>
</label>
@if($error)
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
@endif
