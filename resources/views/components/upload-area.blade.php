@props([
    'name' => '',
    'label' => '',
    'accept' => '.jpg,.jpeg,.png',
    'maxSize' => 2,
    'error' => null,
    'required' => false,
    'preview' => null,
    'hint' => '',
    'class' => '',
])

<div class="{{ $class }}">
    @if($label)
        <label class="label">
            {{ $label }}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    @if($hint)
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $hint }}</p>
    @endif
    <div class="upload-area" @@click="$refs.fileInput.click()">
        @if($preview)
            <img :src="{{ $preview }}" alt="Preview" class="max-h-48 rounded-lg mb-2">
        @else
            <svg class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="upload-text">Click to upload or drag and drop</p>
            <p class="upload-hint">JPG, JPEG, PNG — Max {{ $maxSize }} MB</p>
        @endif
    </div>
    <input id="{{ $name }}" type="file" name="{{ $name }}" accept="{{ $accept }}"
           x-ref="fileInput"
           class="hidden"
           @if($required) required @endif>
    @if($error)
        <p class="mt-1 text-sm text-danger">{{ $error }}</p>
    @endif
</div>
