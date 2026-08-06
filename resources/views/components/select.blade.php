@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'value' => '',
    'error' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'class' => '',
    'autocomplete' => null,
])

<div>
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}
            @if($required)<span class="text-red-600 dark:text-red-400">*</span>@endif
        </label>
    @endif
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => 'select-field ' . ($error ? 'input-error ' : '') . $class]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $key => $option)
            <option value="{{ $key }}" {{ old($name, $value) == $key ? 'selected' : '' }}>{{ $option }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if($error)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
</div>
