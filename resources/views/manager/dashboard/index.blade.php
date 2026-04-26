@extends('layouts.admin')

@section('title', 'Manager Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Dashboard Manager"
        description="Ketua Departemen: {{ $manager->department->name }}"
        icon="fas fa-building" />

    {{-- Pending Checkouts Alert (Departemen Anda) --}}
    @if(isset($pendingCheckouts) && $pendingCheckouts->count() > 0)
    <x-card class="border-yellow-200 bg-yellow-50">
        <div class="flex items-start gap-3 text-yellow-800">
            <div class="mt-1"><i class="fas fa-bell text-lg"></i></div>
            <div class="flex-1">
                <h3 class="font-semibold text-lg mb-1">Peringatan: {{ $pendingCheckouts->count() }} pegawai departemen belum check-out</h3>
                {{-- Mobile cards --}}
                <div class="md:hidden divide-y divide-yellow-100">
                    @foreach($pendingCheckouts->take(10) as $item)
                    <div class="py-3">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <span class="font-semibold text-sm">{{ $item['worker_name'] }}</span>
                                <span class="text-xs text-yellow-700 ml-1">{{ $item['position'] }}</span>
                            </div>
                            <span class="text-red-600 font-semibold text-xs">{{ $item['formatted_late'] }}</span>
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-yellow-800">
                            <span><i class="fas fa-clock mr-1"></i>{{ $item['shift_name'] }}</span>
                            <span><i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($item['attendance_date'])->format('d M Y') }}</span>
                            <span><i class="fas fa-hourglass-end mr-1"></i>{{ \Carbon\Carbon::parse($item['shift_end_time'])->format('H:i') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase text-yellow-700">
                            <tr>
                                <th class="py-2 pr-4">Pegawai</th>
                                <th class="py-2 pr-4">Shift</th>
                                <th class="py-2 pr-4">Tanggal</th>
                                <th class="py-2 pr-4">Berakhir</th>
                                <th class="py-2">Terlambat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-yellow-100">
                            @foreach($pendingCheckouts->take(10) as $item)
                            <tr>
                                <td class="py-2 pr-4">
                                    <div class="font-semibold">{{ $item['worker_name'] }}</div>
                                    <div class="text-xs text-yellow-700">{{ $item['position'] }}</div>
                                </td>
                                <td class="py-2 pr-4">{{ $item['shift_name'] }}</td>
                                <td class="py-2 pr-4">{{ \Carbon\Carbon::parse($item['attendance_date'])->format('d M Y') }}</td>
                                <td class="py-2 pr-4">{{ \Carbon\Carbon::parse($item['shift_end_time'])->format('d M Y H:i') }}</td>
                                <td class="py-2 text-red-600 font-semibold">{{ $item['formatted_late'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($pendingCheckouts->count() > 10)
                    <p class="text-xs text-yellow-700 mt-2">Menampilkan 10 teratas dari {{ $pendingCheckouts->count() }} pending checkout.</p>
                @endif
            </div>
        </div>
    </x-card>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <x-stats-card
            title="Total Pegawai"
            :value="$departmentWorkers"
            icon="fas fa-users"
            color="blue"
            trend="Departemen Anda" />

        <x-stats-card
            title="Hadir Hari Ini"
            :value="$departmentAttendanceToday"
            icon="fas fa-clipboard-check"
            color="green"
            :trend="$attendanceRate . '% Kehadiran'"
            trendUp />

        <x-stats-card
            title="Terlambat"
            :value="$departmentLateToday"
            icon="fas fa-clock"
            color="yellow"
            trend="Hari Ini" />

        <x-stats-card
            title="Tidak Hadir"
            :value="$departmentAbsentToday"
            icon="fas fa-times-circle"
            color="red"
            trend="Hari Ini" />
    </div>

    <!-- Pending Approvals & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Approvals -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center">
                    <i class="fas fa-hourglass-half text-yellow-500 mr-2"></i>
                    <span class="text-lg font-semibold text-gray-900">Pending Approval</span>
                </div>
            </x-slot:header>
            <div class="space-y-4">
                          <a href="{{ route('admin.leave.index') }}"
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
        </x-card>

        <!-- Attendance Chart (Last 7 Days) -->
        <x-card title="Absensi Departemen 7 Hari Terakhir" class="lg:col-span-2">
            <canvas id="attendanceChart" height="250"></canvas>
        </x-card>
    </div>

    <!-- Top Performers -->
    <x-card>
        <x-slot:header>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                    Top Performers Bulan Ini
                </h3>
                <p class="text-sm text-gray-600 mt-1">Pegawai dengan kehadiran terbaik (tidak terlambat)</p>
            </div>
        </x-slot:header>
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
    </x-card>

    <!-- Recent Leave Requests -->
    <x-card>
        <x-slot:header>
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Pengajuan Cuti Terbaru</h3>
                <a href="{{ route('admin.leave.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </x-slot:header>
        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($recentLeaves as $leave)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $leave->worker->name }}</p>
                        <p class="text-xs text-gray-500">{{ $leave->worker->department->name ?? '-' }}</p>
                    </div>
                    <a href="{{ route('admin.leave.show', $leave->id) }}" class="text-blue-600 text-sm font-medium">Review <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded">{{ $leave->leaveType->name }}</span>
                    <span class="text-gray-600">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</span>
                    <span class="font-semibold text-gray-900">{{ $leave->total_days }} hari</span>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">Tidak ada pengajuan cuti pending</div>
            @endforelse
        </div>
        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
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
                            <div class="text-sm text-gray-500">{{ $leave->worker->department->name ?? '-' }}</div>
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
                                     <a href="{{ route('admin.leave.show', $leave->id) }}"
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
    </x-card>

    <!-- Recent Shift Swap Requests -->
    @if($recentShiftSwaps->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Penukaran Shift Terbaru</h3>
        </div>
        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($recentShiftSwaps as $swap)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $swap->requester->name }}</p>
                        <p class="text-xs text-gray-500"><i class="fas fa-exchange-alt mr-1"></i>{{ $swap->targetWorker->name ?? '-' }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600"><i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($swap->requesterShift->shift_date)->format('d M Y') }}</span>
                    <a href="#" class="text-blue-600 font-medium">Review</a>
                </div>
            </div>
            @endforeach
        </div>
        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
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

    {{-- Quick Actions for Manager --}}
    <x-card title="Aksi Cepat">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
            @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('dashboard.manager') || auth()->user()->can('attendance.manage'))
                <x-button
                    variant="success"
                    icon="fas fa-clipboard-check"
                    onclick="window.location.href='{{ route('admin.attendance.index') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Lihat Absensi</span>
                    <span class="sm:hidden">Absensi</span>
                </x-button>
            @endif

            @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('dashboard.manager') || auth()->user()->can('leave.approve'))
                <x-button
                    variant="warning"
                    icon="fas fa-calendar-check"
                    onclick="window.location.href='{{ route('admin.leave.index') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Approval Cuti</span>
                    <span class="sm:hidden">Cuti</span>
                </x-button>
            @endif

            @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('dashboard.manager') || auth()->user()->can('schedule.manage'))
                <x-button
                    variant="secondary"
                    icon="fas fa-user-clock"
                    onclick="window.location.href='{{ route('admin.worker-shifts.index') }}'"
                    class="w-full justify-center">
                    <span class="hidden sm:inline">Jadwal Pegawai</span>
                    <span class="sm:hidden">Jadwal</span>
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
