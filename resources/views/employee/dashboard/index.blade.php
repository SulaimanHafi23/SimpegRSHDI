{{-- filepath: resources/views/employee/dashboard/index.blade.php --}}
@extends('layouts.employee')

@section('title', 'Dashboard Pegawai')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="space-y-4 pb-6" x-data="{
    period: 'month',
    showStats: true
}"
    <!-- Welcome Card dengan Info Pegawai -->
    <div class="bg-gradient-to-br from-green-600 via-green-700 to-green-800 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-300 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-yellow-400 rounded-full -ml-12 -mb-12"></div>
        </div>

        <div class="flex items-center space-x-4 relative z-10">
            <img src="{{ $worker->photo_url ? Storage::url($worker->photo_url) : 'https://ui-avatars.com/api/?name=' . urlencode($worker->name) . '&background=random' }}"
                 alt="Profile"
                 class="h-20 w-20 rounded-full border-4 border-yellow-300 shadow-lg object-cover"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($worker->name) }}&background=random'">
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-1">{{ $worker->name }}</h2>
                <p class="text-yellow-100 text-sm mb-2">
                    <i class="fas fa-briefcase mr-1"></i>
                    {{ $worker->department->name ?? 'Pegawai' }}
                </p>
                <div class="flex items-center space-x-3 text-xs">
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                        <i class="fas fa-id-badge mr-1"></i>
                        {{ $worker->nip }}
                    </span>
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>
                        Bergabung {{ \Carbon\Carbon::parse($worker->hire_date)->format('Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Checkout Alert -->
    @if($pendingCheckout)
    <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="bg-white/20 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold mb-2">
                    <i class="fas fa-sign-out-alt mr-2"></i>Belum Checkout!
                </h3>
                <p class="text-white/90 mb-3">
                    Anda belum melakukan checkout untuk shift <strong>{{ $pendingCheckout['shift_name'] }}</strong>
                    pada tanggal <strong>{{ \Carbon\Carbon::parse($pendingCheckout['attendance_date'])->format('d M Y') }}</strong>.
                </p>
                <div class="flex items-center space-x-4 text-sm mb-4">
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                        <i class="fas fa-clock mr-1"></i>Check-in: {{ $pendingCheckout['check_in_time'] }}
                    </span>
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                        <i class="fas fa-hourglass-end mr-1"></i>Shift berakhir: {{ \Carbon\Carbon::parse($pendingCheckout['shift_end_time'])->format('H:i') }}
                    </span>
                    <span class="bg-white/20 px-3 py-1 rounded-full font-semibold">
                        <i class="fas fa-calendar-times mr-1"></i>{{ $pendingCheckout['formatted_late'] }}
                    </span>
                </div>
                <a href="{{ route('employee.attendance.index') }}"
                   class="inline-flex items-center px-5 py-2 bg-white text-red-600 font-semibold rounded-lg shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Checkout Sekarang
                </a>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($pendingCheckout))
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 flex items-start gap-3 shadow">
        <div class="mt-1 text-yellow-500">
            <i class="fas fa-bell text-lg"></i>
        </div>
        <div class="flex-1">
            <p class="font-semibold">Anda belum check-out untuk shift {{ $pendingCheckout['shift_name'] }} ({{ \Carbon\Carbon::parse($pendingCheckout['attendance_date'])->format('d M Y') }})</p>
            <p class="text-sm mt-1">Shift berakhir pada {{ \Carbon\Carbon::parse($pendingCheckout['shift_end_time'])->format('d M Y H:i') }} ({{ $pendingCheckout['formatted_late'] }}). Silakan selesaikan check-out sekarang.</p>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('employee.attendance.index') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700">
                    <i class="fas fa-sign-out-alt mr-2"></i> Ke halaman absensi
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Action - Check In Button -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    <i class="fas fa-clock mr-2"></i>Absensi Hari Ini
                </h3>
                <p class="text-blue-100 text-sm mb-4">{{ now()->format('l, d F Y') }}</p>
                <a href="{{ route('employee.attendance.check-in-form') }}"
                   class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg shadow hover:shadow-lg transition duration-300 transform hover:scale-105">
                    <i class="fas fa-user-check mr-2"></i>
                    Check In Sekarang
                </a>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-fingerprint"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" x-show="showStats" x-transition>
        <div class="bg-white rounded-xl shadow-lg p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Hadir</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ $attendanceSummary['present'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Bulan ini</p>
                    <div class="mt-2">
                        <span class="text-xs text-green-600 font-medium">
                            <i class="fas fa-arrow-up"></i>
                            {{ number_format($attendanceSummary['percentage'] ?? 0, 1) }}%
                        </span>
                    </div>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Terlambat</p>
                    <h3 class="text-3xl font-bold text-yellow-600">{{ $attendanceSummary['late'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Bulan ini</p>
                    @if(($attendanceSummary['late'] ?? 0) > 0)
                    <div class="mt-2">
                        <span class="text-xs text-yellow-600 font-medium">
                            <i class="fas fa-exclamation-triangle"></i> Perhatian
                        </span>
                    </div>
                    @endif
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Tidak Hadir</p>
                    <h3 class="text-3xl font-bold text-red-600">{{ $attendanceSummary['absent'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Bulan ini</p>
                    @if(($attendanceSummary['absent'] ?? 0) > 0)
                    <div class="mt-2">
                        <span class="text-xs text-red-600 font-medium">
                            <i class="fas fa-times-circle"></i> Review
                        </span>
                    </div>
                    @endif
                </div>
                <div class="bg-red-100 p-4 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5 stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Lembur</p>
                    <h3 class="text-3xl font-bold text-purple-600">{{ $overtimeSummary['total_requests'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">{{ number_format($overtimeSummary['total_hours'] ?? 0, 1) }} jam</p>
                    <div class="mt-2">
                        <span class="text-xs text-purple-600 font-medium">
                            <i class="fas fa-hourglass-half"></i> Total bulan ini
                        </span>
                    </div>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-business-time text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balance/Quota Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-calendar-check text-green-600 mr-2"></i>
                Sisa Kuota Cuti Tahun {{ now()->year }}
            </h3>
            <a href="{{ route('employee.leaves.index') }}" class="text-sm text-green-600 hover:text-green-700 font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($leaveBalances as $balance)
                @php
                    $percentage = $balance['quota'] > 0 ? ($balance['remaining'] / $balance['quota']) * 100 : 0;
                    $colorClass = $percentage > 50 ? 'green' : ($percentage > 20 ? 'yellow' : 'red');
                @endphp
                <div class="bg-gradient-to-br from-{{ $balance['color'] }}-50 to-{{ $balance['color'] }}-100 rounded-lg p-4 border border-{{ $balance['color'] }}-200">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-medium text-{{ $balance['color'] }}-800">{{ $balance['leave_type'] }}</p>
                            <p class="text-xs text-{{ $balance['color'] }}-600 mt-1">Tahun {{ now()->year }}</p>
                        </div>
                        <div class="bg-{{ $balance['color'] }}-200 p-2 rounded-full">
                            <i class="fas fa-umbrella-beach text-{{ $balance['color'] }}-700"></i>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-2xl font-bold text-{{ $balance['color'] }}-900">{{ $balance['remaining'] }}</p>
                                <p class="text-xs text-{{ $balance['color'] }}-600">hari tersisa</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-{{ $balance['color'] }}-700">{{ $balance['used'] }}/{{ $balance['quota'] }}</p>
                                <p class="text-xs text-{{ $balance['color'] }}-600">terpakai</p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-{{ $balance['color'] }}-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-{{ $balance['color'] }}-600 h-full rounded-full transition-all duration-300"
                                 style="width: {{ $percentage }}%"></div>
                        </div>

                        <p class="text-xs text-{{ $balance['color'] }}-700 text-center">
                            {{ number_format($percentage, 0) }}% tersisa
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Belum ada data kuota cuti</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Attendance Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Tren Kehadiran 7 Hari
                </h3>
                <span class="text-xs text-gray-500">
                    <i class="far fa-calendar"></i> Mingguan
                </span>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Leave Summary Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                    Status Pengajuan Cuti
                </h3>
                <span class="text-xs text-gray-500">
                    <i class="far fa-calendar-check"></i> Total: {{ $leaveSummary['total'] ?? 0 }}
                </span>
            </div>
            <div class="chart-container">
                <canvas id="leaveChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Menu Cepat -->
    <div class="bg-white rounded-xl shadow-lg p-5">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
            Menu Cepat
        </h3>
        <div class="grid grid-cols-3 gap-3">
            <a href="{{ route('employee.leaves.create') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-blue-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-calendar-plus text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Ajukan Cuti</span>
            </a>

            <a href="{{ route('employee.overtimes.create') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-purple-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Ajukan Lembur</span>
            </a>

            <a href="{{ route('employee.documents.index') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-green-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Dokumen</span>
            </a>

            <a href="{{ route('employee.attendance.index') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-yellow-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-history text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Riwayat Absen</span>
            </a>

            <a href="{{ route('employee.leaves.index') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-red-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-calendar-times text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Riwayat Cuti</span>
            </a>

            <a href="{{ route('employee.profile.show') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-indigo-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-user-edit text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Edit Profil</span>
            </a>
        </div>
    </div>

    <!-- Recent Activities & Upcoming Leaves -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-history text-indigo-600 mr-2"></i>
                    Aktivitas Terbaru
                </h3>
            </div>
            <div class="space-y-3">
                @forelse($recentActivities as $activity)
                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex-shrink-0 mt-1">
                        @if($activity['type'] === 'attendance')
                            <div class="bg-green-100 text-green-600 p-2 rounded-full">
                                <i class="fas fa-user-check text-sm"></i>
                            </div>
                        @elseif($activity['type'] === 'leave')
                            <div class="bg-blue-100 text-blue-600 p-2 rounded-full">
                                <i class="fas fa-calendar-times text-sm"></i>
                            </div>
                        @elseif($activity['type'] === 'overtime')
                            <div class="bg-purple-100 text-purple-600 p-2 rounded-full">
                                <i class="fas fa-clock text-sm"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ $activity['description'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="far fa-clock mr-1"></i>
                            {{ $activity['time'] }}
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $activity['status'] === 'success' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $activity['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $activity['status'] === 'late' ? 'bg-orange-100 text-orange-800' : '' }}">
                        @if($activity['status'] === 'success')
                            <i class="fas fa-check-circle mr-1"></i> OK
                        @elseif($activity['status'] === 'pending')
                            <i class="fas fa-hourglass-half mr-1"></i> Pending
                        @elseif($activity['status'] === 'late')
                            <i class="fas fa-exclamation-triangle mr-1"></i> Terlambat
                        @endif
                    </span>
                </div>
                @empty
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>Belum ada aktivitas</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Leaves -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-calendar-week text-orange-600 mr-2"></i>
                    Cuti Yang Akan Datang
                </h3>
                <a href="{{ route('employee.leaves.index') }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($upcomingLeaves as $leave)
                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg border border-orange-100">
                    <div class="flex items-center space-x-3">
                        <div class="bg-orange-500 text-white p-2 rounded-lg text-center min-w-[50px]">
                            <div class="text-lg font-bold">{{ \Carbon\Carbon::parse($leave->start_date)->format('d') }}</div>
                            <div class="text-xs">{{ \Carbon\Carbon::parse($leave->start_date)->format('M') }}</div>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">
                                {{ $leave->leaveType->name ?? 'Cuti' }}
                            </p>
                            <p class="text-xs text-gray-600">
                                <i class="far fa-calendar mr-1"></i>
                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} -
                                {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::now()) }} hari lagi
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i> Disetujui
                    </span>
                </div>
                @empty
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-calendar-check text-4xl mb-3"></i>
                    <p>Tidak ada cuti yang akan datang</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Info Penting -->
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 rounded-lg p-5">
        <div class="flex items-start space-x-3">
            <div class="bg-yellow-500 text-white p-2 rounded-full">
                <i class="fas fa-bullhorn text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-gray-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pengumuman Penting
                </h4>
                <p class="text-sm text-gray-700 leading-relaxed">
                    Reminder: Pastikan untuk melakukan absensi tepat waktu setiap hari.
                    Jika ada kendala, segera hubungi bagian HRD RSUD Haji Darlan Ismail.
                </p>
                <p class="text-xs text-gray-600 mt-2">
                    <i class="far fa-calendar mr-1"></i>
                    Diperbarui: {{ \Carbon\Carbon::now()->format('d M Y') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Trend Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        const attendanceData = @json($attendanceChart);

        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceData.labels,
                datasets: [{
                    label: 'Kehadiran',
                    data: attendanceData.data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Hadir: ' + context.parsed.y + ' kali';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Leave Summary Doughnut Chart
    const leaveCtx = document.getElementById('leaveChart');
    if (leaveCtx) {
        const leaveSummary = @json($leaveSummary);

        new Chart(leaveCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Disetujui', 'Ditolak'],
                datasets: [{
                    data: [
                        leaveSummary.pending || 0,
                        leaveSummary.approved || 0,
                        leaveSummary.rejected || 0
                    ],
                    backgroundColor: [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(251, 191, 36)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 13
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

@endsection
