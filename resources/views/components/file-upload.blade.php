@props([
    'name' => 'file',
    'label' => 'Upload File',
    'accept' => '.jpg,.jpeg,.png,.pdf',
    'maxSize' => 5,
    'error' => null,
    'required' => false,
    'preview' => true,
])

<div class="file-upload-wrapper" x-data="{ fileName: '', previewUrl: '' }">
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}
            @if($required)<span class="text-red-600 dark:text-red-400">*</span>@endif
        </label>
    @endif

    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center hover:border-primary-500 transition-colors cursor-pointer"
         @@click="$refs.fileInput.click()"
         @@dragover.prevent="$el.classList.add('border-primary-500', 'bg-primary-50 dark:bg-primary-900/30')"
         @@dragleave.prevent="$el.classList.remove('border-primary-500', 'bg-primary-50 dark:bg-primary-900/30')"
         @@drop.prevent="
            $el.classList.remove('border-primary-500', 'bg-primary-50 dark:bg-primary-900/30');
            const files = $event.dataTransfer.files;
            if (files.length) { $refs.fileInput.files = files; $refs.fileInput.dispatchEvent(new Event('change')); }
         ">

        <template x-if="!previewUrl">
            <div>
                <svg class="w-10 h-10 mx-auto text-gray-600 dark:text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-sm text-gray-600 dark:text-gray-400">Click to upload or drag and drop</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">JPG, JPEG, PNG, or PDF (max {{ $maxSize }} MB)</p>
            </div>
        </template>

        <template x-if="previewUrl">
            <div class="relative inline-block">
                <img :src="previewUrl" class="max-h-32 rounded-lg mx-auto" alt="Preview">
                <button type="button" @@click="previewUrl = ''; fileName = ''; $refs.fileInput.value = ''"
                        class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-700">
                    &times;
                </button>
            </div>
        </template>
    </div>

    <input id="{{ $name }}" type="file" name="{{ $name }}" accept="{{ $accept }}" autocomplete="off"
           x-ref="fileInput"
           class="file-upload-input hidden"
           @if($required) required @endif
           @@change="
                const file = $event.target.files[0];
                if (!file) return;
                if (file.size > {{ $maxSize }} * 1024 * 1024) { alert('File size must not exceed {{ $maxSize }} MB.'); $refs.fileInput.value = ''; return; }
                fileName = file.name;
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => previewUrl = e.target.result;
                    reader.readAsDataURL(file);
                } else { previewUrl = ''; }
           ">

    <p class="file-name text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="fileName"></p>
    @if($error)
        <p class="file-error text-sm text-red-600 dark:text-red-400 mt-1">{{ $error }}</p>
    @endif
</div>
