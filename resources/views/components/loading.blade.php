@props([
    'size' => 'md',
    'color' => 'blue',
    'text' => null,
])

@php
    $sizeClasses = [
        'sm' => 'h-4 w-4',
        'md' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        'xl' => 'h-16 w-16',
    ];
    
    $colorClasses = [
        'blue' => 'text-blue-600',
        'green' => 'text-green-600',
        'red' => 'text-red-600',
        'yellow' => 'text-yellow-600',
        'gray' => 'text-gray-600',
        'white' => 'text-white',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center']) }}>
    <svg class="animate-spin {{ $sizeClasses[$size] }} {{ $colorClasses[$color] }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    @if($text)
        <p class="mt-2 text-sm text-gray-600">{{ $text }}</p>
    @endif
</div>
