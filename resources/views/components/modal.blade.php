@props([
    'name',
    'title' => '',
    'size' => 'md',
    'show' => false,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-2xl',
        'lg' => 'max-w-4xl',
        'xl' => 'max-w-6xl',
        'full' => 'max-w-full mx-4',
    ];
@endphp

<div 
    x-data="{ show: {{ $show ? 'true' : 'false' }} }"
    x-on:open-modal-{{ $name }}.window="show = true"
    x-on:close-modal-{{ $name }}.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 backdrop-blur-sm bg-white/30 transition-opacity"
        @click="show = false"
    ></div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            {{ $attributes->merge(['class' => 'relative bg-white rounded-lg shadow-xl w-full ' . $sizeClasses[$size]]) }}
        >
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                @if(isset($header))
                    {{ $header }}
                @else
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @endif
                <button 
                    @click="show = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if(isset($footer))
                <div class="flex items-center justify-end gap-2 p-4 border-t border-gray-200 bg-gray-50">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
