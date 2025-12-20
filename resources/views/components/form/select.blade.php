@props([
    'label' => null,
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Pilih salah satu',
    'required' => false,
    'disabled' => false,
    'help' => null,
    'error' => null,
    'multiple' => false,
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
    
    <select 
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->except(['class', 'label', 'name', 'options', 'selected', 'placeholder', 'required', 'disabled', 'help', 'error', 'multiple'])->merge([
            'class' => 'w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors ' . 
                      ($error || $errors->has($name) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500') .
                      ($disabled ? ' bg-gray-100 cursor-not-allowed' : '')
        ]) }}
    >
        @if($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(isset($slot) && $slot->isNotEmpty())
            {{ $slot }}
        @else
            @foreach($options as $value => $label)
                <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        @endif
    </select>
    
    @if($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="mt-1 text-sm text-red-600">{{ $errors->first($name) }}</p>
    @endif
</div>
