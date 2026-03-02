@extends('layouts.employee')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8 py-4 sm:py-6 space-y-4 sm:space-y-6">
    <!-- Header & Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-gray-600 text-xs sm:text-sm">
                <span class="px-2 py-1 rounded-full bg-green-50 text-green-700 font-semibold">Absensi</span>
                <span class="hidden sm:inline">Lihat riwayat kehadiran Anda</span>
            </div>
            <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user-check text-green-600 text-base sm:text-lg"></i>
                Riwayat Absensi
            </h1>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-employee-export-dropdown route="employee.attendance.export" />
            @if(isset($todayOffInfo) && $todayOffInfo)
                {{-- Hari libur — tidak perlu tombol check-in --}}
            @elseif(isset($activeAttendance) && $activeAttendance && $activeAttendance->status === 'present')
                <a href="{{ route('employee.attendance.check-out-form') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150">
                    <i class="fas fa-sign-out-alt mr-2 text-xs sm:text-sm"></i>
                    Check Out
                </a>
            @else
                <a href="{{ route('employee.attendance.check-in-form') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150">
                    <i class="fas fa-sign-in-alt mr-2 text-xs sm:text-sm"></i>
                    Check In
                </a>
            @endif
        </div>
    </div>

    {{-- Banner Hari Libur / Cuti / Tanggal Merah --}}
    @if(isset($todayOffInfo) && $todayOffInfo)
        @php
            $offColors = [
                'holiday'       => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon_bg' => 'bg-red-100', 'icon' => 'text-red-600', 'title' => 'text-red-800', 'text' => 'text-red-700'],
                'leave'         => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon_bg' => 'bg-blue-100', 'icon' => 'text-blue-600', 'title' => 'text-blue-800', 'text' => 'text-blue-700'],
                'business_trip' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon_bg' => 'bg-purple-100', 'icon' => 'text-purple-600', 'title' => 'text-purple-800', 'text' => 'text-purple-700'],
                'off_day'       => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon_bg' => 'bg-amber-100', 'icon' => 'text-amber-600', 'title' => 'text-amber-800', 'text' => 'text-amber-700'],
            ];
            $offIcons = [
                'holiday'       => 'fas fa-flag',
                'leave'         => 'fas fa-calendar-check',
                'business_trip' => 'fas fa-plane',
                'off_day'       => 'fas fa-bed',
            ];
            $c = $offColors[$todayOffInfo['type']] ?? $offColors['off_day'];
            $icon = $offIcons[$todayOffInfo['type']] ?? 'fas fa-info-circle';
        @endphp
        <div class="{{ $c['bg'] }} {{ $c['border'] }} border rounded-xl p-4 sm:p-5">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="shrink-0 w-10 h-10 sm:w-12 sm:h-12 {{ $c['icon_bg'] }} rounded-xl flex items-center justify-center">
                    <i class="{{ $icon }} {{ $c['icon'] }} text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold {{ $c['title'] }} text-sm sm:text-base">
                        {{ $todayOffInfo['title'] }}
                    </h3>
                    <p class="{{ $c['text'] }} text-xs sm:text-sm mt-0.5">
                        {{ $todayOffInfo['reason'] }}
                    </p>
                    <p class="{{ $c['text'] }} text-xs mt-2 opacity-75">
                        <i class="fas fa-info-circle mr-1"></i>
                        Anda tidak perlu melakukan absensi hari ini. Selamat beristirahat!
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Cards - Statistik Bulan Ini -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-700">
                <i class="fas fa-chart-pie text-sm"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500">Riwayat Presensi</p>
                <p class="text-sm sm:text-base font-semibold text-gray-800">Statistik Kehadiran {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('F Y') }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <div class="bg-gray-50 rounded-xl p-4 sm:p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 mb-1">Total Hari</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $summary['total_days'] }}</p>
                    </div>
                    <div class="bg-gray-100 p-2 sm:p-3 rounded-lg">
                        <i class="fas fa-calendar text-gray-600 text-base sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 rounded-xl p-4 sm:p-5 border border-green-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-green-700 mb-1">Hadir</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $summary['present'] }}</p>
                    </div>
                    <div class="bg-green-100 p-2 sm:p-3 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-base sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4 sm:p-5 border border-emerald-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-emerald-700 mb-1">Sempurna</p>
                        <p class="text-xl sm:text-2xl font-bold text-emerald-600">{{ $summary['perfect'] ?? 0 }}</p>
                    </div>
                    <div class="bg-emerald-100 p-2 sm:p-3 rounded-lg">
                        <i class="fas fa-star text-emerald-600 text-base sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 rounded-xl p-4 sm:p-5 border border-yellow-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-yellow-700 mb-1">Terlambat</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $summary['late'] }}</p>
                    </div>
                    <div class="bg-yellow-100 p-2 sm:p-3 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-base sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-xl p-4 sm:p-5 border border-orange-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-orange-700 mb-1">Pulang Cepat</p>
                        <p class="text-xl sm:text-2xl font-bold text-orange-600">{{ $summary['early_leave'] ?? 0 }}</p>
                    </div>
                    <div class="bg-orange-100 p-2 sm:p-3 rounded-lg">
                        <i class="fas fa-running text-orange-600 text-base sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-red-50 rounded-xl p-4 sm:p-5 border border-red-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-red-700 mb-1">Tidak Hadir</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-600">{{ $summary['absent'] }}</p>
                    </div>
                    <div class="bg-red-100 p-2 sm:p-3 rounded-lg">
                        <i class="fas fa-times-circle text-red-600 text-base sm:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-50 text-green-700"><i class="fas fa-filter text-sm"></i></span>
                <div>
                    <p class="text-xs text-gray-500">Pencarian & Filter</p>
                    <p class="text-sm sm:text-base font-semibold text-gray-800">Atur data yang ingin ditampilkan</p>
                </div>
            </div>
            <button @click="showFilters = !showFilters" class="text-gray-600 hover:text-gray-800 p-2">
                <i class="fas text-sm sm:text-base" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('employee.attendance.index') }}" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-3 sm:mb-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                        <i class="fas fa-search mr-1 text-xs"></i>
                        Cari
                    </label>
                    <input type="text"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari tanggal, status..."
                           class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           x-data="{ value: '{{ $filters['search'] ?? '' }}' }"
                           x-model="value">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                        <i class="far fa-calendar mr-1 text-xs"></i>
                        Dari
                    </label>
                    <input type="date"
                           name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                        <i class="far fa-calendar-check mr-1 text-xs"></i>
                        Sampai
                    </label>
                    <input type="date"
                           name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                        <i class="fas fa-filter mr-1 text-xs"></i>
                        Status
                    </label>
                    <select name="status"
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ ($filters['status'] ?? '') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="Terlambat" {{ ($filters['status'] ?? '') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="Tidak Hadir" {{ ($filters['status'] ?? '') == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                        <i class="fas fa-list-ol mr-1 text-xs"></i>
                        Tampilkan
                    </label>
                    <select name="per_page"
                            class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="10" {{ ($filters['per_page'] ?? 15) == 10 ? 'selected' : '' }}>10 per halaman</option>
                        <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15 per halaman</option>
                        <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25 per halaman</option>
                        <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50 per halaman</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                        <i class="fas fa-search mr-1 sm:mr-2 text-xs"></i>
                        <span class="hidden sm:inline">Terapkan</span>
                        <span class="sm:hidden">Cari</span>
                    </button>
                    <a href="{{ route('employee.attendance.index') }}"
                       class="px-3 sm:px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                        <i class="fas fa-redo text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(!empty($filters['search']) || !empty($filters['status']))
            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-2">
                <span class="text-xs sm:text-sm text-gray-600">Filter Aktif:</span>
                @if(!empty($filters['search']))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-50 text-blue-800 border border-blue-100">
                        <i class="fas fa-search mr-1"></i>
                        "{{ $filters['search'] }}"
                        <a href="{{ route('employee.attendance.index', array_merge(request()->except('search'))) }}"
                           class="ml-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(!empty($filters['status']))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-green-50 text-green-800 border border-green-100">
                        <i class="fas fa-filter mr-1"></i>
                        Status: {{ $filters['status'] }}
                        <a href="{{ route('employee.attendance.index', array_merge(request()->except('status'))) }}"
                           class="ml-2 text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
            </div>
            @endif
        </form>
    </div>

    <!-- Data Section -->
    <div class="space-y-3 sm:space-y-4">
        <!-- Mobile Card View -->
        @if($attendances->count())
            <div class="sm:hidden space-y-3">
                @foreach($attendances as $attendance)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}
                            </div>
                            <div>
                                @if($attendance->status === 'present')
                                    @if($attendance->is_late)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Terlambat</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Hadir</span>
                                    @endif
                                @elseif($attendance->status === 'late')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Terlambat</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Hadir</span>
                                @elseif($attendance->status === 'sick')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Sakit</span>
                                @elseif($attendance->status === 'permission')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Izin</span>
                                @elseif($attendance->status === 'leave')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Cuti</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-700">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-sign-in-alt text-green-600"></i>
                                <span>In: {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</span>
                                @if($attendance->check_in && $attendance->is_late && $attendance->late_minutes > 0)
                                    <span class="text-red-500 ml-1">({{ $attendance->late_minutes }}m)</span>
                                @elseif($attendance->check_in && $attendance->is_late)
                                    <span class="text-yellow-500 ml-1">(Terlambat)</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-sign-out-alt text-red-500"></i>
                                <span>Out: {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</span>
                            </div>
                            @php
                                $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);
                                $shiftOverride = $attendance->worker->shiftOverrides
                                    ->where('override_date', $attendanceDate->format('Y-m-d'))
                                    ->first();

                                $mobileShift = null;
                                if ($shiftOverride && $shiftOverride->shift) {
                                    $mobileShift = $shiftOverride->shift;
                                } else {
                                    $activeWorkerShift = $attendance->worker->workerShifts
                                        ->first(function($ws) use ($attendanceDate) {
                                            return $ws->isActiveOnDate($attendanceDate);
                                        });

                                    if ($activeWorkerShift && $activeWorkerShift->shift) {
                                        $mobileShift = $activeWorkerShift->shift;
                                    } elseif ($attendance->worker->shift) {
                                        $mobileShift = $attendance->worker->shift;
                                    }
                                }
                            @endphp
                            @if($mobileShift)
                            <div class="flex items-center gap-1 col-span-2 text-gray-600">
                                <i class="fas fa-clock text-blue-600"></i>
                                <span>Shift: {{ \Carbon\Carbon::parse($mobileShift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($mobileShift->end_time)->format('H:i') }}</span>
                            </div>
                            @endif
                            <div class="flex items-center gap-1 col-span-2">
                                <i class="far fa-clock text-indigo-600"></i>
                                <span>Durasi:
                                    @if($attendance->check_in && $attendance->check_out)
                                        @php
                                            $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                            $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                            $duration = $checkIn->diff($checkOut);
                                        @endphp
                                        {{ $duration->format('%H jam %I menit') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 text-sm">
                            @if(!empty($attendance->is_virtual))
                                <span class="text-gray-400 text-xs italic">
                                    <i class="fas fa-minus-circle mr-1"></i>Tidak ada catatan
                                </span>
                            @else
                                <a href="{{ route('employee.attendance.show', $attendance->id) }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                    <i class="fas fa-eye"></i>
                                    Detail
                                </a>
                                @if($attendance->check_in && !$attendance->check_out && $attendance->status === 'present')
                                    <a href="{{ route('employee.attendance.check-out-form') }}" class="text-red-600 hover:text-red-800 inline-flex items-center gap-1">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Check Out
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Desktop Table View -->
        <div class="hidden sm:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="far fa-calendar mr-2 text-xs"></i>Tanggal
                            </th>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-clock mr-2 text-xs"></i>Shift
                            </th>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-sign-in-alt mr-2 text-xs"></i>Check In
                            </th>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-sign-out-alt mr-2 text-xs"></i>Check Out
                            </th>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="far fa-clock mr-2 text-xs"></i>Durasi
                            </th>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-info-circle mr-2 text-xs"></i>Status
                            </th>
                            <th class="px-4 lg:px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-cog mr-2 text-xs"></i>Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attendances as $attendance)
                            <tr class="{{ !empty($attendance->is_virtual) ? 'bg-red-50/40 hover:bg-red-50' : 'hover:bg-gray-50' }}">
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $attendanceDate = \Carbon\Carbon::parse($attendance->attendance_date);

                                        // Cek shift override untuk tanggal ini
                                        $shiftOverride = $attendance->worker->shiftOverrides
                                            ->where('override_date', $attendanceDate->format('Y-m-d'))
                                            ->first();

                                        $shift = null;
                                        $shiftSource = '';

                                        if ($shiftOverride && $shiftOverride->shift) {
                                            $shift = $shiftOverride->shift;
                                            $shiftSource = 'override';
                                        } else {
                                            // Cari worker shift yang aktif untuk tanggal ini menggunakan isActiveOnDate
                                            $activeWorkerShift = $attendance->worker->workerShifts
                                                ->first(function($ws) use ($attendanceDate) {
                                                    return $ws->isActiveOnDate($attendanceDate);
                                                });

                                            if ($activeWorkerShift && $activeWorkerShift->shift) {
                                                $shift = $activeWorkerShift->shift;
                                                $shiftSource = 'active';
                                            } elseif ($attendance->worker->shift) {
                                                // Fallback ke shift default
                                                $shift = $attendance->worker->shift;
                                                $shiftSource = 'default';
                                            }
                                        }
                                    @endphp
                                    @if($shift)
                                        <div class="text-gray-900 font-medium">{{ $shift->name }}</div>
                                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</div>
                                        @if($shiftSource === 'override')
                                            <div class="text-xs text-blue-600">
                                                <i class="fas fa-exchange-alt mr-1"></i>Override
                                            </div>
                                        @elseif($shiftSource === 'active')
                                            <div class="text-xs text-green-600">
                                                <i class="fas fa-check mr-1"></i>Aktif
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-600">
                                                <i class="fas fa-clock mr-1"></i>Default
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-gray-400 text-xs">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Belum ada shift
                                        </div>
                                        <div class="text-xs text-red-500">Perlu setting shift</div>
                                    @endif
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm">
                                    @if($attendance->check_in)
                                        <div class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') }}</div>
                                        @if($attendance->is_late && $attendance->late_minutes > 0)
                                            <div class="text-xs text-red-500">
                                                <i class="fas fa-clock mr-1"></i>Terlambat {{ $attendance->late_minutes }} menit
                                            </div>
                                        @elseif($attendance->is_late && $attendance->late_minutes <= 0)
                                            <div class="text-xs text-yellow-500">
                                                <i class="fas fa-clock mr-1"></i>Status terlambat
                                            </div>
                                        @else
                                            <div class="text-xs text-green-500">
                                                <i class="fas fa-check mr-1"></i>Tepat waktu
                                            </div>
                                        @endif
                                        @if($shift)
                                            <div class="text-xs text-gray-400">Target: {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</div>
                                        @endif
                                    @else
                                        <div class="text-gray-400">-</div>
                                        @if($shift)
                                            <div class="text-xs text-gray-500">Target: {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm">
                                    @if($attendance->check_out)
                                        <div class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') }}</div>
                                        @if($shift)
                                            <div class="text-xs text-gray-400">Target: {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</div>
                                        @endif
                                    @elseif($attendance->check_in)
                                        <div class="text-yellow-600">
                                            <i class="fas fa-clock mr-1"></i>Belum check-out
                                        </div>
                                        @if($shift)
                                            <div class="text-xs text-gray-500">Target: {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</div>
                                        @endif
                                    @else
                                        <div class="text-gray-400">-</div>
                                        @if($shift)
                                            <div class="text-xs text-gray-500">Target: {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($attendance->check_in && $attendance->check_out)
                                        @php
                                            $checkIn = \Carbon\Carbon::parse($attendance->check_in);
                                            $checkOut = \Carbon\Carbon::parse($attendance->check_out);
                                            $duration = $checkIn->diff($checkOut);
                                        @endphp
                                        {{ $duration->format('%H jam %I menit') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    @if($attendance->status === 'present')
                                        @if($attendance->is_late)
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Terlambat
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Hadir
                                            </span>
                                        @endif
                                    @elseif($attendance->status === 'late')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Terlambat
                                        </span>
                                    @elseif($attendance->status === 'absent')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>Tidak Hadir
                                        </span>
                                    @elseif($attendance->status === 'sick')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="fas fa-medkit mr-1"></i>Sakit
                                        </span>
                                    @elseif($attendance->status === 'permission')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <i class="fas fa-info-circle mr-1"></i>Izin
                                        </span>
                                    @elseif($attendance->status === 'leave')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            <i class="fas fa-umbrella-beach mr-1"></i>Cuti
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($attendance->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-3">
                                        @if(!empty($attendance->is_virtual))
                                            {{-- Virtual absent: no DB record, no detail page --}}
                                            <span class="text-gray-400 text-xs italic" title="Tidak ada catatan absensi pada hari ini">
                                                <i class="fas fa-minus-circle mr-1"></i>Tidak ada catatan
                                            </span>
                                        @else
                                            <a href="{{ route('employee.attendance.show', $attendance->id) }}"
                                               class="text-blue-600 hover:text-blue-900" title="Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            @if($attendance->check_in && !$attendance->check_out && $attendance->status === 'present')
                                                <a href="{{ route('employee.attendance.check-out-form') }}"
                                                   class="text-red-600 hover:text-red-900"
                                                   title="Check Out">
                                                    <i class="fas fa-sign-out-alt"></i>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                        <p class="text-base font-medium text-gray-500 mb-1">Belum ada data absensi</p>
                                        <p class="text-sm text-gray-400">Data absensi akan muncul di sini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="px-4 lg:px-6 py-4 border-t border-gray-100 bg-gray-50/70">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>

        <!-- Empty state for mobile when no data -->
        @if(!$attendances->count())
            <div class="md:hidden bg-white rounded-xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500">
                <div class="flex flex-col items-center gap-2">
                    <i class="fas fa-inbox text-gray-300 text-4xl"></i>
                    <p class="font-medium text-gray-600">Belum ada data absensi</p>
                    <p class="text-xs text-gray-400">Data absensi akan muncul di sini</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
