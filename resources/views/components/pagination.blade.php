@props(['paginator'])

@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-700">
            Menampilkan
            <span class="font-medium">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai
            <span class="font-medium">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="font-medium">{{ $paginator->total() }}</span>
            data
        </div>

        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </nav>
    </div>
@endif
