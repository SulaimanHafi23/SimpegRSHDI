@extends('layouts.admin')

@section('title', 'Riwayat Presensi - ' . ($worker->name ?? ''))

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <x-button
                variant="secondary"
                size="sm"
                icon="fas fa-arrow-left"
                onclick="window.history.back()">
            </x-button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Riwayat Presensi</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $worker->name ?? '' }} - {{ $worker->nip ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- Filter Month/Year --}}
    <x-card>
        <form method="GET" action="{{ route('admin.attendance.history', $worker->id) }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <x-button type="submit" variant="primary" icon="fas fa-search">
                Tampilkan
            </x-button>
        </form>
    </x-card>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stats-card
            title="Total Hadir"
            :value="$totalPresent"
            icon="fas fa-check-circle"
            color="green" />

        <x-stats-card
            title="Total Terlambat"
            :value="$totalLate"
            icon="fas fa-clock"
            color="yellow" />

        <x-stats-card
            title="Total Absen"
            :value="$totalAbsent"
            icon="fas fa-times-circle"
            color="red" />

        <x-stats-card
            title="Total Cuti"
            :value="$totalLeave"
            icon="fas fa-calendar-times"
            color="blue" />
    </div>

    {{-- Shift Schedule Information --}}
    @if($worker->activeWorkerShift)
        <x-card>
            <div class="flex items-center mb-4">
                <i class="fas fa-clock text-indigo-600 text-2xl mr-3"></i>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Jadwal Shift Aktif</h3>
                    <p class="text-sm text-gray-600">Periode: {{ $worker->activeWorkerShift->effective_from->format('d M Y') }}
                        @if($worker->activeWorkerShift->effective_until)
                            - {{ $worker->activeWorkerShift->effective_until->format('d M Y') }}
                        @else
                            - Sekarang
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Shift Details --}}
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-4 border border-indigo-200">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-business-time text-indigo-600 mr-2"></i>
                        Detail Shift: {{ $worker->activeWorkerShift->shift->name }}
                    </h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between bg-white rounded px-3 py-2">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                                Jam Masuk
                            </span>
                            <span class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($worker->activeWorkerShift->shift->start_time)->format('H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between bg-white rounded px-3 py-2">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                                Jam Pulang
                            </span>
                            <span class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($worker->activeWorkerShift->shift->end_time)->format('H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between bg-white rounded px-3 py-2">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-hourglass-half text-blue-600 mr-2"></i>
                                Total Jam Kerja
                            </span>
                            <span class="font-semibold text-gray-900">
                                {{ $worker->activeWorkerShift->shift->total_hours }} Jam
                            </span>
                        </div>
                        <div class="flex items-center justify-between bg-white rounded px-3 py-2">
                            <span class="text-gray-600 flex items-center">
                                <i class="fas fa-clock text-yellow-600 mr-2"></i>
                                Toleransi Keterlambatan
                            </span>
                            <span class="font-semibold text-gray-900">
                                {{ $worker->activeWorkerShift->shift->grace_period_minutes }} Menit
                            </span>
                        </div>
                        @if($worker->activeWorkerShift->shift->is_overnight)
                            <div class="bg-purple-50 border border-purple-200 rounded px-3 py-2">
                                <span class="text-purple-700 flex items-center text-xs font-medium">
                                    <i class="fas fa-moon mr-2"></i>
                                    Shift Malam (Melewati Tengah Malam)
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pattern Information --}}
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-calendar-week text-green-600 mr-2"></i>
                        Pola Kerja
                    </h4>
                    <div class="space-y-3">
                        <div class="bg-white rounded px-3 py-2">
                            <span class="text-xs text-gray-500 block mb-1">Tipe Pola</span>
                            <span class="font-semibold text-gray-900 capitalize">
                                @if($worker->activeWorkerShift->pattern_type === 'fixed')
                                    <i class="fas fa-lock text-blue-600 mr-1"></i>Tetap
                                @elseif($worker->activeWorkerShift->pattern_type === 'rotating')
                                    <i class="fas fa-sync-alt text-purple-600 mr-1"></i>Bergilir
                                @elseif($worker->activeWorkerShift->pattern_type === 'custom')
                                    <i class="fas fa-cog text-orange-600 mr-1"></i>Custom
                                @else
                                    {{ $worker->activeWorkerShift->pattern_type }}
                                @endif
                            </span>
                        </div>

                        @if($worker->activeWorkerShift->pattern_type === 'fixed')
                            <div class="bg-white rounded px-3 py-2">
                                <span class="text-xs text-gray-500 block mb-2">Hari Kerja</span>
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        // For fixed pattern, if custom_working_days is set, use it. Otherwise default to Mon-Fri (1-5)
                                        $workingDays = $worker->activeWorkerShift->custom_working_days ?? [1, 2, 3, 4, 5];
                                    @endphp
                                    @foreach($workingDays as $dayIndex)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                            {{ $dayNames[$dayIndex] ?? $dayIndex }}
                                        </span>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-2 italic">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Shift tetap {{ $worker->activeWorkerShift->shift->name }} setiap hari kerja
                                </p>
                            </div>
                        @endif

                        @if($worker->activeWorkerShift->pattern_type === 'rotating' && $worker->activeWorkerShift->rotating_days)
                            <div class="bg-white rounded px-3 py-2">
                                <span class="text-xs text-gray-500 block mb-2">Hari Kerja Bergilir</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($worker->activeWorkerShift->rotating_days as $day)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                            {{ $day }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($worker->activeWorkerShift->pattern_type === 'custom' && $worker->activeWorkerShift->custom_working_days)
                            <div class="bg-white rounded px-3 py-2">
                                <span class="text-xs text-gray-500 block mb-2">Hari Kerja Custom</span>
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    @endphp
                                    @foreach($worker->activeWorkerShift->custom_working_days as $dayIndex)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                            {{ $dayNames[$dayIndex] ?? $dayIndex }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($worker->activeWorkerShift->notes)
                            <div class="bg-yellow-50 border border-yellow-200 rounded px-3 py-2">
                                <span class="text-xs text-gray-500 block mb-1">
                                    <i class="fas fa-info-circle text-yellow-600 mr-1"></i>Catatan
                                </span>
                                <span class="text-sm text-gray-700">{{ $worker->activeWorkerShift->notes }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    @else
        <x-card>
            <div class="flex items-center justify-center py-8">
                <div class="text-center">
                    <i class="fas fa-clock text-gray-400 text-5xl mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Jadwal Shift Aktif</h3>
                    <p class="text-sm text-gray-500">Pegawai ini belum memiliki jadwal shift yang aktif.</p>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Calendar View --}}
    <x-card>
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                Kalender Presensi - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
            </h3>
        </div>

        {{-- Scrollable Calendar Container for Mobile --}}
        <div class="overflow-x-auto -mx-4 px-4 md:mx-0 md:px-0">
            <div class="min-w-[700px]">
                {{-- Calendar Header (Days of Week) --}}
                <div class="grid grid-cols-7 gap-2 mb-2">
                    <div class="text-center font-semibold text-sm text-red-600 py-2">Minggu</div>
                    <div class="text-center font-semibold text-sm text-gray-700 py-2">Senin</div>
                    <div class="text-center font-semibold text-sm text-gray-700 py-2">Selasa</div>
                    <div class="text-center font-semibold text-sm text-gray-700 py-2">Rabu</div>
                    <div class="text-center font-semibold text-sm text-gray-700 py-2">Kamis</div>
                    <div class="text-center font-semibold text-sm text-gray-700 py-2">Jumat</div>
                    <div class="text-center font-semibold text-sm text-gray-700 py-2">Sabtu</div>
                </div>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-2">
            {{-- Empty cells for days before month starts --}}
            @php
                $startDayOfWeek = $startDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
                $emptyCells = $startDayOfWeek;
            @endphp

            @for ($i = 0; $i < $emptyCells; $i++)
                <div class="border border-gray-200 rounded-lg p-2 bg-gray-50 min-h-[120px]"></div>
            @endfor

            {{-- Calendar Days --}}
            @foreach($calendarData as $dayData)
                @php
                    $date = $dayData['date'];
                    $attendance = $dayData['attendance'];
                    $shift = $dayData['shift'];
                    $isWeekend = $dayData['isWeekend'];
                    $isToday = $date->isToday();

                    // Determine background color
                    $bgColor = 'bg-white';
                    if ($isToday) {
                        $bgColor = 'bg-blue-50 border-blue-400';
                    } elseif ($isWeekend) {
                        $bgColor = 'bg-gray-50';
                    }

                    // Status badge config
                    $statusConfig = [
                        'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fas fa-check-circle', 'label' => 'Hadir'],
                        'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fas fa-times-circle', 'label' => 'Absen'],
                        'leave' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fas fa-calendar-times', 'label' => 'Cuti'],
                        'sick' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fas fa-notes-medical', 'label' => 'Sakit'],
                        'permission' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'fas fa-file-signature', 'label' => 'Izin'],
                    ];
                @endphp

                <div class="border border-gray-300 rounded-lg p-3 {{ $bgColor }} min-h-[180px] hover:shadow-lg transition-all relative overflow-hidden">
                    {{-- Date Number --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xl font-bold {{ $isWeekend ? 'text-red-600' : 'text-gray-800' }}">
                            {{ $dayData['day'] }}
                        </span>
                        @if($isToday)
                            <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-semibold">Hari Ini</span>
                        @endif
                    </div>

                    {{-- Shift Schedule Info --}}
                    @if($shift)
                        <div class="mb-2 pb-2 border-b border-gray-200">
                            <div class="text-xs font-semibold text-indigo-700 mb-1 flex items-center">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $shift->name }}
                            </div>
                            <div class="text-xs text-gray-600 flex items-center justify-between">
                                <span class="flex items-center">
                                    <i class="fas fa-sign-in-alt text-green-600 mr-1"></i>{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Attendance Status --}}
                    @if($attendance)
                        @php
                            $config = $statusConfig[$attendance->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => 'fas fa-question-circle', 'label' => 'Unknown'];
                        @endphp

                        <div class="space-y-1.5">
                            {{-- Status Badge --}}
                            <div class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }} w-full justify-center">
                                <i class="{{ $config['icon'] }} mr-1"></i>
                                {{ $config['label'] }}
                            </div>

                            {{-- Late & Early Leave Info --}}
                            <div class="flex gap-1">
                                @if($attendance->is_late && $attendance->late_minutes)
                                    <div class="flex-1 inline-flex items-center justify-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-700">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        +{{ $attendance->late_minutes }}m
                                    </div>
                                @endif

                                @if($attendance->is_early_leave && $attendance->early_leave_minutes)
                                    <div class="flex-1 inline-flex items-center justify-center px-2 py-1 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                        <i class="fas fa-running mr-1"></i>
                                        -{{ $attendance->early_leave_minutes }}m
                                    </div>
                                @endif
                            </div>

                            {{-- Check-in & Check-out Time --}}
                            <div class="bg-gray-50 rounded-md p-2 space-y-1">
                                @if($attendance->check_in)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-600">
                                            <i class="fas fa-sign-in-alt text-green-600 mr-1"></i>Masuk
                                        </span>
                                        <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</span>
                                    </div>
                                @endif

                                @if($attendance->check_out)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-600">
                                            <i class="fas fa-sign-out-alt text-red-600 mr-1"></i>Keluar
                                        </span>
                                        <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</span>
                                    </div>
                                @endif

                                {{-- Work Duration --}}
                                @if($attendance->check_in && $attendance->check_out)
                                    @php
                                        $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                        $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                        $duration = $checkIn->diff($checkOut);
                                        $hours = $duration->h;
                                        $minutes = $duration->i;
                                    @endphp
                                    <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200">
                                        <span class="text-gray-600">
                                            <i class="fas fa-hourglass-half text-blue-600 mr-1"></i>Durasi
                                        </span>
                                        <span class="font-bold text-blue-600">{{ $hours }}j {{ $minutes }}m</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Location Info --}}
                            @if($attendance->location)
                                <div class="text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <span class="truncate flex-1">{{ Str::limit($attendance->location->name, 18) }}</span>
                                </div>
                            @endif

                            {{-- Notes/Remarks --}}
                            @if($attendance->notes)
                                <div class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded flex items-start">
                                    <i class="fas fa-sticky-note mr-1 mt-0.5"></i>
                                    <span class="flex-1 line-clamp-2">{{ $attendance->notes }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- No attendance record --}}
                        @if($shift && !$isWeekend)
                            <div class="text-xs text-gray-400 italic mt-2 text-center bg-gray-50 rounded py-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Belum ada presensi
                            </div>
                        @elseif($isWeekend)
                            <div class="text-xs text-gray-400 italic mt-2 text-center bg-gray-50 rounded py-2">
                                <i class="fas fa-calendar-day mr-1"></i>
                                Hari Libur
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
            </div>
        </div>
        </div>

        {{-- Legend --}}
        <div class="mt-6 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Keterangan:</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-sm">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-green-100 border border-green-300 mr-2"></div>
                    <span class="text-gray-700">Hadir</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-red-100 border border-red-300 mr-2"></div>
                    <span class="text-gray-700">Absen</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-yellow-100 border border-yellow-300 mr-2"></div>
                    <span class="text-gray-700">Cuti</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-blue-100 border border-blue-300 mr-2"></div>
                    <span class="text-gray-700">Sakit</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-purple-100 border border-purple-300 mr-2"></div>
                    <span class="text-gray-700">Izin</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-orange-100 border border-orange-300 mr-2"></div>
                    <span class="text-gray-700">Terlambat</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-amber-100 border border-amber-300 mr-2"></div>
                    <span class="text-gray-700">Pulang Cepat</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded bg-blue-50 border border-blue-400 mr-2"></div>
                    <span class="text-gray-700">Hari Ini</span>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Modal for Attendance Detail --}}
    <div id="attendanceDetailModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h3 class="text-lg font-bold text-gray-900">Detail Presensi</h3>
                <button onclick="closeAttendanceDetail()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="attendanceDetailContent" class="space-y-4">
                {{-- Content will be loaded here --}}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showAttendanceDetail(attendanceId, date) {
        const modal = document.getElementById('attendanceDetailModal');
        const content = document.getElementById('attendanceDetailContent');

        // Show modal
        modal.classList.remove('hidden');

        // Show loading
        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i><p class="mt-2 text-gray-600">Memuat data...</p></div>';

        // Fetch attendance detail
        fetch(`/api/attendance/${attendanceId}/detail`)
            .then(response => response.json())
            .then(data => {
                let html = '<div class="space-y-4">';

                // Date
                html += `<div class="bg-blue-50 p-3 rounded-lg"><div class="text-sm text-gray-600">Tanggal</div><div class="font-semibold text-gray-900">${date}</div></div>`;

                // Status
                html += `<div class="bg-gray-50 p-3 rounded-lg"><div class="text-sm text-gray-600">Status</div><div class="font-semibold text-gray-900 capitalize">${data.status}</div></div>`;

                // Check-in
                if (data.check_in_time) {
                    html += `<div class="bg-green-50 p-3 rounded-lg">
                        <div class="text-sm text-gray-600">Check In</div>
                        <div class="font-semibold text-gray-900">${data.check_in_time}</div>
                        ${data.is_late ? `<div class="text-xs text-orange-600 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Terlambat ${data.late_minutes} menit</div>` : ''}
                    </div>`;
                }

                // Check-out
                if (data.check_out_time) {
                    html += `<div class="bg-red-50 p-3 rounded-lg">
                        <div class="text-sm text-gray-600">Check Out</div>
                        <div class="font-semibold text-gray-900">${data.check_out_time}</div>
                        ${data.is_early_leave ? `<div class="text-xs text-amber-600 mt-1"><i class="fas fa-running mr-1"></i>Pulang ${data.early_leave_minutes} menit lebih cepat</div>` : ''}
                    </div>`;
                }

                // Location
                if (data.location) {
                    html += `<div class="bg-indigo-50 p-3 rounded-lg"><div class="text-sm text-gray-600">Lokasi</div><div class="font-semibold text-gray-900">${data.location}</div></div>`;
                }

                // Notes
                if (data.notes) {
                    html += `<div class="bg-yellow-50 p-3 rounded-lg"><div class="text-sm text-gray-600">Catatan</div><div class="text-gray-900">${data.notes}</div></div>`;
                }

                html += '</div>';
                content.innerHTML = html;
            })
            .catch(error => {
                content.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-4xl mb-2"></i><p>Gagal memuat data</p></div>';
                console.error('Error:', error);
            });
    }

    function closeAttendanceDetail() {
        document.getElementById('attendanceDetailModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('attendanceDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAttendanceDetail();
        }
    });
</script>
@endpush

@endsection
