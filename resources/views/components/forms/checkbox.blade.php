{{-- filepath: resources/views/components/forms/checkbox.blade.php --}}
@props([
    'name',
    'label',
    'checked' => false,
    'value' => '1',
])

<div class="flex items-center mb-4">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        {{ old($name, $checked) ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2']) }}
    >
    <label for="{{ $name }}" class="ml-2 text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
</div>
