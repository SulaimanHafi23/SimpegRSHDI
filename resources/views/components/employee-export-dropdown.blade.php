@props(['route', 'formats' => ['pdf', 'excel', 'csv']])

{{-- Export Dropdown untuk Employee Interface --}}
<div class="relative inline-block text-left" x-data="{ open: false }">
    <button @click="open = !open" type="button"
            class="inline-flex items-center px-4 sm:px-5 py-2 sm:py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-200 shadow-sm hover:shadow-md">
        <i class="fas fa-download mr-2 text-xs sm:text-sm"></i>
        <span class="text-sm">Export</span>
        <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>

    {{-- Mobile: Fixed bottom sheet --}}
    <template x-teleport="body">
        <div x-show="open"
             @click.away="open = false"
             class="sm:hidden fixed inset-0 z-[9999]">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/30" @click="open = false"></div>
            {{-- Bottom Sheet --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform translate-y-full"
                 x-transition:enter-end="transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="transform translate-y-0"
                 x-transition:leave-end="transform translate-y-full"
                 class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl p-4 pb-8">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-4"></div>
                <h3 class="text-lg font-bold text-gray-800 mb-3 text-center">Export Data</h3>
                <div class="space-y-2">
                    @if(in_array('pdf', $formats))
                    <a href="{{ route($route, array_merge(request()->all(), ['format' => 'pdf'])) }}"
                       target="_blank"
                       class="flex items-center p-4 rounded-xl bg-red-50 hover:bg-red-100 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center mr-4">
                            <i class="fas fa-file-pdf text-2xl text-red-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">Export PDF</div>
                            <div class="text-sm text-gray-500">Format dokumen cetak</div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                    @endif
                    @if(in_array('excel', $formats))
                    <a href="{{ route($route, array_merge(request()->all(), ['format' => 'excel'])) }}"
                       class="flex items-center p-4 rounded-xl bg-green-50 hover:bg-green-100 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                            <i class="fas fa-file-excel text-2xl text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">Export Excel</div>
                            <div class="text-sm text-gray-500">Format spreadsheet</div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                    @endif
                    @if(in_array('csv', $formats))
                    <a href="{{ route($route, array_merge(request()->all(), ['format' => 'csv'])) }}"
                       class="flex items-center p-4 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
                            <i class="fas fa-file-csv text-2xl text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">Export CSV</div>
                            <div class="text-sm text-gray-500">Format comma separated</div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                    @endif
                </div>
                <button @click="open = false" class="w-full mt-4 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl font-semibold text-gray-700 transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </template>

    {{-- Desktop Dropdown --}}
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="hidden sm:block absolute right-0 z-50 mt-2 w-72 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden">
        <div class="py-2">
            @if(in_array('pdf', $formats))
            <a href="{{ route($route, array_merge(request()->all(), ['format' => 'pdf'])) }}"
               target="_blank"
               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-all duration-150">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-red-100 group-hover:bg-red-200 mr-3 transition-colors duration-150">
                    <i class="fas fa-file-pdf text-lg text-red-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-sm">Export PDF</div>
                    <div class="text-xs text-gray-500">Format dokumen cetak</div>
                </div>
                <i class="fas fa-external-link-alt text-gray-400 group-hover:text-red-600 text-xs"></i>
            </a>
            @endif

            @if(in_array('excel', $formats))
            <a href="{{ route($route, array_merge(request()->all(), ['format' => 'excel'])) }}"
               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-all duration-150">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-green-100 group-hover:bg-green-200 mr-3 transition-colors duration-150">
                    <i class="fas fa-file-excel text-lg text-green-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-sm">Export Excel</div>
                    <div class="text-xs text-gray-500">Format spreadsheet</div>
                </div>
                <i class="fas fa-download text-gray-400 group-hover:text-green-600 text-xs"></i>
            </a>
            @endif

            @if(in_array('csv', $formats))
            <a href="{{ route($route, array_merge(request()->all(), ['format' => 'csv'])) }}"
               class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-150">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-100 group-hover:bg-blue-200 mr-3 transition-colors duration-150">
                    <i class="fas fa-file-csv text-lg text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-sm">Export CSV</div>
                    <div class="text-xs text-gray-500">Format comma separated</div>
                </div>
                <i class="fas fa-download text-gray-400 group-hover:text-blue-600 text-xs"></i>
            </a>
            @endif
        </div>
    </div>
</div>
