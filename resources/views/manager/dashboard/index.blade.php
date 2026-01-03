@extends('layouts.admin')

@section('title', 'Manager Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manager Dashboard</h1>
            <p class="text-gray-600 mt-1">Selamat datang, {{ auth()->user()->name }}</p>
            <p class="text-sm text-gray-500 mt-1">
                <i class="fas fa-building mr-1"></i>{{ $manager->department->name }}
            </p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-calendar mr-2"></i>{{ now()->format('l, d F Y') }}
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Department Workers -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pegawai</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $departmentWorkers }}</p>
                    <p class="text-xs text-gray-500 mt-1">Departemen Anda</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Attendance Today -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Hadir Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $departmentAttendanceToday }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $attendanceRate }}% tingkat kehadiran</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Late Today -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Terlambat</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $departmentLateToday }}</p>
                    <p class="text-xs text-gray-500 mt-1">Hari ini</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-4">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Absent Today -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tidak Hadir</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $departmentAbsentToday }}</p>
                    <p class="text-xs text-gray-500 mt-1">Hari ini</p>
                </div>
                <div class="bg-red-100 rounded-full p-4">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Approvals -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-hourglass-half text-yellow-500 mr-2"></i>
                    Pending Approval
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <a href="{{ route('approvals.leaves.index') }}" 
                       class="flex items-center justify-between p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition group">
                        <div class="flex items-center">
                            <div class="bg-blue-500 rounded-lg p-3 group-hover:scale-110 transition">
                                <i class="fas fa-calendar-times text-white"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Cuti</p>
                                <p class="text-xs text-gray-500">Pengajuan cuti pending</p>
                            </div>
                        </div>
                        <span class="text-2xl font-bold text-blue-600">{{ $pendingLeaves }}</span>
                    </a>

                    <a href="{{ route('approvals.overtimes.index') }}" 
                       class="flex items-center justify-between p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition group">
                        <div class="flex items-center">
                            <div class="bg-purple-500 rounded-lg p-3 group-hover:scale-110 transition">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Lembur</p>
                                <p class="text-xs text-gray-500">Pengajuan lembur pending</p>
                            </div>
                        </div>
                        <span class="text-2xl font-bold text-purple-600">{{ $pendingOvertimes }}</span>
                    </a>

                    <a href="#" 
                       class="flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg transition group">
                        <div class="flex items-center">
                            <div class="bg-green-500 rounded-lg p-3 group-hover:scale-110 transition">
                                <i class="fas fa-exchange-alt text-white"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Shift Swap</p>
                                <p class="text-xs text-gray-500">Penukaran shift pending</p>
                            </div>
                        </div>
                        <span class="text-2xl font-bold text-green-600">{{ $pendingShiftSwaps }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance Chart (Last 7 Days) -->
        <div class="bg-white rounded-lg shadow lg:col-span-2">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Absensi Departemen 7 Hari Terakhir</h3>
            </div>
            <div class="p-6">
                <canvas id="attendanceChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                Top Performers Bulan Ini
            </h3>
            <p class="text-sm text-gray-600 mt-1">Pegawai dengan kehadiran terbaik (tidak terlambat)</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($topPerformers as $index => $performer)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full {{ $index === 0 ? 'bg-yellow-400' : ($index === 1 ? 'bg-gray-300' : 'bg-orange-400') }} flex items-center justify-center font-bold text-white">
                            {{ $index + 1 }}
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">{{ $performer['name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $performer['days'] }} hari hadir tepat waktu</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-green-600">{{ $performer['rate'] }}%</p>
                        <p class="text-xs text-gray-500">Tingkat kehadiran</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-500 py-8">Belum ada data performa bulan ini</p>
                @endforelse
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe Cuti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentLeaves as $leave)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $leave->worker->name }}</div>
                            <div class="text-sm text-gray-500">{{ $leave->worker->position->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $leave->leaveType->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - 
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="font-semibold text-gray-900">{{ $leave->total_days }}</span> hari
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('approvals.leaves.show', $leave->id) }}" 
                               class="text-blue-600 hover:text-blue-900 font-medium text-sm">
                                Review <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Jam</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOvertimes as $overtime)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $overtime->worker->name }}</div>
                            <div class="text-sm text-gray-500">{{ $overtime->worker->position->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {{ \Carbon\Carbon::parse($overtime->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="font-semibold text-gray-900">{{ number_format($overtime->total_hours, 1) }}</span> jam
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('approvals.overtimes.show', $overtime->id) }}" 
                               class="text-blue-600 hover:text-blue-900 font-medium text-sm">
                                Review <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada pengajuan lembur pending
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Shift Swap Requests -->
    @if($recentShiftSwaps->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Penukaran Shift Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentShiftSwaps as $swap)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $swap->requester->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $swap->targetWorker->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($swap->requesterShift->shift_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="#" class="text-blue-600 hover:text-blue-900 font-medium text-sm">
                                Review
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
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
                    stepSize: 2
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
