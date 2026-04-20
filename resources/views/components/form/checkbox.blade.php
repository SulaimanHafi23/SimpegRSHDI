@props([
    'label' => null,
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'help' => null,
    'error' => null,
])

<div {{ $attributes->only('class') }}>
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input
                type="checkbox"
                name="{{ $name }}"
                id="{{ $name }}"
                value="{{ $value }}"
                {{ old($name, $checked) ? 'checked' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                {{ $attributes->except(['class', 'label', 'name', 'value', 'checked', 'disabled', 'help', 'error'])->merge([
                    'class' => 'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2' .
                              ($disabled ? ' opacity-50 cursor-not-allowed' : ' cursor-pointer') .
                              ($error || $errors->has($name) ? ' border-red-500' : '')
                ]) }}
            >
        </div>
        @if($label)
            <div class="ml-3 text-sm">
                <label for="{{ $name }}" class="font-medium text-gray-700 {{ $disabled ? 'opacity-50' : 'cursor-pointer' }}">
                    {{ $label }}
                </label>
                @if($help)
                    <p class="text-gray-500">{{ $help }}</p>
                @endif
                @if($error)
                    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
                @elseif($errors->has($name))
                    <p class="mt-1 text-sm text-red-600">{{ $errors->first($name) }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
