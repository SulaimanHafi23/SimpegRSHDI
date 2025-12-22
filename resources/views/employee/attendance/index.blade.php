@extends('layouts.employee')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    <i class="fas fa-user-check text-green-600 mr-2"></i>
                    Riwayat Absensi
                </h1>
                <p class="text-gray-600 mt-2">Lihat riwayat kehadiran Anda</p>
            </div>
            <a href="{{ route('employee.attendance.export-pdf', request()->all()) }}" 
               class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-150"
               target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>
                <span class="hidden sm:inline">Export PDF</span>
                <span class="sm:hidden">PDF</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Hari</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total_days'] }}</p>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <i class="fas fa-calendar text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $summary['present'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $summary['late'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Tidak Hadir</p>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['absent'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-filter mr-2 text-green-600"></i>
                Pencarian & Filter
            </h3>
            <button @click="showFilters = !showFilters" class="text-gray-600 hover:text-gray-800">
                <i class="fas" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('employee.attendance.index') }}" x-show="showFilters" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>
                        Cari
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari tanggal, status, lokasi..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           x-data="{ value: '{{ $filters['search'] ?? '' }}' }"
                           x-model="value">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="far fa-calendar mr-1"></i>
                        Dari Tanggal
                    </label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="far fa-calendar-check mr-1"></i>
                        Sampai Tanggal
                    </label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-filter mr-1"></i>
                        Status Kehadiran
                    </label>
                    <select name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ ($filters['status'] ?? '') == 'Hadir' ? 'selected' : '' }}>
                            <i class="fas fa-check-circle"></i> Hadir
                        </option>
                        <option value="Terlambat" {{ ($filters['status'] ?? '') == 'Terlambat' ? 'selected' : '' }}>
                            <i class="fas fa-clock"></i> Terlambat
                        </option>
                        <option value="Tidak Hadir" {{ ($filters['status'] ?? '') == 'Tidak Hadir' ? 'selected' : '' }}>
                            <i class="fas fa-times-circle"></i> Tidak Hadir
                        </option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-list-ol mr-1"></i>
                        Tampilkan
                    </label>
                    <select name="per_page" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="10" {{ ($filters['per_page'] ?? 15) == 10 ? 'selected' : '' }}>10 per halaman</option>
                        <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15 per halaman</option>
                        <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25 per halaman</option>
                        <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50 per halaman</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>
                        Terapkan
                    </button>
                    <a href="{{ route('employee.attendance.index') }}" 
                       class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i>
                        Reset
                    </a>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(!empty($filters['search']) || !empty($filters['status']))
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-sm text-gray-600">Filter Aktif:</span>
                @if(!empty($filters['search']))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-search mr-1"></i>
                        "{{ $filters['search'] }}"
                        <a href="{{ route('employee.attendance.index', array_merge(request()->except('search'))) }}" 
                           class="ml-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(!empty($filters['status']))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
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

    <!-- Check In Button -->
    <div class="mb-6">
        <a href="{{ route('employee.attendance.check-in-form') }}" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Check In
        </a>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="far fa-calendar mr-1"></i>Tanggal
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-sign-in-alt mr-1"></i>Check In
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-sign-out-alt mr-1"></i>Check Out
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="far fa-clock mr-1"></i>Durasi
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-info-circle mr-1"></i>Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-cog mr-1"></i>Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->status === 'present')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Hadir</span>
                                @elseif($attendance->status === 'late')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Terlambat</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Hadir</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="{{ route('employee.attendance.show', $attendance->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($attendance->check_in && !$attendance->check_out && \Carbon\Carbon::parse($attendance->attendance_date)->isToday())
                                        <form action="{{ route('employee.attendance.check-out', $attendance->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Check Out"
                                                    onclick="return confirm('Apakah Anda yakin ingin check-out?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                    <p class="text-lg font-medium text-gray-500 mb-2">Belum ada data absensi</p>
                                    <p class="text-sm text-gray-400">Data absensi akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
