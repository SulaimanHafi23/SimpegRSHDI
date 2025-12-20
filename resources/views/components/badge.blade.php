@props([
    'variant' => 'default',
    'size' => 'md',
    'icon' => null,
])

@php
    $variantClasses = [
        'default' => 'bg-gray-100 text-gray-800',
        'primary' => 'bg-blue-100 text-blue-800',
        'success' => 'bg-green-100 text-green-800',
        'danger' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-indigo-100 text-indigo-800',
        'secondary' => 'bg-purple-100 text-purple-800',
        'gray' => 'bg-gray-200 text-gray-700',
        'dark' => 'bg-gray-800 text-white',
    ];
    
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
        'lg' => 'px-3 py-1.5 text-base',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-medium rounded-full ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size]]) }}>
    @if($icon)
        <i class="{{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</span>
