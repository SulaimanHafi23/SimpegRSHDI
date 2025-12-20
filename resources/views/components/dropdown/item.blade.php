@props([
    'icon' => null,
    'href' => null,
])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150']) }}>
        @if($icon)
            <i class="{{ $icon }} mr-2 text-gray-500"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150']) }}>
        @if($icon)
            <i class="{{ $icon }} mr-2 text-gray-500"></i>
        @endif
        {{ $slot }}
    </button>
@endif
