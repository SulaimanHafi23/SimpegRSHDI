{{-- filepath: resources/views/components/ui/button.blade.php --}}
@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success, warning
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'loading' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-lg transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2';

$variants = [
    'primary' => 'bg-primary-600 hover:bg-primary-700 text-white shadow-md hover:shadow-lg focus:ring-primary-500',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-500',
    'outline' => 'border-2 border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-5 py-2.5 text-base',
    'lg' => 'px-6 py-3 text-lg',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @if($loading) disabled @endif
>
    @if($loading)
        <i class="fas fa-spinner fa-spin mr-2"></i>
    @elseif($icon)
        <i class="{{ $icon }} mr-2"></i>
    @endif

    {{ $slot }}
</button>
