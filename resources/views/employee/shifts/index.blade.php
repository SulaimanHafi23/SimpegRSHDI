@extends('layouts.employee')

@section('title', 'Jadwal Kerja')

@section('content')
<div class="space-y-6" x-data="shiftCalendar()" x-init="init()">
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


    <!-- Month Navigation (inline with calendar style like Kalender Cuti) -->

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

    <!-- Calendar (styled similar to Kalender Cuti) -->
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

        <div class="overflow-x-auto pb-2">
            <div class="w-full p-2 sm:p-4">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2">
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Min</div>
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Sen</div>
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Sel</div>
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Rab</div>
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Kam</div>
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Jum</div>
                    <div class="text-center text-[11px] sm:text-sm font-semibold text-gray-600 py-1.5 sm:py-2">Sab</div>
                </div>

                <!-- Calendar Days -->
                @foreach($calendar as $week)
                    <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2">
                        @foreach($week as $day)
                            @php
                                $isToday = $day['isToday'];
                                $isCurrentMonth = $day['isCurrentMonth'];
                                $hasShift = !empty($day['shift']);
                                $isHoliday = $day['isHoliday'] ?? false;
                                $isOffDay = $day['isOffDay'] ?? false;
                                    $isLeave = $day['isLeave'] ?? false;
                            @endphp
                            <div
                                class="min-h-16 sm:min-h-24 border rounded-lg p-1.5 sm:p-2 relative cursor-pointer transition
                                        {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : ($isHoliday && $hasShift ? 'bg-orange-50 border-orange-400' : ($isHoliday ? 'bg-red-100 border-red-400' : ($isLeave ? 'bg-purple-50 border-purple-300' : ($isOffDay ? 'bg-rose-50 border-rose-300' : ($hasShift ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-300'))))) }}
                                       {{ $isToday ? 'ring-2 ring-green-500' : '' }}"
                                data-date-label="{{ $day['date']->translatedFormat('l, d F Y') }}"
                                data-has-shift="{{ $hasShift ? 1 : 0 }}"
                                data-shift-name="{{ $hasShift ? $day['shift']->name : '' }}"
                                data-start="{{ $hasShift && !empty($day['schedule']['start_time']) ? \Carbon\Carbon::parse($day['schedule']['start_time'])->format('H:i') : '' }}"
                                data-end="{{ $hasShift && !empty($day['schedule']['end_time']) ? \Carbon\Carbon::parse($day['schedule']['end_time'])->format('H:i') : '' }}"
                                data-is-current="{{ $isCurrentMonth ? 1 : 0 }}"
                                data-is-override="{{ $day['isOverride'] ? 1 : 0 }}"
                                data-is-offday="{{ $isOffDay ? 1 : 0 }}"
                                data-is-leave="{{ $isLeave ? 1 : 0 }}"
                                data-leave-type="{{ $isLeave ? ($day['leaveTypeName'] ?? 'Cuti') : '' }}"
                                data-is-holiday="{{ $isHoliday ? 1 : 0 }}"
                                data-holiday-name="{{ $isHoliday ? ($day['holidayName'] ?? 'Libur Nasional') : '' }}"
                                @click="openDay($event.currentTarget)"
                            >
                                <!-- Date Number -->
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-xs sm:text-sm font-semibold
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
                                @if($isHoliday && $hasShift)
                                    {{-- Hari libur tapi departemen standby, tetap kerja --}}
                                    <div class="mt-2 flex items-center justify-center gap-1">
                                        <i class="fas fa-flag text-red-600 text-xs"></i>
                                        <i class="fas fa-briefcase text-blue-600 text-xs"></i>
                                    </div>
                                @elseif($isLeave)
                                    <div class="mt-4 flex items-center justify-center">
                                        <i class="fas fa-user-check text-purple-600 text-lg"></i>
                                    </div>
                                @elseif($isOffDay)
                                    <div class="mt-4 flex items-center justify-center">
                                        <i class="fas fa-calendar-times text-rose-600 text-lg"></i>
                                    </div>
                                @elseif($isHoliday)
                                    <div class="mt-4 flex items-center justify-center">
                                        <i class="fas fa-flag text-red-600 text-lg"></i>
                                    </div>
                                @elseif($hasShift)
                                    <div class="mt-1 space-y-0.5">
                                        <p class="text-[10px] sm:text-[11px] font-semibold text-blue-700 leading-tight truncate">
                                            {{ $day['shift']->name }}
                                        </p>
                                        <p class="text-[10px] text-gray-600 leading-tight">
                                            @if(!empty($day['schedule']['start_time']) && !empty($day['schedule']['end_time']))
                                                {{ \Carbon\Carbon::parse($day['schedule']['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($day['schedule']['end_time'])->format('H:i') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($day['shift']->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($day['shift']->end_time)->format('H:i') }}
                                            @endif
                                        </p>
                                    </div>
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

    <!-- Activity Calendar -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden" x-data="activityCalendar({ month: {{ $month }}, year: {{ $year }} })" x-init="initCalendar()">
        <div class="border-b border-gray-200 bg-gradient-to-r from-slate-700 to-slate-800 px-4 py-4 text-white sm:px-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-amber-300"></i>
                        Kalender Aktivitas
                    </h3>
                    <p class="mt-1 text-xs text-slate-200 sm:text-sm">Cuti, libur nasional, dan perjalanan dinas pada bulan ini.</p>
                </div>
                <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white/90">{{ $date->translatedFormat('F Y') }}</span>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            <div class="mb-4 flex flex-wrap gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-red-600"></span>
                    <span class="text-gray-600">Libur Nasional</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                    <span class="text-gray-600">Cuti Disetujui</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-purple-500"></span>
                    <span class="text-gray-600">Perjalanan Dinas</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="text-gray-600">Menunggu Persetujuan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                    <span class="text-gray-600">Ditolak</span>
                </div>
            </div>

            <p class="mb-2 text-xs italic text-gray-400 md:hidden"><i class="fas fa-arrows-alt-h mr-1"></i>Geser untuk melihat kalender lengkap</p>
            <div class="overflow-x-auto scroll-smooth -mx-4 px-4 md:mx-0 md:px-0">
                <div class="min-w-[600px]">
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day">
                            <div class="text-center text-sm font-semibold text-gray-600 py-2" x-text="day"></div>
                        </template>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="(day, index) in calendarDays" :key="index">
                            <div :class="getDayClass(day)"
                                 @click="day.date ? showDayEvents(day) : null"
                                 class="min-h-24 rounded-lg border p-2 relative">
                                <div class="text-sm font-semibold mb-1"
                                     :class="day.isToday ? 'text-white' : (day.isCurrentMonth ? 'text-gray-700' : 'text-gray-400')"
                                     x-text="day.day"></div>

                                <template x-if="day.events && day.events.length > 0">
                                    <div class="space-y-1">
                                        <template x-for="event in day.events.slice(0, 2)" :key="event.id">
                                            <div class="truncate rounded px-2 py-1 text-xs text-white" :style="'background-color: ' + event.color" x-text="event.title"></div>
                                        </template>
                                        <template x-if="day.events.length > 2">
                                            <div class="text-xs font-semibold text-gray-500">+<span x-text="day.events.length - 2"></span> lainnya</div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showModal"
             x-cloak
             @click="showModal = false"
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-white/30">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900" x-text="selectedDate"></h3>
                        <p class="mt-1 text-sm text-gray-600" x-text="selectedSummary"></p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <template x-if="selectedEvents.length === 0">
                    <p class="py-4 text-center text-gray-500">Tidak ada aktivitas pada tanggal ini</p>
                </template>

                <div class="space-y-3">
                    <template x-for="event in selectedEvents" :key="event.id">
                        <div class="rounded-lg border border-gray-200 p-4" :style="'border-left: 4px solid ' + event.color">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <h4 class="font-semibold text-gray-900" x-text="event.title"></h4>
                                <span class="rounded-full px-2 py-1 text-xs text-white" :style="'background-color: ' + event.color" x-text="getStatusText(event.status)"></span>
                            </div>
                            <p class="mb-2 text-sm text-gray-600" x-text="event.description"></p>
                            <template x-if="event.type === 'holiday'">
                                <p class="text-xs text-gray-500"><i class="fas fa-flag mr-1"></i>Libur Nasional Indonesia</p>
                            </template>
                            <template x-if="event.type === 'leave'">
                                <p class="text-xs text-gray-500"><i class="fas fa-calendar-day mr-1"></i><span x-text="event.days"></span> hari</p>
                            </template>
                            <template x-if="event.type === 'business-trip'">
                                <p class="text-xs text-gray-500"><i class="fas fa-plane-departure mr-1"></i><span x-text="'Tujuan: ' + event.destination"></span></p>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white rounded-xl shadow-md p-5">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-purple-600"></i>
            Keterangan:
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-7 gap-4 text-sm">
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
                <div class="w-6 h-6 bg-rose-50 border-2 border-rose-300 rounded flex items-center justify-center">
                    <i class="fas fa-calendar-times text-rose-600 text-xs"></i>
                </div>
                <span class="text-gray-700">Libur Kerja (Off-day)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-purple-50 border-2 border-purple-300 rounded flex items-center justify-center">
                    <i class="fas fa-user-check text-purple-600 text-xs"></i>
                </div>
                <span class="text-gray-700">Cuti/Izin/Sakit</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-red-100 border-2 border-red-400 rounded flex items-center justify-center">
                    <i class="fas fa-flag text-red-600 text-xs"></i>
                </div>
                <span class="text-gray-700">Libur Nasional</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-orange-50 border-2 border-orange-400 rounded flex items-center justify-center">
                    <i class="fas fa-briefcase text-blue-600 text-[8px]"></i>
                </div>
                <span class="text-gray-700">Libur - Tetap Bertugas</span>
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

    <!-- Riwayat Perubahan Shift -->
    <div class="mt-6 bg-white rounded-xl shadow-md overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-history text-gray-600"></i>
                <h3 class="text-lg font-semibold text-gray-800">Riwayat Perubahan Shift</h3>
                <span class="bg-gray-200 text-gray-700 text-xs font-medium px-2 py-0.5 rounded-full">
                    {{ $shiftHistories->count() }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Daftar shift sebelumnya yang pernah berlaku untuk Anda</p>
        </div>

        @if(isset($shiftHistories) && $shiftHistories->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode Berlaku</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Diganti</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($shiftHistories->take(10) as $history)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="bg-orange-100 rounded-lg p-2 mr-3">
                                        <i class="fas fa-clock text-orange-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $history->shift->name ?? '-' }}</p>
                                        @if($history->shift)
                                            <p class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($history->shift->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($history->shift->end_time)->format('H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($history->effective_from)->format('d M Y') }}
                                <span class="text-gray-400 mx-1">—</span>
                                @if($history->effective_until)
                                    {{ \Carbon\Carbon::parse($history->effective_until)->format('d M Y') }}
                                @else
                                    <span class="text-gray-400 italic">Tanpa batas</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($history->changed_at)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $reasonLabels = [
                                        'shift_replaced' => ['Diganti', 'bg-blue-100 text-blue-800'],
                                        'shift_updated' => ['Diperbarui', 'bg-yellow-100 text-yellow-800'],
                                        'shift_deleted' => ['Dihapus', 'bg-red-100 text-red-800'],
                                    ];
                                    $label = $reasonLabels[$history->change_reason] ?? [$history->change_reason, 'bg-gray-100 text-gray-800'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $label[1] }}">
                                    {{ $label[0] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($shiftHistories->take(10) as $history)
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="bg-orange-100 rounded-lg p-1.5">
                                <i class="fas fa-clock text-orange-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $history->shift->name ?? '-' }}</span>
                        </div>
                        @php
                            $reasonLabels = [
                                'shift_replaced' => ['Diganti', 'bg-blue-100 text-blue-800'],
                                'shift_updated' => ['Diperbarui', 'bg-yellow-100 text-yellow-800'],
                                'shift_deleted' => ['Dihapus', 'bg-red-100 text-red-800'],
                            ];
                            $label = $reasonLabels[$history->change_reason] ?? [$history->change_reason, 'bg-gray-100 text-gray-800'];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $label[1] }}">
                            {{ $label[0] }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                        @if($history->shift)
                            <p><i class="fas fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($history->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($history->shift->end_time)->format('H:i') }}</p>
                        @endif
                        <p><i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($history->effective_from)->format('d M Y') }} — {{ $history->effective_until ? \Carbon\Carbon::parse($history->effective_until)->format('d M Y') : 'Tanpa batas' }}</p>
                        <p><i class="fas fa-exchange-alt mr-1"></i>Diganti: {{ \Carbon\Carbon::parse($history->changed_at)->format('d M Y') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="p-10 text-center">
                <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-gray-800 font-medium">Belum Ada Riwayat</h4>
                <p class="text-gray-500 text-sm mt-1">Anda belum memiliki riwayat perubahan jadwal shift.</p>
            </div>
        @endif
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
            const isOffDay = el.dataset.isOffday === '1';
            const isLeave = el.dataset.isLeave === '1';
            const hasShift = el.dataset.hasShift === '1';
            this.modalDate = el.dataset.dateLabel || '';

            if (isHoliday && hasShift) {
                this.modalTitle = '🏥 Libur Nasional - Tetap Bertugas';
                const start = el.dataset.start || '';
                const end = el.dataset.end || '';
                this.modalShiftTime = start && end ? `${start} - ${end}` : '';
                this.modalNote = el.dataset.holidayName || 'Libur Nasional';
            } else if (isHoliday) {
                this.modalTitle = '🇮🇩 Libur Nasional';
                this.modalShiftTime = '';
                this.modalNote = el.dataset.holidayName || 'Libur Nasional';
            } else if (isOffDay) {
                this.modalTitle = '🏖️ Libur Kerja (Off-day)';
                this.modalShiftTime = '';
                this.modalNote = 'Off-day';
            } else if (isLeave) {
                this.modalTitle = '📝 Cuti/Izin/Sakit';
                this.modalShiftTime = '';
                this.modalNote = el.dataset.leaveType || 'Cuti';
            } else if (hasShift) {
                this.modalTitle = el.dataset.shiftName || 'Jadwal Kerja';
                const start = el.dataset.start || '';
                const end = el.dataset.end || '';
                this.modalShiftTime = start && end ? `${start} - ${end}` : '';
                this.modalNote = el.dataset.isOverride === '1'
                    ? 'Override'
                    : 'Hari kerja';
            } else {
                this.modalTitle = 'Libur';
                this.modalShiftTime = '';
                this.modalNote = 'Tidak ada jadwal kerja';
            }

            this.showModal = true;
        }
    }
}

function activityCalendar({ month, year }) {
    return {
        currentDate: new Date(year, month - 1, 1),
        calendarDays: [],
        events: [],
        showModal: false,
        selectedDate: '',
        selectedSummary: '',
        selectedEvents: [],

        initCalendar() {
            this.updateCalendar();
            this.loadEvents();
        },

        updateCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startingDayOfWeek = firstDay.getDay();
            const daysFromPrevMonth = startingDayOfWeek;
            const totalDays = lastDay.getDate();
            const totalCells = Math.ceil((daysFromPrevMonth + totalDays) / 7) * 7;

            this.calendarDays = [];

            const prevMonthLastDay = new Date(year, month, 0).getDate();
            for (let i = daysFromPrevMonth - 1; i >= 0; i--) {
                const dayNum = prevMonthLastDay - i;
                const date = new Date(year, month - 1, dayNum);
                this.calendarDays.push({ day: dayNum, date, isCurrentMonth: false, isToday: false, events: [] });
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for (let i = 1; i <= totalDays; i++) {
                const date = new Date(year, month, i);
                date.setHours(0, 0, 0, 0);
                this.calendarDays.push({ day: i, date, isCurrentMonth: true, isToday: this.isSameDay(date, today), events: [] });
            }

            const remainingCells = totalCells - this.calendarDays.length;
            for (let i = 1; i <= remainingCells; i++) {
                const date = new Date(year, month + 1, i);
                date.setHours(0, 0, 0, 0);
                this.calendarDays.push({ day: i, date, isCurrentMonth: false, isToday: false, events: [] });
            }

            this.assignEventsToCalendar();
        },

        loadEvents() {
            const start = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1).toISOString().split('T')[0];
            const end = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0).toISOString().split('T')[0];
            const cacheBuster = new Date().getTime();

            fetch(`{{ route('employee.calendar.events') }}?start=${start}&end=${end}&_=${cacheBuster}`)
                .then(response => response.json())
                .then(data => {
                    this.events = data || [];
                    this.assignEventsToCalendar();
                })
                .catch(error => console.error('Error loading activity calendar:', error));
        },

        assignEventsToCalendar() {
            this.calendarDays.forEach(day => {
                if (!day.date) return;

                const dayDate = new Date(day.date);
                dayDate.setHours(0, 0, 0, 0);

                day.events = this.events.filter(event => {
                    const eventStart = new Date(event.start);
                    eventStart.setHours(0, 0, 0, 0);

                    const eventEnd = new Date(event.end);
                    eventEnd.setHours(0, 0, 0, 0);

                    return dayDate >= eventStart && dayDate < eventEnd;
                });
            });
        },

        showDayEvents(day) {
            if (!day.date || !day.events || day.events.length === 0) return;

            this.selectedDate = day.date.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            this.selectedEvents = day.events;
            this.selectedSummary = `${day.events.length} aktivitas terjadwal`;
            this.showModal = true;
        },

        getStatusText(status) {
            const statusMap = {
                holiday: 'Libur Nasional',
                approved: 'Disetujui',
                pending: 'Menunggu',
                rejected: 'Ditolak'
            };

            return statusMap[status] || status;
        },

        getDayClass(day) {
            let classes = 'cursor-pointer hover:bg-gray-50 transition';

            if (day.isToday) {
                classes += ' bg-green-600';
            } else if (!day.isCurrentMonth) {
                classes += ' bg-gray-50';
            }

            return classes;
        },

        isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear()
                && date1.getMonth() === date2.getMonth()
                && date1.getDate() === date2.getDate();
        }
    }
}
</script>
@endpush
