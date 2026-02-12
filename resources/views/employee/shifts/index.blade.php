@extends('layouts.employee')

@section('title', 'Jadwal Kerja')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="shiftCalendar()" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <i class="fas fa-calendar-week text-purple-600 text-2xl"></i>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Jadwal Kerja Saya</h1>
                <p class="text-gray-600 text-sm mt-1">Lihat jadwal shift Anda</p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Month Navigation (inline with calendar style like Kalender Cuti & Lembur) -->

    <!-- Regular Shift Info -->
    @if($workerShift)
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl shadow-md p-5 mb-6">
            <h3 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i>
                Jadwal Regular Anda:
            </h3>
            
            <div class="text-sm">
                <p class="text-gray-700">Pola: <span class="font-medium text-blue-700">Tetap</span></p>
                <p class="text-gray-700 mt-1">Shift: <span class="font-medium text-green-700">{{ $workerShift->shift->name ?? '-' }}</span></p>
                <p class="text-gray-600 mt-1">
                    {{ $workerShift->shift ? \Carbon\Carbon::parse($workerShift->shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($workerShift->shift->end_time)->format('H:i') : '' }}
                </p>
                <p class="text-gray-600 mt-1">
                    Jadwal per hari mengikuti pengaturan shift.
                </p>
            </div>
        </div>
    @else
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-300 rounded-xl shadow-md p-5 mb-6">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-1"></i>
                <div>
                    <p class="font-semibold text-yellow-800 mb-1">Belum Ada Jadwal Regular</p>
                    <p class="text-yellow-700 text-sm">Anda belum memiliki jadwal shift regular. Hubungi HR untuk informasi lebih lanjut.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Calendar (styled similar to Kalender Cuti & Lembur) -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Calendar Navigation -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 flex items-center justify-between">
            <a href="{{ route('employee.shifts.index', ['month' => $date->copy()->subMonth()->month, 'year' => $date->copy()->subMonth()->year]) }}" 
               class="p-2 hover:bg-green-500 rounded-lg transition">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h2 class="text-lg font-bold flex items-center gap-2">
                <i class="far fa-calendar-alt"></i>
                {{ $date->translatedFormat('F Y') }}
            </h2>
            <a href="{{ route('employee.shifts.index', ['month' => $date->copy()->addMonth()->month, 'year' => $date->copy()->addMonth()->year]) }}" 
               class="p-2 hover:bg-green-500 rounded-lg transition">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="-mx-4 sm:mx-0 overflow-x-auto pb-2">
            <div class="min-w-[980px] lg:min-w-full p-4">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-2 mb-2">
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Min</div>
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Sen</div>
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Sel</div>
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Rab</div>
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Kam</div>
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Jum</div>
                    <div class="text-center text-sm font-semibold text-gray-600 py-2">Sab</div>
                </div>

                <!-- Calendar Days -->
                @foreach($calendar as $week)
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        @foreach($week as $day)
                            @php
                                $isToday = $day['isToday'];
                                $isCurrentMonth = $day['isCurrentMonth'];
                                $hasShift = !empty($day['shift']);
                                $isHoliday = $day['isHoliday'] ?? false;
                            @endphp
                            <div
                                class="min-h-24 border rounded-lg p-2 relative cursor-pointer transition
                                       {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : ($isHoliday ? 'bg-red-100 border-red-400' : ($hasShift ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-300')) }}
                                       {{ $isToday ? 'ring-2 ring-green-500' : '' }}"
                                data-date-label="{{ $day['date']->translatedFormat('l, d F Y') }}"
                                data-has-shift="{{ $hasShift ? 1 : 0 }}"
                                data-shift-name="{{ $hasShift ? $day['shift']->name : '' }}"
                                data-start="{{ $hasShift ? \Carbon\Carbon::parse($day['shift']->start_time)->format('H:i') : '' }}"
                                data-end="{{ $hasShift ? \Carbon\Carbon::parse($day['shift']->end_time)->format('H:i') : '' }}"
                                data-is-current="{{ $isCurrentMonth ? 1 : 0 }}"
                                data-is-override="{{ $day['isOverride'] ? 1 : 0 }}"
                                data-is-holiday="{{ $isHoliday ? 1 : 0 }}"
                                data-holiday-name="{{ $isHoliday ? ($day['holidayName'] ?? 'Libur Nasional') : '' }}"
                                @click="openDay($event.currentTarget)"
                            >
                                <!-- Date Number -->
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-sm font-semibold
                                                 {{ $isCurrentMonth ? 'text-gray-800' : 'text-gray-400' }}">
                                        {{ $day['date']->day }}
                                    </span>
                                    @if($day['isOverride'])
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-orange-500 text-white" title="Jadwal Override">
                                            OVR
                                        </span>
                                    @endif
                                </div>

                                <!-- Visual indicator only (no text, just colors/icons) -->
                                @if($isHoliday)
                                    <div class="mt-4 flex items-center justify-center">
                                        <i class="fas fa-flag text-red-600 text-lg"></i>
                                    </div>
                                @elseif($hasShift)
                                    {{-- Working day - blue background only, no text shown --}}
                                @else
                                    @if($isCurrentMonth)
                                        <div class="mt-4 flex items-center justify-center">
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white rounded-xl shadow-md p-5">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-purple-600"></i>
            Keterangan:
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-blue-50 border-2 border-blue-300 rounded"></div>
                <span class="text-gray-700">Hari Kerja</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-white border-2 border-gray-300 rounded flex items-center justify-center">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                </div>
                <span class="text-gray-700">Libur</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-red-100 border-2 border-red-400 rounded flex items-center justify-center">
                    <i class="fas fa-flag text-red-600 text-xs"></i>
                </div>
                <span class="text-gray-700">Libur Nasional</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-gray-50 border-2 border-gray-300 rounded"></div>
                <span class="text-gray-700">Bulan Lain</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-3 py-1 rounded-full shadow-sm text-xs font-medium">
                    <i class="fas fa-exclamation mr-1"></i>OVR
                </span>
                <span class="text-gray-700">Override</span>
            </div>
        </div>
    </div>

    <!-- Modal for Shift Detail -->
    <div x-show="showModal" 
         x-cloak
         @click="showModal = false"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-white/30">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="modalDate"></h3>
                    <p class="text-sm text-gray-600 mt-1" x-text="modalTitle"></p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="modalShiftTime">
                <p class="text-sm text-gray-800 font-medium mb-2">
                    <i class="fas fa-clock mr-1 text-blue-500"></i>
                    <span x-text="modalShiftTime"></span>
                </p>
            </template>

            <p class="text-sm text-gray-600" x-text="modalNote"></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function shiftCalendar() {
    return {
        showModal: false,
        modalDate: '',
        modalTitle: '',
        modalShiftTime: '',
        modalNote: '',

        init() {},

        openDay(el) {
            const isCurrent = el.dataset.isCurrent === '1';
            if (!isCurrent) return;

            const isHoliday = el.dataset.isHoliday === '1';
            const hasShift = el.dataset.hasShift === '1';
            this.modalDate = el.dataset.dateLabel || '';

            if (isHoliday) {
                this.modalTitle = '🇮🇩 Libur Nasional';
                this.modalShiftTime = '';
                this.modalNote = (el.dataset.holidayName || 'Libur Nasional') + '. Anda tidak perlu melakukan absensi pada hari ini.';
            } else if (hasShift) {
                this.modalTitle = el.dataset.shiftName || 'Jadwal Kerja';
                const start = el.dataset.start || '';
                const end = el.dataset.end || '';
                this.modalShiftTime = start && end ? `${start} - ${end}` : '';
                this.modalNote = el.dataset.isOverride === '1'
                    ? 'Jadwal ini merupakan override khusus untuk hari ini.'
                    : 'Anda dijadwalkan bekerja pada hari ini.';
            } else {
                this.modalTitle = 'Libur';
                this.modalShiftTime = '';
                this.modalNote = 'Anda tidak memiliki jadwal kerja pada hari ini.';
            }

            this.showModal = true;
        }
    }
}
</script>
@endpush
