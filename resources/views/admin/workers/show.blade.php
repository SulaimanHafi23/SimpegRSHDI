@extends('layouts.admin')

@section('title', 'Detail Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header with Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Pegawai</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">Informasi lengkap pegawai dan ringkasan aktivitasnya.</p>
        </div>
        <div class="flex space-x-2 w-full sm:w-auto">
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                <x-button
                    variant="warning"
                    icon="fas fa-edit"
                    onclick="window.location.href='{{ route('admin.workers.edit', $worker->id ?? 1) }}'">
                    Edit
                </x-button>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                <x-button
                    variant="danger"
                    icon="fas fa-trash"
                    onclick="if(confirm('Yakin ingin menghapus?')) document.getElementById('delete-form').submit()">
                    Hapus
                </x-button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Left Column - Profile Card --}}
        <div class="lg:col-span-1 space-y-4 sm:space-y-6">
            {{-- Profile Photo & Status --}}
            <x-card>
                @php
                    $workerPhotoUrl = ($worker->photo_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo_url))
                        ? \Illuminate\Support\Facades\Storage::url($worker->photo_url)
                        : null;
                @endphp
                <div class="flex flex-col items-center">
                    @if($workerPhotoUrl)
                        <img src="{{ $workerPhotoUrl }}"
                             alt="{{ $worker->name ?? '' }}"
                             class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-green-500 object-cover mb-4"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-green-500 overflow-hidden bg-gray-100 flex items-center justify-center mb-4\'><i class=\'fas fa-user text-4xl sm:text-5xl text-gray-400\'></i></div>';">
                    @else
                        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-green-500 overflow-hidden bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fas fa-user text-4xl sm:text-5xl text-gray-400"></i>
                        </div>
                    @endif
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-2">{{ $worker->name ?? '[Nama Pegawai]' }}</h2>
                    @php
                        $statusDisplay = [
                            'active' => ['variant' => 'success', 'label' => 'Aktif'],
                            'inactive' => ['variant' => 'warning', 'label' => 'Non-Aktif'],
                            'resigned' => ['variant' => 'danger', 'label' => 'Resign'],
                        ];
                        $currentStatus = $statusDisplay[$worker->status ?? 'active'] ?? ['variant' => 'secondary', 'label' => '-'];
                    @endphp
                    <x-badge :variant="$currentStatus['variant']" icon="fas fa-circle">
                        {{ $currentStatus['label'] }}
                    </x-badge>
                </div>
                <div class="mt-6 space-y-3 text-center">
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">NIP</p>
                        <p class="font-semibold text-gray-900">{{ $worker->nip ?? '-' }}</p>
                    </div>
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">Departemen</p>
                        <p class="font-semibold text-gray-900">{{ $worker->department->name ?? '-' }}</p>
                    </div>
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">Status Kepegawaian</p>
                        @php
                            $statusLabels = [
                                'permanent' => 'Tetap',
                                'contract' => 'Kontrak',
                                'internship' => 'Magang',
                            ];
                        @endphp
                        <p class="font-semibold text-gray-900">{{ $statusLabels[$worker->employment_status ?? ''] ?? '-' }}</p>
                    </div>
                    <div class="py-3 border-t">
                        <p class="text-sm text-gray-600">Bergabung</p>
                        <p class="font-semibold text-gray-900">{{ $worker->hire_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Quick Actions --}}
            <x-card title="Aksi Cepat">
                <div class="space-y-2">
                    <a href="{{ route('admin.workers.attendance-history', $worker->id) }}" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span class="ml-3">Riwayat Absensi</span>
                    </a>
                </div>
            </x-card>
        </div>

        {{-- Right Column - Detailed Information --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Information --}}
            <x-card>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-circle text-green-600 mr-2"></i>
                    Data Pribadi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Tempat Lahir</p>
                        <p class="font-semibold text-gray-900">{{ $worker->birth_place ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Lahir</p>
                        <p class="font-semibold text-gray-900">{{ $worker->birth_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jenis Kelamin</p>
                        <p class="font-semibold text-gray-900">{{ $worker->gender->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Agama</p>
                        <p class="font-semibold text-gray-900">{{ $worker->religion->name ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">Alamat</p>
                        <p class="font-semibold text-gray-900">{{ $worker->address ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Contact Information --}}
            <x-card>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-phone text-green-600 mr-2"></i>
                    Informasi Kontak
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">No. Telepon</p>
                        <p class="font-semibold text-gray-900">{{ $worker->phone_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-semibold text-gray-900">{{ $worker->email ?? '-' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Employment Information --}}
            <x-card>
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-briefcase text-green-600 mr-2"></i>
                    Informasi Kepegawaian
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Departemen</p>
                        <p class="font-semibold text-gray-900">{{ $worker->department->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status Kepegawaian</p>
                        @php
                            $employmentBadges = [
                                'permanent' => ['variant' => 'success', 'label' => 'Tetap'],
                                'contract' => ['variant' => 'warning', 'label' => 'Kontrak'],
                                'internship' => ['variant' => 'primary', 'label' => 'Magang'],
                            ];
                            $badge = $employmentBadges[$worker->employment_status ?? ''] ?? ['variant' => 'secondary', 'label' => '-'];
                        @endphp
                        <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Masuk</p>
                        <p class="font-semibold text-gray-900">{{ $worker->hire_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Tanggal Resign</p>
                        <p class="font-semibold text-gray-900">{{ $worker->resign_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        @php
                            $statusBadges = [
                                'active' => ['variant' => 'success', 'label' => 'Aktif'],
                                'inactive' => ['variant' => 'warning', 'label' => 'Non-Aktif'],
                                'resigned' => ['variant' => 'danger', 'label' => 'Resign'],
                            ];
                            $statusBadge = $statusBadges[$worker->status ?? 'active'] ?? ['variant' => 'secondary', 'label' => '-'];
                        @endphp
                        <x-badge :variant="$statusBadge['variant']">{{ $statusBadge['label'] }}</x-badge>
                    </div>
                </div>
            </x-card>

            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-stats-card
                    title="Hadir Bulan Ini"
                    :value="($attendanceThisMonth ?? 0) . ' Hari'"
                    icon="fas fa-calendar-check"
                    color="blue" />
                <x-stats-card
                    title="Total Lembur"
                    :value="($totalOvertime ?? 0) . ' Jam'"
                    icon="fas fa-clock"
                    color="green" />
            </div>

            {{-- Riwayat Shift --}}
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-exchange-alt text-green-600 mr-2"></i>
                        Riwayat Perubahan Shift
                    </h3>
                </div>

                @if(isset($shiftHistories) && $shiftHistories->isNotEmpty())
                    <div class="space-y-3">
                        {{-- Current active shift --}}
                        @if($worker->activeWorkerShift)
                            <div class="border-2 border-green-300 bg-green-50 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $worker->activeWorkerShift->shift->name ?? '-' }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ \Carbon\Carbon::parse($worker->activeWorkerShift->effective_from)->format('d M Y') }} — Sekarang
                                        </p>
                                    </div>
                                    <x-badge variant="success" icon="fas fa-check-circle" size="sm">Aktif</x-badge>
                                </div>
                            </div>
                        @endif

                        {{-- Past shifts from history --}}
                        @foreach($shiftHistories->take(5) as $history)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $history->shift->name ?? '-' }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ \Carbon\Carbon::parse($history->effective_from)->format('d M Y') }}
                                            <span class="text-gray-400 mx-1">—</span>
                                            @if($history->effective_until)
                                                {{ \Carbon\Carbon::parse($history->effective_until)->format('d M Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($history->changed_at)->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        @php
                                            $reasonConfig = [
                                                'shift_replaced' => ['variant' => 'primary', 'icon' => 'fas fa-sync', 'label' => 'Diganti'],
                                                'shift_updated' => ['variant' => 'warning', 'icon' => 'fas fa-edit', 'label' => 'Diperbarui'],
                                                'shift_deleted' => ['variant' => 'danger', 'icon' => 'fas fa-trash', 'label' => 'Dihapus'],
                                            ];
                                            $rCfg = $reasonConfig[$history->change_reason] ?? ['variant' => 'secondary', 'icon' => 'fas fa-question', 'label' => $history->change_reason];
                                        @endphp
                                        <x-badge :variant="$rCfg['variant']" :icon="$rCfg['icon']" size="sm">
                                            {{ $rCfg['label'] }}
                                        </x-badge>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-user mr-1"></i>
                                    Diubah oleh: {{ $history->changedByUser->name ?? 'System' }}
                                    pada {{ \Carbon\Carbon::parse($history->changed_at)->format('d M Y') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-exchange-alt text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500">Belum ada riwayat perubahan shift</p>
                    </div>
                @endif
            </x-card>

            {{-- Daftar Cuti --}}
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-plane text-yellow-600 mr-2"></i>
                        Riwayat Cuti
                    </h3>
                    <a href="#" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        Lihat Semua
                    </a>
                </div>

                @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($leaveRequests->take(5) as $leave)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $leave->leaveType->name ?? 'Cuti' }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ $leave->start_date?->format('d M Y') }} - {{ $leave->end_date?->format('d M Y') }}
                                            <span class="text-gray-500">({{ $leave->total_days }} hari)</span>
                                        </p>
                                    </div>
                                    <div>
                                        @php
                                            $leaveStatusConfig = [
                                                'pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu'],
                                                'approved' => ['variant' => 'success', 'icon' => 'fas fa-check-circle', 'label' => 'Disetujui'],
                                                'rejected' => ['variant' => 'danger', 'icon' => 'fas fa-times-circle', 'label' => 'Ditolak'],
                                                'cancelled' => ['variant' => 'secondary', 'icon' => 'fas fa-ban', 'label' => 'Dibatalkan'],
                                            ];
                                            $leaveStatus = $leaveStatusConfig[$leave->status] ?? ['variant' => 'secondary', 'icon' => 'fas fa-question', 'label' => 'Unknown'];
                                        @endphp
                                        <x-badge :variant="$leaveStatus['variant']" :icon="$leaveStatus['icon']" size="sm">
                                            {{ $leaveStatus['label'] }}
                                        </x-badge>
                                    </div>
                                </div>
                                @if($leave->reason)
                                    <p class="text-sm text-gray-600 mt-2">
                                        <i class="fas fa-comment-dots mr-1"></i>
                                        {{ \Illuminate\Support\Str::limit($leave->reason, 100) }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-plane text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500">Belum ada riwayat cuti</p>
                    </div>
                @endif
            </x-card>

            {{-- Daftar Lembur --}}
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        Riwayat Lembur
                    </h3>
                    <a href="#" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        Lihat Semua
                    </a>
                </div>

                @if(isset($overtimeRequests) && $overtimeRequests->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($overtimeRequests->take(5) as $overtime)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">Lembur - {{ $overtime->overtime_date?->format('d M Y') }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}
                                            <span class="text-gray-500">({{ $overtime->total_hours }} jam)</span>
                                        </p>
                                    </div>
                                    <div>
                                        @php
                                            $overtimeStatusConfig = [
                                                'pending' => ['variant' => 'warning', 'icon' => 'fas fa-clock', 'label' => 'Menunggu'],
                                                'approved' => ['variant' => 'success', 'icon' => 'fas fa-check-circle', 'label' => 'Disetujui'],
                                                'rejected' => ['variant' => 'danger', 'icon' => 'fas fa-times-circle', 'label' => 'Ditolak'],
                                                'cancelled' => ['variant' => 'secondary', 'icon' => 'fas fa-ban', 'label' => 'Dibatalkan'],
                                            ];
                                            $overtimeStatus = $overtimeStatusConfig[$overtime->status] ?? ['variant' => 'secondary', 'icon' => 'fas fa-question', 'label' => 'Unknown'];
                                        @endphp
                                        <x-badge :variant="$overtimeStatus['variant']" :icon="$overtimeStatus['icon']" size="sm">
                                            {{ $overtimeStatus['label'] }}
                                        </x-badge>
                                    </div>
                                </div>
                                @if($overtime->description)
                                    <p class="text-sm text-gray-600 mt-2">
                                        <i class="fas fa-tasks mr-1"></i>
                                        {{ \Illuminate\Support\Str::limit($overtime->description, 100) }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-clock text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500">Belum ada riwayat lembur</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>

@if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
    <form id="delete-form" action="{{ route('admin.workers.destroy', $worker->id ?? 1) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
