@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'value' => '',
    'error' => null,
    'required' => false,
    'class' => '',
])

<div class="{{ $class }}">
    @if($label)
        <label class="label">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <div class="radio-group">
        @foreach($options as $key => $optionLabel)
            @php
                $isSelected = $value == $key;
            @endphp
            <label class="radio-option {{ $isSelected ? 'selected' : '' }}">
                <input type="radio" name="{{ $name }}" value="{{ $key }}"
                    @if($isSelected) checked @endif
                    @if($required) required @endif
                    class="radio-input"
                    {{ $attributes->except(['name', 'label', 'options', 'value', 'error', 'required', 'class']) }}>
                <span class="radio-label">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
    @if($error)
        <p class="mt-1 text-sm text-danger">{{ $error }}</p>
    @endif
</div>
