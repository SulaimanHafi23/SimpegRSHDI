{{-- filepath: resources/views/components/ui/empty-state.blade.php --}}
@props([
    'icon' => 'fas fa-inbox',
    'title' => 'No data available',
    'description' => null,
    'action' => null,
])

<div class="text-center py-12">
    <i class="{{ $icon }} text-6xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-700 mb-2">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-gray-500 mb-4">{{ $description }}</p>
    @endif
    @if($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
