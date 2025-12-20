@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null,
])

@php
    $typeClasses = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'danger' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];
    
    $iconClasses = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'danger' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
    ];
@endphp

<div {{ $attributes->merge(['class' => $typeClasses[$type] . ' border px-4 py-3 rounded relative mb-4']) }} role="alert" x-data="{ show: true }" x-show="show" x-transition>
    <div class="flex items-start">
        @if($icon || isset($iconClasses[$type]))
            <i class="{{ $icon ?? $iconClasses[$type] }} mr-3 mt-0.5"></i>
        @endif
        <div class="flex-1">
            {{ $slot }}
        </div>
        @if($dismissible)
            <button @click="show = false" class="ml-4">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
</div>
