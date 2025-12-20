@extends('layouts.admin')

@section('title', 'Detail Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header with Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <x-button 
                variant="secondary" 
                size="sm"
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.workers.index') }}'">
            </x-button>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Pegawai</h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Informasi lengkap pegawai</p>
            </div>
        </div>
        <div class="flex space-x-2 w-full sm:w-auto">
            @can('edit-workers')
                <x-button 
                    variant="warning" 
                    icon="fas fa-edit"
                    onclick="window.location.href='{{ route('admin.workers.edit', $worker->id ?? 1) }}'">
                    Edit
                </x-button>
            @endcan
            @can('delete-workers')
                <x-button 
                    variant="danger" 
                    icon="fas fa-trash"
                    onclick="if(confirm('Yakin ingin menghapus?')) document.getElementById('delete-form').submit()">
                    Hapus
                </x-button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Left Column - Profile Card --}}
        <div class="lg:col-span-1 space-y-4 sm:space-y-6">
            {{-- Profile Photo & Status --}}
            <x-card>
                <div class="flex flex-col items-center">
                    @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                        <img src="{{ asset('storage/' . $worker->photo_url) }}" 
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
                    <a href="#" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-700">
                        <i class="fas fa-file-invoice w-5"></i>
                        <span class="ml-3">Lihat Slip Gaji</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-700">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span class="ml-3">Riwayat Absensi</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg text-yellow-700">
                        <i class="fas fa-plane w-5"></i>
                        <span class="ml-3">Daftar Cuti</span>
                    </a>
                    <a href="#" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-700">
                        <i class="fas fa-clock w-5"></i>
                        <span class="ml-3">Lembur</span>
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
        </div>
    </div>
</div>

@can('delete-workers')
    <form id="delete-form" action="{{ route('admin.workers.destroy', $worker->id ?? 1) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endcan
@endsection
