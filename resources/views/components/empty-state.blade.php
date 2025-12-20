@props([
    'icon' => 'fas fa-inbox',
    'title' => 'Tidak ada data',
    'description' => 'Belum ada data yang tersedia saat ini.',
    'actionText' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
        <i class="{{ $icon }} text-3xl text-gray-400"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $title }}</h3>
    <p class="text-gray-500 mb-6">{{ $description }}</p>
    
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
            {{ $actionText }}
        </a>
    @endif
    
    @if(isset($action))
        {{ $action }}
    @endif
</div>
