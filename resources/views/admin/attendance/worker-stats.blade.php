@extends('layouts.admin')

@section('title', 'Statistik Detail Kehadiran - ' . $worker->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.attendance.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                        <img class="h-16 w-16 rounded-full object-cover"
                             src="{{ asset('storage/' . $worker->photo_url) }}"
                             alt="{{ $worker->name }}">
                    @else
                        <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-xl">
                            {{ substr($worker->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $worker->name }}</h1>
                    <p class="text-sm text-gray-600">{{ $worker->nip ?? '-' }} • {{ $worker->department->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">Statistik Kehadiran Detail</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode Statistik</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="date"
                               name="date_from"
                               value="{{ $dateFrom }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <input type="date"
                               name="date_to"
                               value="{{ $dateTo }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-search mr-2"></i>Terapkan
                </button>
                <a href="{{ route('admin.attendance.worker-stats', $worker->id) }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-download mr-2 text-green-600"></i>
                    Export Laporan
                </h3>
                <p class="text-sm text-gray-600">Unduh laporan statistik kehadiran dalam format PDF atau Excel</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.attendance.stats.export-pdf', ['worker' => $worker->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                    <i class="fas fa-file-pdf mr-2 text-lg"></i>
                    Export PDF
                </a>
                <a href="{{ route('admin.attendance.stats.export-excel', ['worker' => $worker->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                   class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-lg shadow-lg transition duration-200 transform hover:scale-105">
                    <i class="fas fa-file-excel mr-2 text-lg"></i>
                    Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Hari Kerja -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Hari Kerja</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_work_days'] }}</p>
                    <p class="text-xs text-gray-500">Senin - Jumat</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar text-gray-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Hadir -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['total_present'] }}</p>
                    <p class="text-xs text-green-500">{{ $stats['attendance_percentage'] }}% kehadiran</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Tidak Hadir -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Absent</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['total_absent'] }}</p>
                    <p class="text-xs text-red-500">{{ $stats['absence_percentage'] }}% ketidakhadiran</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-red-600"></i>
                </div>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Terlambat</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['late_arrivals'] }}</p>
                    <p class="text-xs text-orange-500">Dari {{ $stats['total_present'] }} hari hadir</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Kehadiran -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Rincian Absensi -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                    Rincian Absensi
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-sign-in-alt mr-2 text-green-500"></i>Check In + Check Out
                    </span>
                    <span class="font-medium text-green-600">{{ $stats['complete_attendance'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-sign-in-alt mr-2 text-yellow-500"></i>Check In Saja
                    </span>
                    <span class="font-medium text-yellow-600">{{ $stats['check_in_only'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-sign-out-alt mr-2 text-orange-500"></i>Check Out Saja
                    </span>
                    <span class="font-medium text-orange-600">{{ $stats['check_out_only'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-2 text-orange-500"></i>Keterlambatan
                    </span>
                    <span class="font-medium text-orange-600">{{ $stats['late_arrivals'] }}</span>
                </div>
            </div>
        </div>

        <!-- Rincian Cuti & Izin -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                    Rincian Cuti & Izin
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-umbrella-beach mr-2 text-blue-500"></i>Cuti
                    </span>
                    <span class="font-medium text-blue-600">{{ $stats['leave_days'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-thermometer-half mr-2 text-red-500"></i>Sakit
                    </span>
                    <span class="font-medium text-red-600">{{ $stats['sick_days'] }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-hand-paper mr-2 text-yellow-500"></i>Izin
                    </span>
                    <span class="font-medium text-yellow-600">{{ $stats['permission_days'] }}</span>
                </div>

                <!-- Chart Simple -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span>Hadir: {{ $stats['attendance_percentage'] }}%</span>
                            <span>Absent: {{ $stats['absence_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['attendance_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Riwayat -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history mr-2 text-gray-600"></i>
                Riwayat Detail ({{ $attendances->count() }} record)
            </h3>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </div>
        </div>

        <!-- Mobile Card Layout -->
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($attendances as $attendance)
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l') }}</div>
                    </div>
                    @switch($attendance->status)
                        @case('present')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Hadir</span>
                            @break
                        @case('absent')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Tidak Hadir</span>
                            @break
                        @case('leave')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Cuti</span>
                            @break
                        @case('sick')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Sakit</span>
                            @break
                        @case('permission')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Izin</span>
                            @break
                        @default
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($attendance->status) }}</span>
                    @endswitch
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-gray-500">Check In:</span>
                        @if($attendance->check_in)
                            <span class="font-medium">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</span>
                            @if($attendance->is_late)
                                <span class="text-red-600">(Terlambat)</span>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-500">Check Out:</span>
                        @if($attendance->check_out)
                            <span class="font-medium">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</span>
                            @if($attendance->is_early_leave)
                                <span class="text-orange-600">(Awal)</span>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </div>
                @if($attendance->notes)
                    <div class="mt-1 text-xs text-gray-500">{{ Str::limit($attendance->notes, 50) }}</div>
                @endif
            </div>
            @empty
            <div class="p-6 text-center">
                <i class="fas fa-calendar-times text-gray-400 text-3xl mb-2"></i>
                <p class="text-gray-500 text-sm">Tidak ada data absensi dalam periode ini</p>
            </div>
            @endforelse
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($attendance->check_in)
                                <div class="flex items-center">
                                    <i class="fas fa-sign-in-alt mr-1 text-green-500"></i>
                                    {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                                    @if($attendance->is_late)
                                        <span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Terlambat</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($attendance->check_out)
                                <div class="flex items-center">
                                    <i class="fas fa-sign-out-alt mr-1 text-blue-500"></i>
                                    {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                                    @if($attendance->is_early_leave)
                                        <span class="ml-2 text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">Pulang Awal</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($attendance->status)
                                @case('present')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Hadir
                                    </span>
                                    @break
                                @case('absent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Tidak Hadir
                                    </span>
                                    @break
                                @case('leave')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-umbrella-beach mr-1"></i>Cuti
                                    </span>
                                    @break
                                @case('sick')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-thermometer-half mr-1"></i>Sakit
                                    </span>
                                    @break
                                @case('permission')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-hand-paper mr-1"></i>Izin
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($attendance->notes)
                                {{ Str::limit($attendance->notes, 50) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-times text-gray-400 text-4xl mb-3"></i>
                                <p class="text-gray-500">Tidak ada data absensi dalam periode ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
