@extends('layouts.admin')

@section('title', 'HR Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">HR Dashboard</h1>
            <p class="text-gray-600 mt-1">Selamat datang, {{ auth()->user()->name }}</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-calendar mr-2"></i>{{ now()->format('l, d F Y') }}
        </div>
    </div>

    <!-- Worker Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Workers -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pegawai</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalWorkers }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Workers -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pegawai Aktif</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $activeWorkers }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-user-check text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Inactive Workers -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tidak Aktif</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $inactiveWorkers }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <i class="fas fa-user-slash text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Resigned Workers -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Resign</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $resignedWorkers }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-4">
                    <i class="fas fa-user-minus text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Employment Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By Employment Type -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Status Kepegawaian</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Tetap</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $permanentWorkers }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Kontrak</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $contractWorkers }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Percobaan</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $probationWorkers }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                            <span class="text-gray-700">Magang</span>
                        </div>
                        <span class="font-semibold text-gray-900">{{ $internWorkers }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- By Department -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pegawai per Departemen</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    @foreach($workersByDepartment as $dept)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">{{ $dept['department'] }}</span>
                        <span class="font-semibold text-gray-900 bg-gray-100 px-3 py-1 rounded-full text-sm">
                            {{ $dept['total'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Today -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Absensi Hari Ini</h3>
                <i class="fas fa-calendar-check text-blue-500 text-xl"></i>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Hadir
                    </span>
                    <span class="font-bold text-green-600">{{ $attendanceToday }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 flex items-center">
                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                        Terlambat
                    </span>
                    <span class="font-bold text-yellow-600">{{ $lateToday }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 flex items-center">
                        <i class="fas fa-times-circle text-red-500 mr-2"></i>
                        Tidak Hadir
                    </span>
                    <span class="font-bold text-red-600">{{ $absentToday }}</span>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Pending Approval</h3>
                <i class="fas fa-hourglass-half text-yellow-500 text-xl"></i>
            </div>
            <div class="space-y-3">
                <a href="{{ route('approvals.leaves.index') }}" class="flex items-center justify-between hover:bg-gray-50 p-2 rounded transition">
                    <span class="text-gray-600">Cuti</span>
                    <span class="font-bold text-blue-600 bg-blue-100 px-3 py-1 rounded-full text-sm">
                        {{ $pendingLeaves }}
                    </span>
                </a>
                <a href="{{ route('approvals.overtimes.index') }}" class="flex items-center justify-between hover:bg-gray-50 p-2 rounded transition">
                    <span class="text-gray-600">Lembur</span>
                    <span class="font-bold text-purple-600 bg-purple-100 px-3 py-1 rounded-full text-sm">
                        {{ $pendingOvertimes }}
                    </span>
                </a>
                <a href="{{ route('approvals.documents.index') }}" class="flex items-center justify-between hover:bg-gray-50 p-2 rounded transition">
                    <span class="text-gray-600">Dokumen</span>
                    <span class="font-bold text-green-600 bg-green-100 px-3 py-1 rounded-full text-sm">
                        {{ $pendingDocuments }}
                    </span>
                </a>
            </div>
        </div>

        <!-- Monthly Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Bulan Ini</h3>
                <i class="fas fa-chart-line text-green-500 text-xl"></i>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 text-sm">Cuti Disetujui</span>
                    <span class="font-bold text-gray-900">{{ $approvedLeavesThisMonth }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 text-sm">Lembur Disetujui</span>
                    <span class="font-bold text-gray-900">{{ $approvedOvertimesThisMonth }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 text-sm">Total Jam Lembur</span>
                    <span class="font-bold text-gray-900">{{ number_format($totalOvertimeHours, 1) }} jam</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 text-sm">Dokumen Verified</span>
                    <span class="font-bold text-gray-900">{{ $verifiedDocumentsThisMonth }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attendance Chart (Last 7 Days) -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Absensi 7 Hari Terakhir</h3>
            </div>
            <div class="p-6">
                <canvas id="attendanceChart" height="300"></canvas>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }}-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                            <p class="text-sm text-gray-600">{{ $activity['description'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-8">Tidak ada aktivitas terbaru</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leave Requests -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Pengajuan Cuti Terbaru</h3>
            <a href="{{ route('approvals.leaves.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Cuti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentLeaves as $leave)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $leave->worker->name }}</div>
                            <div class="text-sm text-gray-500">{{ $leave->worker->nip }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $leave->leaveType->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $leave->total_days }} hari
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('approvals.leaves.show', $leave->id) }}" class="text-blue-600 hover:text-blue-900">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada pengajuan cuti pending
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Overtime Requests -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Pengajuan Lembur Terbaru</h3>
            <a href="{{ route('approvals.overtimes.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOvertimes as $overtime)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $overtime->worker->name }}</div>
                            <div class="text-sm text-gray-500">{{ $overtime->worker->nip }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($overtime->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($overtime->total_hours, 1) }} jam
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('approvals.overtimes.show', $overtime->id) }}" class="text-blue-600 hover:text-blue-900">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada pengajuan lembur pending
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Attendance Chart
const attendanceData = @json($attendanceChart);
const ctx = document.getElementById('attendanceChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: attendanceData.map(d => d.date),
        datasets: [
            {
                label: 'Hadir',
                data: attendanceData.map(d => d.present),
                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1
            },
            {
                label: 'Terlambat',
                data: attendanceData.map(d => d.late),
                backgroundColor: 'rgba(234, 179, 8, 0.7)',
                borderColor: 'rgba(234, 179, 8, 1)',
                borderWidth: 1
            },
            {
                label: 'Tidak Hadir',
                data: attendanceData.map(d => d.absent),
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 5
                }
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection
