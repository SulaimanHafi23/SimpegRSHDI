{{-- filepath: resources/views/components/forms/datepicker.blade.php --}}
@props([
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => 'Select date',
    'required' => false,
    'minDate' => null,
    'maxDate' => null,
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
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-calendar-alt text-gray-400"></i>
        </div>
        <input
            type="date"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            @if($minDate) min="{{ $minDate }}" @endif
            @if($maxDate) max="{{ $maxDate }}" @endif
            {{ $attributes->merge(['class' => 'input-field pl-10' . ($errors->has($name) ? ' border-red-500' : '')]) }}
        >
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
        </p>
    @enderror
</div>
