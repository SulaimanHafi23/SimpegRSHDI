@props([
    'title' => null,
    'noPadding' => false,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md overflow-hidden']) }}>
    @if($title || isset($header))
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
            @if(isset($header))
                {{ $header }}
            @else
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            @endif
        </div>
    @endif
    
    <div class="{{ $noPadding ? '' : 'p-4 sm:p-6' }}">
        {{ $slot }}
    </div>
    
    @if($footer || isset($cardFooter))
        <div class="px-4 sm:px-6 py-3 bg-gray-50 border-t border-gray-200">
            {{ $footer ?? $cardFooter }}
        </div>
    @endif
</div>
