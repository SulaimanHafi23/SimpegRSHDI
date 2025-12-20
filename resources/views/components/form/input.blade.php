@props([
    'label' => null,
    'name',
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'error' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
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
        {{ $attributes->except(['class', 'label', 'name', 'type', 'placeholder', 'value', 'required', 'disabled', 'readonly', 'help', 'error'])->merge([
            'class' => 'w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors ' . 
                      ($error || $errors->has($name) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500') .
                      ($disabled ? ' bg-gray-100 cursor-not-allowed' : '') .
                      ($readonly ? ' bg-gray-50' : '')
        ]) }}
    >
    
    @if($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="mt-1 text-sm text-red-600">{{ $errors->first($name) }}</p>
    @endif
</div>
