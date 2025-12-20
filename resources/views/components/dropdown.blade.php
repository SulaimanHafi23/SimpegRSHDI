@props([
    'align' => 'right',
    'width' => '48',
])

@php
    $alignClasses = [
        'left' => 'origin-top-left left-0',
        'right' => 'origin-top-right right-0',
    ];
    
    $widthClasses = [
        '48' => 'w-48',
        '60' => 'w-60',
        '72' => 'w-72',
        'full' => 'w-full',
    ];
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Trigger -->
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClasses[$width] }} rounded-md shadow-lg {{ $alignClasses[$align] }}"
        style="display: none;"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
            {{ $slot }}
        </div>
    </div>
</div>
