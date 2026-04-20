@props(['route', 'title' => 'Export Data', 'formats' => ['pdf', 'excel', 'csv'], 'showDateRange' => true])

{{-- Export Buttons Inline + Filter Modal --}}
<div x-data="{
    showExportModal: false,
    exportFormat: '',
    dateFrom: @js(request('date_from')) || '',
    dateTo: @js(request('date_to')) || '',
    get isDateRangeInvalid() {
        if (!this.dateFrom || !this.dateTo) return false;
        return this.dateFrom > this.dateTo;
    },
    openExport(format) {
        this.exportFormat = format;
        this.showExportModal = true;
    },
    sanitizeAndSubmit(event) {
        if (this.isDateRangeInvalid) {
            event.preventDefault();
            return;
        }

        Array.from(event.target.elements).forEach((el) => {
            if (!el.name || el.disabled) return;
            const type = (el.type || '').toLowerCase();
            if (['button', 'submit', 'reset'].includes(type)) return;
            const isEmpty = el.value === null || String(el.value).trim() === '';
            if (isEmpty) el.disabled = true;
        });
    }
}">
    {{-- Inline Buttons --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-1 flex items-center gap-1">
        @if(in_array('pdf', $formats))
        <button @click="openExport('pdf')" type="button"
                class="inline-flex items-center justify-center px-3 py-2 rounded-md text-xs sm:text-sm font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition">
            <i class="fas fa-file-pdf mr-1.5"></i>PDF
        </button>
        @endif
        @if(in_array('excel', $formats))
        <button @click="openExport('excel')" type="button"
                class="inline-flex items-center justify-center px-3 py-2 rounded-md text-xs sm:text-sm font-semibold text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 transition">
            <i class="fas fa-file-excel mr-1.5"></i>Excel
        </button>
        @endif
        @if(in_array('csv', $formats))
        <button @click="openExport('csv')" type="button"
                class="inline-flex items-center justify-center px-3 py-2 rounded-md text-xs sm:text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition">
            <i class="fas fa-file-csv mr-1.5"></i>CSV
        </button>
        @endif
    </div>

    {{-- Filter Modal --}}
    <template x-teleport="body">
        <div x-show="showExportModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             @keydown.escape.window="showExportModal = false">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/40" @click="showExportModal = false"></div>

            {{-- Modal Content --}}
            <div x-show="showExportModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                 @click.stop>
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                 :class="{
                                     'bg-red-100': exportFormat === 'pdf',
                                     'bg-green-100': exportFormat === 'excel',
                                     'bg-blue-100': exportFormat === 'csv'
                                 }">
                                <i class="fas text-lg"
                                   :class="{
                                       'fa-file-pdf text-red-600': exportFormat === 'pdf',
                                       'fa-file-excel text-green-600': exportFormat === 'excel',
                                       'fa-file-csv text-blue-600': exportFormat === 'csv'
                                   }"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">{{ $title }}</h3>
                                <p class="text-sm text-gray-500">Format: <span class="font-semibold uppercase" x-text="exportFormat"></span></p>
                            </div>
                        </div>
                        <button @click="showExportModal = false" class="text-gray-400 hover:text-gray-600 transition p-1">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                {{-- Form --}}
                    <form action="{{ $route }}" method="GET" class="p-6 space-y-4"
                        @submit="sanitizeAndSubmit($event)">
                    <input type="hidden" name="format" :value="exportFormat">

                    {{-- Date Range --}}
                    @if($showDateRange)
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from"
                                   x-model="dateFrom"
                                   :max="dateTo || null"
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to"
                                   x-model="dateTo"
                                   :min="dateFrom || null"
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        </div>
                    </div>
                    <p x-show="isDateRangeInvalid" x-cloak class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        Rentang tanggal tidak valid. Tanggal mulai tidak boleh lebih besar dari tanggal selesai.
                    </p>
                    @endif

                    {{-- Extra Filters (slot) --}}
                    {{ $slot }}

                    {{-- Info --}}
                    @if($showDateRange)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-start gap-2">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <p class="text-xs text-amber-700">Kosongkan tanggal untuk mengexport semua data. Isi tanggal untuk memfilter berdasarkan periode tertentu.</p>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showExportModal = false"
                                class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition text-sm">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="isDateRangeInvalid"
                                class="flex-1 px-4 py-2.5 font-semibold rounded-lg transition text-sm text-white shadow-sm"
                                :class="isDateRangeInvalid
                                    ? 'bg-gray-300 cursor-not-allowed'
                                    : {
                                        'bg-red-600 hover:bg-red-700': exportFormat === 'pdf',
                                        'bg-green-600 hover:bg-green-700': exportFormat === 'excel',
                                        'bg-blue-600 hover:bg-blue-700': exportFormat === 'csv'
                                    }"
                                >
                            <i class="fas fa-download mr-2"></i>
                            Export <span x-text="exportFormat.toUpperCase()"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
