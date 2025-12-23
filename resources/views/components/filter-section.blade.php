@props([
    'action',
    'method' => 'GET',
])

<div x-data="{ showFilters: false }" class="mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        {{-- Filter Header --}}
        <button @click="showFilters = !showFilters" 
                class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <i class="fas fa-filter text-indigo-600"></i>
                <span class="font-semibold text-gray-900">Filter & Pencarian</span>
            </div>
            <i class="fas fa-chevron-down transform transition-transform" 
               :class="{ 'rotate-180': showFilters }"></i>
        </button>

        {{-- Filter Form --}}
        <div x-show="showFilters" 
             x-collapse 
             class="border-t border-gray-200">
            <form method="{{ $method }}" action="{{ $action }}" class="p-6">
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
        </div>
    </div>
</div>
