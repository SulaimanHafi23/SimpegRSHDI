{{-- filepath: resources/views/employee/dashboard/index.blade.php --}}
@extends('layouts.employee')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="space-y-4 pb-6">
    <!-- Welcome Card dengan Info Pegawai -->
    <div class="bg-gradient-to-br from-green-600 via-green-700 to-green-800 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-300 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-yellow-400 rounded-full -ml-12 -mb-12"></div>
        </div>
        
        <div class="flex items-center space-x-4 relative z-10">
            @if($worker->photo_url)
                <img src="{{ Storage::url($worker->photo_url) }}"
                     alt="Profile"
                     class="h-20 w-20 rounded-full border-4 border-yellow-300 shadow-lg object-cover">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($worker->name) }}&background=random"
                     alt="Profile"
                     class="h-20 w-20 rounded-full border-4 border-yellow-300 shadow-lg object-cover">
            @endif
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

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Hadir</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ $attendanceSummary['present'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Bulan ini</p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Terlambat</p>
                    <h3 class="text-3xl font-bold text-yellow-600">{{ $attendanceSummary['late'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Bulan ini</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Tidak Hadir</p>
                    <h3 class="text-3xl font-bold text-red-600">{{ $attendanceSummary['absent'] ?? 0 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Bulan ini</p>
                </div>
                <div class="bg-red-100 p-4 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Sisa Cuti</p>
                    <h3 class="text-3xl font-bold text-blue-600">{{ $leaveBalance['annual_leave'] - $leaveBalance['used_annual'] }}</h3>
                    <p class="text-gray-500 text-xs mt-2">Hari</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-umbrella-beach text-blue-600 text-2xl"></i>
                </div>
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
            <a href="{{ route('admin.leave.create') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-blue-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-calendar-plus text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Ajukan Cuti</span>
            </a>

            <a href="{{ route('admin.overtime.create') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-purple-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Ajukan Lembur</span>
            </a>

            <a href="{{ route('admin.worker-documents.index') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-green-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Dokumen</span>
            </a>

            <a href="{{ route('admin.attendance.index') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-yellow-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-history text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Riwayat Absen</span>
            </a>

            <a href="{{ route('admin.leave.index') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-red-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-calendar-times text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Riwayat Cuti</span>
            </a>

            <a href="{{ route('profile.show') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-indigo-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-user-edit text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Edit Profil</span>
            </a>
        </div>
    </div>

    <!-- Riwayat Pengajuan Cuti -->
    <div class="bg-white rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-list-alt text-green-600 mr-2"></i>
                Riwayat Pengajuan Cuti Terakhir
            </h3>
            <a href="#" class="text-sm text-green-600 hover:text-green-700 font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($recentLeaves as $leave)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                        <i class="fas fa-calendar-times text-lg"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">
                            Cuti {{ $leave->leaveType->name ?? 'Umum' }}
                        </p>
                        <p class="text-xs text-gray-600">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                    {{ $leave->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $leave->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $leave->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($leave->status) }}
                </span>
            </div>
            @empty
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>Belum ada riwayat pengajuan cuti</p>
            </div>
            @endforelse
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
@endsection
