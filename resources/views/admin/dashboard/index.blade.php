{{-- filepath: resources/views/admin/dashboard/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('page-description', 'Selamat datang di SIMPEGRS RSUD Haji Darlan Ismail')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Pegawai -->
        <div class="p-6 text-white transition duration-300 transform shadow-lg bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-blue-100">Total Pegawai</p>
                    <h3 class="text-3xl font-bold">{{ $statistics['total_workers'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs text-blue-100">
                        <i class="mr-1 fas fa-user-check"></i>
                        {{ $statistics['active_workers'] ?? 0 }} Aktif
                    </p>
                </div>
                <div class="p-4 rounded-full bg-white/20">
                    <i class="text-3xl sm:text-4xl fas fa-users"></i>
                </div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="p-6 text-white transition duration-300 transform shadow-lg bg-gradient-to-br from-green-500 to-green-600 rounded-xl hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-green-100">Hadir Hari Ini</p>
                    <h3 class="text-3xl font-bold">{{ $statistics['present_today'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs text-green-100">
                        <i class="mr-1 fas fa-percentage"></i>
                        {{ $statistics['attendance_rate'] ?? 0 }}% Kehadiran
                    </p>
                </div>
                <div class="p-4 rounded-full bg-white/20">
                    <i class="text-3xl sm:text-4xl fas fa-clipboard-check"></i>
                </div>
            </div>
        </div>

        <!-- Cuti Pending -->
        <div class="p-6 text-white transition duration-300 transform shadow-lg bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-yellow-100">Permohonan Cuti</p>
                    <h3 class="text-3xl font-bold">{{ $statistics['pending_leaves'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs text-yellow-100">
                        <i class="mr-1 fas fa-clock"></i>
                        Menunggu Approval
                    </p>
                </div>
                <div class="p-4 rounded-full bg-white/20">
                    <i class="text-3xl sm:text-4xl fas fa-calendar-times"></i>
                </div>
            </div>
        </div>

        <!-- Lembur Pending -->
        <div class="p-6 text-white transition duration-300 transform shadow-lg bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-sm font-medium text-purple-100">Permohonan Lembur</p>
                    <h3 class="text-3xl font-bold">{{ $statistics['pending_overtimes'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs text-purple-100">
                        <i class="mr-1 fas fa-clock"></i>
                        Menunggu Approval
                    </p>
                </div>
                <div class="p-4 rounded-full bg-white/20">
                    <i class="text-3xl sm:text-4xl fas fa-business-time"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        <!-- Attendance Chart -->
        <div class="p-4 sm:p-6 bg-white shadow-lg rounded-xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3">
                <h3 class="text-base sm:text-lg font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-chart-line"></i>
                    Grafik Kehadiran Minggu Ini
                </h3>
                <select class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option>Minggu Ini</option>
                    <option>Bulan Ini</option>
                    <option>Tahun Ini</option>
                </select>
            </div>
            <div class="flex items-center justify-center h-48 sm:h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Department Distribution -->
        <div class="p-4 sm:p-6 bg-white shadow-lg rounded-xl">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <h3 class="text-base sm:text-lg font-bold text-gray-800">
                    <i class="mr-2 text-green-600 fas fa-chart-pie"></i>
                    Distribusi Pegawai per Jabatan
                </h3>
            </div>
            <div class="space-y-4">
                @forelse($positionDistribution ?? [] as $position)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ $position->name }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $position->workers_count }}</span>
                    </div>
                    <div class="w-full h-3 bg-gray-200 rounded-full">
                        <div class="h-3 transition-all duration-500 rounded-full bg-gradient-to-r from-green-500 to-green-600"
                             style="width: {{ ($position->workers_count / ($statistics['total_workers'] ?? 1)) * 100 }}%">
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-gray-500">
                    <i class="mb-3 text-3xl sm:text-4xl fas fa-inbox"></i>
                    <p>Belum ada data</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        <!-- Pengajuan Cuti Terbaru -->
        <div class="p-4 sm:p-6 bg-white shadow-lg rounded-xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-2">
                <h3 class="text-base sm:text-lg font-bold text-gray-800">
                    <i class="mr-2 text-yellow-600 fas fa-calendar-alt"></i>
                    Pengajuan Cuti Terbaru
                </h3>
                <a href="{{ route('admin.leave.index') }}" class="text-sm font-medium text-green-600 hover:text-green-700">
                    Lihat Semua <i class="ml-1 fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentLeaves ?? [] as $leave)
                <div class="flex items-center justify-between p-4 transition duration-200 rounded-lg bg-gray-50 hover:bg-gray-100">
                    <div class="flex items-center space-x-3">
                        <img src="{{ $leave->worker->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($leave->worker->name) }}"
                             alt="{{ $leave->worker->name }}"
                             class="w-10 h-10 border-2 border-green-200 rounded-full">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $leave->worker->name }}</p>
                            <p class="text-xs text-gray-600">{{ $leave->leave_type }} • {{ $leave->total_days }} hari</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $leave->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $leave->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                        <p class="mt-1 text-xs text-gray-500">{{ $leave->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-gray-500">
                    <i class="mb-3 text-3xl sm:text-4xl fas fa-inbox"></i>
                    <p>Belum ada pengajuan cuti</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Ulang Tahun Pegawai -->
        <div class="p-6 bg-white shadow-lg rounded-xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="mr-2 text-pink-600 fas fa-birthday-cake"></i>
                    Ulang Tahun Bulan Ini
                </h3>
            </div>
            <div class="space-y-3">
                @forelse($birthdayWorkers ?? [] as $worker)
                <div class="flex items-center justify-between p-4 rounded-lg bg-gradient-to-r from-pink-50 to-purple-50">
                    <div class="flex items-center space-x-3">
                        <img src="{{ $worker->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($worker->name) }}"
                             alt="{{ $worker->name }}"
                             class="w-10 h-10 border-2 border-pink-200 rounded-full">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $worker->name }}</p>
                            <p class="text-xs text-gray-600">{{ $worker->position->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-pink-600">{{ $worker->birth_date->format('d M') }}</p>
                        <p class="text-xs text-gray-500">{{ $worker->birth_date->age }} tahun</p>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-gray-500">
                    <i class="mb-3 text-3xl sm:text-4xl fas fa-inbox"></i>
                    <p>Tidak ada ulang tahun bulan ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="p-6 bg-white shadow-lg rounded-xl">
        <h3 class="mb-6 text-lg font-bold text-gray-800">
            <i class="mr-2 text-yellow-500 fas fa-bolt"></i>
            Quick Actions
        </h3>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
            <a href="{{ route('admin.workers.create') }}" class="flex flex-col items-center p-4 transition duration-300 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 hover:shadow-md group">
                <div class="p-3 text-white transition duration-300 bg-blue-500 rounded-full group-hover:scale-110">
                    <i class="text-xl fas fa-user-plus"></i>
                </div>
                <span class="mt-3 text-sm font-medium text-center text-gray-700">Tambah Pegawai</span>
            </a>

            <a href="{{ route('admin.absents.index') }}" class="flex flex-col items-center p-4 transition duration-300 rounded-lg bg-gradient-to-br from-green-50 to-green-100 hover:shadow-md group">
                <div class="p-3 text-white transition duration-300 bg-green-500 rounded-full group-hover:scale-110">
                    <i class="text-xl fas fa-clipboard-check"></i>
                </div>
                <span class="mt-3 text-sm font-medium text-center text-gray-700">Kelola Absensi</span>
            </a>

            <a href="{{ route('admin.leave.index') }}" class="flex flex-col items-center p-4 transition duration-300 rounded-lg bg-gradient-to-br from-yellow-50 to-yellow-100 hover:shadow-md group">
                <div class="p-3 text-white transition duration-300 bg-yellow-500 rounded-full group-hover:scale-110">
                    <i class="text-xl fas fa-calendar-check"></i>
                </div>
                <span class="mt-3 text-sm font-medium text-center text-gray-700">Approval Cuti</span>
            </a>

            <a href="{{ route('admin.overtime.index') }}" class="flex flex-col items-center p-4 transition duration-300 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 hover:shadow-md group">
                <div class="p-3 text-white transition duration-300 bg-purple-500 rounded-full group-hover:scale-110">
                    <i class="text-xl fas fa-business-time"></i>
                </div>
                <span class="mt-3 text-sm font-medium text-center text-gray-700">Approval Lembur</span>
            </a>

            <a href="{{ route('admin.workers.index') }}" class="flex flex-col items-center p-4 transition duration-300 rounded-lg bg-gradient-to-br from-red-50 to-red-100 hover:shadow-md group">
                <div class="p-3 text-white transition duration-300 bg-red-500 rounded-full group-hover:scale-110">
                    <i class="text-xl fas fa-users"></i>
                </div>
                <span class="mt-3 text-sm font-medium text-center text-gray-700">Data Pegawai</span>
            </a>

            <a href="{{ route('admin.settings.shifts.index') }}" class="flex flex-col items-center p-4 transition duration-300 rounded-lg bg-gradient-to-br from-indigo-50 to-indigo-100 hover:shadow-md group">
                <div class="p-3 text-white transition duration-300 bg-indigo-500 rounded-full group-hover:scale-110">
                    <i class="text-xl fas fa-cog"></i>
                </div>
                <span class="mt-3 text-sm font-medium text-center text-gray-700">Pengaturan</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Attendance Chart
    const ctx = document.getElementById('attendanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($attendanceChartLabels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!},
                datasets: [{
                    label: 'Hadir',
                    data: {!! json_encode($attendanceChartData ?? [45, 52, 48, 50, 47, 30, 28]) !!},
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
