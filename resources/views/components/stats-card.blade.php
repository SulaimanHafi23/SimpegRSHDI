@props([
    'title',
    'value',
    'icon',
    'color' => 'blue',
    'trend' => null,
    'trendUp' => true,
])

@php
    $colorClasses = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'yellow' => 'bg-yellow-100 text-yellow-600',
        'red' => 'bg-red-100 text-red-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'indigo' => 'bg-indigo-100 text-indigo-600',
        'pink' => 'bg-pink-100 text-pink-600',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
    
    $valueColorClasses = [
        'blue' => 'text-blue-600',
        'green' => 'text-green-600',
        'yellow' => 'text-yellow-600',
        'red' => 'text-red-600',
        'purple' => 'text-purple-600',
        'indigo' => 'text-indigo-600',
        'pink' => 'text-pink-600',
        'gray' => 'text-gray-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow p-4 hover:shadow-lg transition-shadow duration-200']) }}>
    <div class="flex items-center justify-between">
        <div class="flex items-center flex-1">
            <div class="p-3 {{ $colorClasses[$color] ?? $colorClasses['blue'] }} rounded-full">
                <i class="{{ $icon }} text-xl"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm text-gray-600">{{ $title }}</p>
                <p class="text-2xl font-bold {{ $valueColorClasses[$color] ?? 'text-gray-900' }}">{{ $value }}</p>
                
                @if($trend)
                    <div class="flex items-center mt-1">
                        <i class="fas fa-arrow-{{ $trendUp ? 'up' : 'down' }} text-xs {{ $trendUp ? 'text-green-500' : 'text-red-500' }} mr-1"></i>
                        <span class="text-xs {{ $trendUp ? 'text-green-500' : 'text-red-500' }}">{{ $trend }}</span>
                    </div>
                @endif
            </div>
        </div>
        
        @if(isset($action))
            <div class="ml-2">
                {{ $action }}
            </div>
        @endif
    </div>
</div>
