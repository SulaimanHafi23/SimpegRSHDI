{{-- filepath: resources/views/components/forms/input.blade.php --}}
@props([
    'type' => 'text',
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'icon' => null,
    'helper' => null,
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="{{ $icon }} text-gray-400"></i>
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge(['class' => 'input-field' . ($icon ? ' pl-10' : '') . ($errors->has($name) ? ' border-red-500 focus:ring-red-500' : '')]) }}
        >
    </div>

    @if($helper)
        <p class="mt-1 text-xs text-gray-500">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
        </p>
    @enderror
</div>
