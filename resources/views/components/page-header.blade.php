@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-0">
    <div>
        <div class="flex items-center gap-3">
            @if($icon)
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="{{ $icon }} text-blue-600 text-xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">{{ $title }}</h1>
                @if($description)
                    <p class="text-sm sm:text-base text-gray-600 mt-1">{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>
    
    @if(isset($actions))
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            {{ $actions }}
        </div>
    @endif
</div>
