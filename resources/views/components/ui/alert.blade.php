{{-- filepath: resources/views/components/ui/alert.blade.php --}}
@props([
    'type' => 'info', // success, error, warning, info
    'message',
    'dismissible' => true,
])

@php
$styles = [
    'success' => 'bg-green-50 border-green-500 text-green-800',
    'error' => 'bg-red-50 border-red-500 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-500 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-500 text-blue-800',
];

$icons = [
    'success' => 'fas fa-check-circle',
    'error' => 'fas fa-exclamation-circle',
    'warning' => 'fas fa-exclamation-triangle',
    'info' => 'fas fa-info-circle',
];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    {{ $attributes->merge(['class' => 'rounded-lg border-l-4 p-4 mb-4 ' . $styles[$type]]) }}
>
    <div class="flex items-start">
        <i class="{{ $icons[$type] }} text-xl mr-3 mt-0.5"></i>
        <div class="flex-1">
            <p class="font-medium">{{ $message }}</p>
        </div>
        @if($dismissible)
            <button @click="show = false" class="ml-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
</div>
