@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false,
])

@php
    $variantClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
        'info' => 'bg-indigo-600 hover:bg-indigo-700 text-white',
        'purple' => 'bg-purple-600 hover:bg-purple-700 text-white',
        'outline' => 'border-2 border-gray-300 text-gray-700 hover:bg-gray-50',
        'outline-primary' => 'border-2 border-blue-600 text-blue-600 hover:bg-blue-50',
        'outline-secondary' => 'border-2 border-gray-600 text-gray-600 hover:bg-gray-50',
        'outline-danger' => 'border-2 border-red-600 text-red-600 hover:bg-red-50',
    ];
    
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
        'xl' => 'px-8 py-4 text-xl',
    ];
    
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg shadow-md transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed';
@endphp

<button 
    {{ $attributes->merge([
        'type' => 'button',
        'class' => $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size],
        'disabled' => $disabled || $loading
    ]) }}
>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon && $iconPosition === 'left')
        <i class="{{ $icon }} mr-2"></i>
    @endif
    
    {{ $slot }}
    
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ml-2"></i>
    @endif
</button>
