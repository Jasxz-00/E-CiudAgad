@props([
    'name' => '',
    'label' => '',
    'checked' => false,
    'value' => '1',
    'disabled' => false,
])

<label class="relative inline-flex items-center gap-3 cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}"
           class="sr-only peer"
           @if($checked) checked @endif
           @if($disabled) disabled @endif
           {{ $attributes }}>
    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500/30 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-200 dark:border-gray-700 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
    @if($label)
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
    @endif
</label>
