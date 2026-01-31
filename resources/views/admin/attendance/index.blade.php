@extends('layouts.admin')

@section('title', 'Manajemen Absensi')

@section('content')
<div x-data="{ showFilters: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-user-check mr-3 text-blue-600"></i>
                Manajemen Absensi
            </h1>
            <p cl\ass="mt-1 text-sm text-gray-600">Kelola data absensi pegawai dan monitoring kehadiran</p>
            <div id="real-time-clock" class="mt-2 text-sm text-blue-600 font-semibold"></div>
        </div>
        <div class="flex gap-2">
            <!-- <a href="{{ route('admin.attendance.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Tambah Absensi
            </a> -->
            <a href="{{ route('admin.attendance.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-file-excel mr-2"></i>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <button @click="showFilters = !showFilters" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <i class="fas fa-filter text-blue-600"></i>
                <span class="font-semibold text-gray-900">Filter & Pencarian</span>
            </div>
            <i class="fas fa-chevron-down transform transition-transform" :class="{ 'rotate-180': showFilters }"></i>
        </button>

        <div x-show="showFilters" x-collapse class="border-t border-gray-200">
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <input type="text" name="search" value="{{ $historyFilters['search'] ?? '' }}" 
                               placeholder="Nama, NIP, Email..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                        <select name="worker_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Pegawai</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ ($historyFilters['worker_id'] ?? '') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }} ({{ $worker->nip }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="present" {{ ($historyFilters['status'] ?? '') == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ ($historyFilters['status'] ?? '') == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ ($historyFilters['status'] ?? '') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="sick" {{ ($historyFilters['status'] ?? '') == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="permission" {{ ($historyFilters['status'] ?? '') == 'permission' ? 'selected' : '' }}>Izin</option>
                            <option value="leave" {{ ($historyFilters['status'] ?? '') == 'leave' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Halaman</label>
                        <select name="per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="15" {{ ($historyFilters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ ($historyFilters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($historyFilters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($historyFilters['per_page'] ?? 15) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                        <input type="date" name="date_from" value="{{ $historyFilters['date_from'] ?? '' }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                        <input type="date" name="date_to" value="{{ $historyFilters['date_to'] ?? '' }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>
                        Terapkan
                    </button>
                    <a href="{{ route('admin.attendance.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                        <i class="fas fa-redo mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        @php
            $todayStats = [
                'total_workers' => $workersWithAttendance->count(),
                'checked_in' => $workersWithAttendance->whereNotNull('check_in_time')->count(),
                'not_checked_in' => $workersWithAttendance->where('attendance_status', 'not_checked_in')->count(),
                'late' => $workersWithAttendance->where('is_late', true)->count(),
                'on_leave' => $workersWithAttendance->whereIn('attendance_status', ['leave', 'sick', 'permission'])->count(),
            ];
        @endphp
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Total Pegawai</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayStats['total_workers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Sudah Masuk</p>
                    <p class="text-2xl font-bold text-green-600">{{ $todayStats['checked_in'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-red-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Belum Masuk</p>
                    <p class="text-2xl font-bold text-red-600">{{ $todayStats['not_checked_in'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $todayStats['late'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-day text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Cuti/Izin</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $todayStats['on_leave'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle View -->
    <div class="bg-white rounded-lg shadow-md mb-6 p-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Tampilan Data</h3>
            <div class="flex space-x-2">
                <button id="btn-today-view" class="px-4 py-2 bg-blue-600 text-white rounded-lg transition duration-200 hover:bg-blue-700">
                    Absensi Hari Ini
                </button>
                <button id="btn-history-view" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg transition duration-200 hover:bg-gray-300">
                    Riwayat Absensi
                </button>
            </div>
        </div>
    </div>
    <!-- Table for Today's Attendance -->
    <div id="today-view" class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Absensi Hari Ini - {{ now()->isoFormat('dddd, D MMMM Y') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jadwal Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status & Keterangan</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($workersWithAttendance as $worker)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ asset('storage/' . $worker->photo_url) }}"
                                             alt="{{ $worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($worker->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $worker->nip ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $worker->department->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                // Cek shift override hari ini terlebih dahulu
                                $todayShift = $worker->workerShifts->where('date', now()->format('Y-m-d'))->first();
                                // Kalau tidak ada override, gunakan shift default worker atau shift aktif
                                $shift = null;
                                if ($todayShift && $todayShift->shift) {
                                    $shift = $todayShift->shift;
                                } else {
                                    // Cari shift aktif dari workerShifts
                                    $activeWorkerShift = $worker->workerShifts
                                        ->where('is_active', true)
                                        ->where('effective_from', '<=', now()->format('Y-m-d'))
                                        ->filter(function($ws) {
                                            return is_null($ws->effective_until) || $ws->effective_until >= now()->format('Y-m-d');
                                        })
                                        ->first();
                                    
                                    if ($activeWorkerShift && $activeWorkerShift->shift) {
                                        $shift = $activeWorkerShift->shift;
                                    } elseif ($worker->shift) {
                                        // Fallback ke shift default worker
                                        $shift = $worker->shift;
                                    }
                                }
                            @endphp
                            @if($shift)
                                <div class="text-sm text-gray-900 font-medium">{{ $shift->name }}</div>
                                <div class="text-xs text-gray-500">{{ $shift->start_time }} - {{ $shift->end_time }}</div>
                                @if($todayShift)
                                    <div class="text-xs text-blue-600">
                                        <i class="fas fa-exchange-alt mr-1"></i>Shift Override
                                    </div>
                                @elseif($activeWorkerShift)
                                    <div class="text-xs text-green-600">
                                        <i class="fas fa-check mr-1"></i>Shift Aktif
                                    </div>
                                @else
                                    <div class="text-xs text-gray-600">
                                        <i class="fas fa-clock mr-1"></i>Shift Default
                                    </div>
                                @endif
                            @else
                                <div class="text-sm text-gray-400">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Belum ada jadwal shift
                                </div>
                                <div class="text-xs text-red-500">Perlu setting shift</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($worker->check_in_time)
                                <div class="text-sm text-gray-900 font-medium">{{ $worker->check_in_time->format('H:i:s') }}</div>
                                @if($worker->is_late)
                                    <div class="text-xs text-red-500">
                                        <i class="fas fa-clock mr-1"></i>Terlambat {{ $worker->late_minutes }} menit
                                    </div>
                                @else
                                    <div class="text-xs text-green-500">
                                        <i class="fas fa-check mr-1"></i>Tepat waktu
                                    </div>
                                @endif
                                @if($shift)
                                    <div class="text-xs text-gray-400">Target: {{ $shift->start_time }}</div>
                                @endif
                            @else
                                <div class="text-sm text-gray-400">Belum check-in</div>
                                @if($shift)
                                    <div class="text-xs text-gray-500">Target: {{ $shift->start_time }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($worker->check_out_time)
                                <div class="text-sm text-gray-900 font-medium">{{ $worker->check_out_time->format('H:i:s') }}</div>
                                @if($worker->is_early_leave && $worker->early_leave_minutes > 0)
                                    <div class="text-xs text-orange-600">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Pulang awal {{ $worker->early_leave_minutes }} menit
                                    </div>
                                @elseif($shift)
                                    <div class="text-xs text-green-500">
                                        <i class="fas fa-check mr-1"></i>Sesuai jadwal
                                    </div>
                                @endif
                                @if($shift)
                                    <div class="text-xs text-gray-400">Target: {{ $shift->end_time }}</div>
                                @endif
                            @elseif($worker->check_in_time)
                                <div class="text-sm text-yellow-600">
                                    <i class="fas fa-clock mr-1"></i>Belum check-out
                                </div>
                                @if($shift)
                                    <div class="text-xs text-gray-500">Target: {{ $shift->end_time }}</div>
                                @endif
                            @else
                                <div class="text-sm text-gray-400">-</div>
                                @if($shift)
                                    <div class="text-xs text-gray-500">Target: {{ $shift->end_time }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusConfig = [
                                    'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir', 'icon' => 'fa-check'],
                                    'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat', 'icon' => 'fa-clock'],
                                    'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir', 'icon' => 'fa-times'],
                                    'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit', 'icon' => 'fa-medkit'],
                                    'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin', 'icon' => 'fa-info-circle'],
                                    'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti', 'icon' => 'fa-umbrella-beach'],
                                    'not_checked_in' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Belum Absen', 'icon' => 'fa-clock'],
                                ];
                                $status = $statusConfig[$worker->attendance_status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Unknown', 'icon' => 'fa-question'];
                            @endphp
                            <div class="space-y-2">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                    <i class="fas {{ $status['icon'] }} mr-1"></i>
                                    {{ $status['label'] }}
                                </span>
                                
                                @if($worker->today_attendance)
                                    @if($worker->today_attendance->notes)
                                        <div class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-sticky-note mr-1 text-gray-400"></i>
                                            {{ Str::limit($worker->today_attendance->notes, 50) }}
                                        </div>
                                    @endif
                                    
                                    @if($worker->is_late && $worker->late_minutes > 0)
                                        <div class="text-xs text-red-600">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Keterlambatan: {{ $worker->late_minutes }} menit
                                        </div>
                                    @endif
                                    
                                    @if($worker->today_attendance->location)
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $worker->today_attendance->location->name }}
                                        </div>
                                    @endif
                                @elseif($worker->attendance_status == 'not_checked_in')
                                    <div class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Belum melakukan absensi hari ini
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                @if($worker->today_attendance)
                                    <a href="{{ route('admin.attendance.show', $worker->today_attendance->id) }}"
                                       class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                        <i class="fas fa-eye w-4 h-4"></i>
                                    </a>
                                    @if(!$worker->check_out_time)
                                        <button onclick="checkOutWorker('{{ $worker->today_attendance->id }}', '{{ $worker->name }}')"
                                                class="p-2 text-orange-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-colors" title="Check Out">
                                            <i class="fas fa-sign-out-alt w-4 h-4"></i>
                                        </button>
                                    @endif
                                @else
                                    <button onclick="checkInWorker('{{ $worker->id }}', '{{ $worker->name }}')"
                                            class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-colors" title="Check In">
                                        <i class="fas fa-sign-in-alt w-4 h-4"></i>
                                    </button>
                                @endif
                                <!-- Tombol Detail Statistik -->
                                <a href="{{ route('admin.attendance.worker-stats', $worker->id) }}"
                                   class="p-2 text-purple-600 hover:text-purple-900 hover:bg-purple-50 rounded-lg transition-colors" title="Detail Statistik">
                                    <i class="fas fa-chart-bar w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada data pegawai</p>
                                <p class="text-sm">Belum ada pegawai yang terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table for Attendance History -->
    <div id="history-view" class="bg-white rounded-lg shadow-md overflow-hidden hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Pegawai</th>
                        <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                        <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jam Masuk</th>
                        <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="hidden xl:table-cell px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($attendance->worker->photo_url && Storage::disk('public')->exists($attendance->worker->photo_url))
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ asset('storage/' . $attendance->worker->photo_url) }}"
                                             alt="{{ $attendance->worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($attendance->worker->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $attendance->worker->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attendance->worker->nip ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 md:hidden">{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell">
                            <div class="text-sm text-gray-900">{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $attendance->attendance_date?->isoFormat('dddd') ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <div class="text-sm text-gray-900">{{ $attendance->check_in?->format('H:i') ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <div class="text-sm text-gray-900">{{ $attendance->check_out?->format('H:i') ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusConfig = [
                                    'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir', 'icon' => 'fa-check'],
                                    'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat', 'icon' => 'fa-clock'],
                                    'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir', 'icon' => 'fa-times'],
                                    'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit', 'icon' => 'fa-medkit'],
                                    'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin', 'icon' => 'fa-info-circle'],
                                    'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti', 'icon' => 'fa-umbrella-beach'],
                                ];
                                $config = $statusConfig[$attendance->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($attendance->status), 'icon' => 'fa-question'];
                            @endphp
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                <i class="fas {{ $config['icon'] }} mr-1"></i>
                                {{ $config['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 hidden xl:table-cell">
                            <div class="text-sm text-gray-500">{{ $attendance->location->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}"
                                   class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                    <i class="fas fa-eye w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.attendance.edit', $attendance->id) }}"
                                   class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-edit w-4 h-4"></i>
                                </a>
                                <!-- Tombol Detail Statistik -->
                                <a href="{{ route('admin.attendance.worker-stats', $attendance->worker_id) }}"
                                   class="p-2 text-purple-600 hover:text-purple-900 hover:bg-purple-50 rounded-lg transition-colors" title="Detail Statistik">
                                    <i class="fas fa-chart-bar w-4 h-4"></i>
                                </a>
                                <form action="{{ route('admin.attendance.destroy', $attendance->id) }}"
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Yakin ingin menghapus data absensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <i class="fas fa-trash w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada data absensi</p>
                                <p class="text-sm">Silakan tambah data absensi atau sesuaikan filter pencarian</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination for History -->
        @if($attendances->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $attendances->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Toggle between today view and history view
    document.addEventListener('DOMContentLoaded', function() {
        const btnTodayView = document.getElementById('btn-today-view');
        const btnHistoryView = document.getElementById('btn-history-view');
        const todayView = document.getElementById('today-view');
        const historyView = document.getElementById('history-view');

        btnTodayView.addEventListener('click', function() {
            // Show today view
            todayView.classList.remove('hidden');
            historyView.classList.add('hidden');
            
            // Update button styles
            btnTodayView.classList.remove('bg-gray-200', 'text-gray-700');
            btnTodayView.classList.add('bg-blue-600', 'text-white');
            btnHistoryView.classList.remove('bg-blue-600', 'text-white');
            btnHistoryView.classList.add('bg-gray-200', 'text-gray-700');
        });

        btnHistoryView.addEventListener('click', function() {
            // Show history view
            todayView.classList.add('hidden');
            historyView.classList.remove('hidden');
            
            // Update button styles
            btnHistoryView.classList.remove('bg-gray-200', 'text-gray-700');
            btnHistoryView.classList.add('bg-blue-600', 'text-white');
            btnTodayView.classList.remove('bg-blue-600', 'text-white');
            btnTodayView.classList.add('bg-gray-200', 'text-gray-700');
        });
    });

    // Check in worker function
    function checkInWorker(workerId, workerName) {
        if (confirm(`Apakah Anda yakin ingin check-in ${workerName}?`)) {
            // Redirect to check-in form or handle via AJAX
            window.location.href = `{{ route('admin.attendance.create') }}?worker_id=${workerId}`;
        }
    }

    // Check out worker function
    function checkOutWorker(attendanceId, workerName) {
        if (confirm(`Apakah Anda yakin ingin check-out ${workerName}?`)) {
            // Create and submit form for check-out
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin/attendance') }}/${attendanceId}/check-out`;
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add method PUT
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
            
            // Add location (you might want to get this from GPS or default location)
            const locationInput = document.createElement('input');
            locationInput.type = 'hidden';
            locationInput.name = 'location_id';
            locationInput.value = '{{ $locations->first()->id ?? "" }}'; // Default location
            form.appendChild(locationInput);
            
            // Add coordinates (you might want to get this from GPS)
            const latInput = document.createElement('input');
            latInput.type = 'hidden';
            latInput.name = 'latitude';
            latInput.value = '0'; // Default or get from GPS
            form.appendChild(latInput);
            
            const lngInput = document.createElement('input');
            lngInput.type = 'hidden';
            lngInput.name = 'longitude';
            lngInput.value = '0'; // Default or get from GPS
            form.appendChild(lngInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto refresh every 5 minutes for real-time data (only for today view)
    setInterval(function() {
        if (!document.getElementById('today-view').classList.contains('hidden')) {
            window.location.reload();
        }
    }, 300000);

    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID');
        const dateString = now.toLocaleDateString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Update clock if element exists
        const clockElement = document.getElementById('real-time-clock');
        if (clockElement) {
            clockElement.innerHTML = `<i class="fas fa-clock mr-2"></i>${timeString} - ${dateString}`;
        }
    }

    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>
@endpush
@endsection
