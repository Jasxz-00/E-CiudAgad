@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'value' => '',
    'error' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'class' => '',
    'wrapperClass' => '',
    'autocomplete' => null,
])

<div class="{{ $wrapperClass }}">
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}
            @if($required)<span class="text-red-600 dark:text-red-400">*</span>@endif
        </label>
    @endif
    <input
        id="{{ $name }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'input-field ' . ($error ? 'input-error ' : '') . $class]) }}
    >
    @if($error)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
    {{ $slot }}
</div>
