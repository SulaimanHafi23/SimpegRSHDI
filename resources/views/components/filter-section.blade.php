@props([
    'action',
    'method' => 'GET',
])

<x-card title="Filter" class="mb-6">
    <form method="{{ $method }}" action="{{ $action }}">
        @if($method !== 'GET')
            @csrf
            @method($method)
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{ $slot }}
        </div>
        
        @if(isset($actions))
            <div class="flex gap-2 mt-4">
                {{ $actions }}
            </div>
        @else
            <div class="flex gap-2 mt-4">
                <x-button type="submit" variant="primary" icon="fas fa-search">
                    Filter
                </x-button>
                <x-button type="button" variant="outline-secondary" icon="fas fa-redo" onclick="window.location.href='{{ $action }}'">
                    Reset
                </x-button>
            </div>
        @endif
    </form>
</x-card>
