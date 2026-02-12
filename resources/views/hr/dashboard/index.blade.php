@extends('layouts.admin')

@section('title', 'HR Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Dashboard HR"
        description="Direktur: {{ auth()->user()->name }}"
        icon="fas fa-user-tie" />

    {{-- Worker Statistics Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <x-stats-card
            title="Total Pegawai"
            :value="$totalWorkers"
            icon="fas fa-users"
            color="blue"
            :trend="$activeWorkers . ' Aktif'"
            trendUp />

        <x-stats-card
            title="Pegawai Aktif"
            :value="$activeWorkers"
            icon="fas fa-user-check"
            color="green"
            :trend="$inactiveWorkers . ' Nonaktif'" />

        <x-stats-card
            title="Tidak Aktif"
            :value="$inactiveWorkers"
            icon="fas fa-user-slash"
            color="yellow"
            trend="Status Pegawai" />

        <x-stats-card
            title="Resign"
            :value="$resignedWorkers"
            icon="fas fa-user-minus"
            color="red"
            trend="Riwayat" />
    </div>

    <!-- Employment Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By Employment Type -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center">
                    <i class="mr-2 text-green-600 fas fa-id-badge"></i>
                    <span class="text-lg font-semibold text-gray-900">Status Kepegawaian</span>
                </div>
            </x-slot:header>
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
            </x-card>

        <!-- By Department -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center">
                    <i class="mr-2 text-green-600 fas fa-chart-pie"></i>
                    <span class="text-lg font-semibold text-gray-900">Pegawai per Departemen</span>
                </div>
            </x-slot:header>
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
        </x-card>
    </div>

    <!-- Attendance Today -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Absensi Hari Ini</h3>
                    <i class="fas fa-calendar-check text-blue-500 text-xl"></i>
                </div>
            </x-slot:header>
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
        </x-card>

        <!-- Pending Approvals -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Pending Approval</h3>
                    <i class="fas fa-hourglass-half text-yellow-500 text-xl"></i>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <a href="{{ route('admin.leave.index') }}" class="flex items-center justify-between hover:bg-gray-50 p-2 rounded transition">
                    <span class="text-gray-600">Cuti</span>
                    <span class="font-bold text-blue-600 bg-blue-100 px-3 py-1 rounded-full text-sm">
                        {{ $pendingLeaves }}
                    </span>
                </a>
                <a href="{{ route('admin.overtime.index') }}" class="flex items-center justify-between hover:bg-gray-50 p-2 rounded transition">
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
        </x-card>

        <!-- Monthly Stats -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Bulan Ini</h3>
                    <i class="fas fa-chart-line text-green-500 text-xl"></i>
                </div>
            </x-slot:header>
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
        </x-card>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attendance Chart (Last 7 Days) -->
        <x-card title="Absensi 7 Hari Terakhir">
            <canvas id="attendanceChart" height="300"></canvas>
        </x-card>

        <!-- Recent Activities -->
        <x-card title="Aktivitas Terbaru">
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
            </x-card>
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

    {{-- Quick Actions for HR --}}
    <x-card title="Aksi Cepat">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('worker.manage'))
                <x-button 
                    variant="primary" 
                    icon="fas fa-user-plus"
                    onclick="window.location.href='{{ route('admin.workers.create') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Tambah Pegawai</span>
                    <span class="sm:hidden">Pegawai</span>
                </x-button>
            @endif
            
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('attendance.manage'))
                <x-button 
                    variant="success" 
                    icon="fas fa-clipboard-check"
                    onclick="window.location.href='{{ route('admin.attendance.create') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Input Absensi</span>
                    <span class="sm:hidden">Absensi</span>
                </x-button>
            @endif
            
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('report.view'))
                <x-button 
                    variant="warning" 
                    icon="fas fa-chart-bar"
                    onclick="window.location.href='{{ route('admin.attendance.report.monthly') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Laporan Absensi</span>
                    <span class="sm:hidden">Laporan</span>
                </x-button>
            @endif
            
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve'))
                <x-button 
                    variant="secondary" 
                    icon="fas fa-tasks"
                    onclick="window.location.href='{{ route('admin.leave.index') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Kelola Cuti</span>
                    <span class="sm:hidden">Cuti</span>
                </x-button>
            @endif
        </div>
    </x-card>
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
