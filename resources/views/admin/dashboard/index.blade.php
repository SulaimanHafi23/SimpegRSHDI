@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Dashboard Admin" 
        description="Selamat datang di SIMPEGRS RSUD Haji Darjlan Ismail"
        icon="fas fa-tachometer-alt" />

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <x-stats-card 
            title="Total Pegawai" 
            :value="$statistics['total_workers'] ?? 0" 
            icon="fas fa-users" 
            color="blue"
            :trend="($statistics['active_workers'] ?? 0) . ' Aktif'"
            trendUp />
        
        <x-stats-card 
            title="Hadir Hari Ini" 
            :value="$statistics['present_today'] ?? 0" 
            icon="fas fa-clipboard-check" 
            color="green"
            :trend="($statistics['attendance_rate'] ?? 0) . '% Kehadiran'"
            trendUp />
        
        <x-stats-card 
            title="Permohonan Cuti" 
            :value="$statistics['pending_leaves'] ?? 0" 
            icon="fas fa-calendar-times" 
            color="yellow"
            trend="Menunggu Approval" />
        
        <x-stats-card 
            title="Permohonan Lembur" 
            :value="$statistics['pending_overtimes'] ?? 0" 
            icon="fas fa-business-time" 
            color="purple"
            trend="Menunggu Approval" />
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        {{-- Attendance Chart --}}
        <x-card title="Grafik Kehadiran Minggu Ini">
            <x-slot:header>
                <div class="flex items-center">
                    <i class="mr-2 text-green-600 fas fa-chart-line"></i>
                    <span>Grafik Kehadiran Minggu Ini</span>
                </div>
                <select class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option>Minggu Ini</option>
                    <option>Bulan Ini</option>
                    <option>Tahun Ini</option>
                </select>
            </x-slot:header>
            
            <div class="flex items-center justify-center h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </x-card>

        {{-- Department Distribution --}}
        <x-card>
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                <i class="mr-2 text-green-600 fas fa-chart-pie"></i>
                Distribusi Pegawai per Departemen
            </h3>
            
            @if(isset($positionDistribution) && count($positionDistribution) > 0)
                <div class="space-y-4">
                    @foreach($positionDistribution as $position)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ $position->name }}</span>
                                <span class="text-sm font-bold text-gray-900">{{ $position->workers_count }}</span>
                            </div>
                            <div class="w-full h-3 bg-gray-200 rounded-full">
                                <div class="h-3 transition-all duration-500 rounded-full bg-gradient-to-r from-green-500 to-green-600"
                                     style="width: {{ ($position->workers_count / max($statistics['total_workers'] ?? 1, 1)) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="fas fa-inbox"
                    title="Belum ada data"
                    description="Data distribusi pegawai per departemen akan ditampilkan di sini" />
            @endif
        </x-card>
    </div>

    {{-- Recent Activities --}}
    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
        {{-- Recent Leave Requests --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="mr-2 text-yellow-600 fas fa-calendar-alt"></i>
                    Pengajuan Cuti Terbaru
                </h3>
                <a href="{{ route('admin.leave.index') }}" class="text-sm font-medium text-green-600 hover:text-green-700">
                    Lihat Semua <i class="ml-1 fas fa-arrow-right"></i>
                </a>
            </div>
            
            @if(isset($recentLeaves) && count($recentLeaves) > 0)
                <div class="space-y-3">
                    @foreach($recentLeaves as $leave)
                        <div class="flex items-center justify-between p-4 transition duration-200 rounded-lg bg-gray-50 hover:bg-gray-100">
                            <div class="flex items-center space-x-3">
                                @if($leave->worker->photo_url ?? false)
                                    <img src="{{ Storage::url($leave->worker->photo_url) }}" 
                                         alt="{{ $leave->worker->name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <span class="text-yellow-600 font-semibold">{{ substr($leave->worker->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $leave->worker->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $leave->leave_type->name ?? '-' }}</p>
                                </div>
                            </div>
                            <x-badge variant="warning">Pending</x-badge>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="fas fa-calendar-alt"
                    title="Belum ada pengajuan cuti"
                    description="Pengajuan cuti terbaru akan ditampilkan di sini" />
            @endif
        </x-card>

        {{-- Recent Overtime Requests --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="mr-2 text-purple-600 fas fa-clock"></i>
                    Pengajuan Lembur Terbaru
                </h3>
                <a href="{{ route('admin.overtime.index') }}" class="text-sm font-medium text-green-600 hover:text-green-700">
                    Lihat Semua <i class="ml-1 fas fa-arrow-right"></i>
                </a>
            </div>
            
            @if(isset($recentOvertimes) && count($recentOvertimes) > 0)
                <div class="space-y-3">
                    @foreach($recentOvertimes as $overtime)
                        <div class="flex items-center justify-between p-4 transition duration-200 rounded-lg bg-gray-50 hover:bg-gray-100">
                            <div class="flex items-center space-x-3">
                                @if($overtime->worker->photo_url ?? false)
                                    <img src="{{ Storage::url($overtime->worker->photo_url) }}" 
                                         alt="{{ $overtime->worker->name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-semibold">{{ substr($overtime->worker->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $overtime->worker->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $overtime->duration ?? '-' }} jam</p>
                                </div>
                            </div>
                            @if($overtime->status == 'approved')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Disetujui
                            </span>
                            @elseif($overtime->status == 'rejected')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Ditolak
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                            @endif
                            <!-- <x-badge variant="warning">{{ $overtime->status ?? '-' }}</x-badge> -->
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="fas fa-clock"
                    title="Belum ada pengajuan lembur"
                    description="Pengajuan lembur terbaru akan ditampilkan di sini" />
            @endif
        </x-card>
    </div>

    {{-- Quick Actions --}}
    <x-card title="Aksi Cepat">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @can('create-workers')
                <x-button 
                    variant="primary" 
                    icon="fas fa-user-plus"
                    onclick="window.location.href='{{ route('admin.workers.create') }}'"
                    class="w-full justify-center">
                    Tambah Pegawai
                </x-button>
            @endcan
            
            @can('create-attendance')
                <x-button 
                    variant="success" 
                    icon="fas fa-clipboard-check"
                    onclick="window.location.href='{{ route('admin.attendance.create') }}'"
                    class="w-full justify-center">
                    Input Absensi
                </x-button>
            @endcan
            
            @can('view-attendance')
                <x-button 
                    variant="warning" 
                    icon="fas fa-chart-bar"
                    onclick="window.location.href='{{ route('admin.attendance.report.monthly') }}'"
                    class="w-full justify-center">
                    Laporan Absensi
                </x-button>
            @endcan
            
            @can('view-leave')
                <x-button 
                    variant="secondary" 
                    icon="fas fa-tasks"
                    onclick="window.location.href='{{ route('admin.leave.index') }}'"
                    class="w-full justify-center">
                    Kelola Cuti
                </x-button>
            @endcan
        </div>
    </x-card>
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
                labels: {!! json_encode($attendanceChart['labels'] ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!},
                datasets: [{
                    label: 'Kehadiran',
                    data: {!! json_encode($attendanceChart['data'] ?? [0, 0, 0, 0, 0, 0, 0]) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
