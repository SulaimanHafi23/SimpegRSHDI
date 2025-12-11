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
            <img src="{{ auth()->user()->worker->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->worker->name ?? auth()->user()->name) }}"
                 alt="Profile"
                 class="h-20 w-20 rounded-full border-4 border-yellow-300 shadow-lg object-cover">
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-1">{{ auth()->user()->worker->name ?? auth()->user()->name }}</h2>
                <p class="text-yellow-100 text-sm mb-2">
                    <i class="fas fa-briefcase mr-1"></i>
                    {{ auth()->user()->worker->position->name ?? 'Pegawai' }}
                </p>
                <div class="flex items-center space-x-3 text-xs">
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                        <i class="fas fa-id-badge mr-1"></i>
                        {{ auth()->user()->worker->nik ?? '-' }}
                    </span>
                    <span class="bg-white/20 px-3 py-1 rounded-full">
                        <i class="fas fa-calendar mr-1"></i>
                        Bergabung {{ auth()->user()->worker->hire_date?->format('Y') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Absensi Hari Ini -->
    <div class="bg-white rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-calendar-check text-green-600 mr-2"></i>
                Status Kehadiran Hari Ini
            </h3>
            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}</span>
        </div>

        @if($todayAttendance ?? false)
        <div class="space-y-3">
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-500 text-white p-3 rounded-full">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jam Masuk</p>
                        <p class="text-xl font-bold text-gray-800">{{ $todayAttendance->check_in?->format('H:i') ?? '-' }}</p>
                    </div>
                </div>
                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                    {{ $todayAttendance->check_in_status === 'on_time' ? 'Tepat Waktu' : 'Terlambat' }}
                </span>
            </div>

            @if($todayAttendance->check_out)
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-500 text-white p-3 rounded-full">
                        <i class="fas fa-sign-out-alt text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jam Pulang</p>
                        <p class="text-xl font-bold text-gray-800">{{ $todayAttendance->check_out->format('H:i') }}</p>
                    </div>
                </div>
                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                    Selesai
                </span>
            </div>
            @else
            <div class="text-center py-4">
                <a href="{{ route('employee.attendance') }}" class="inline-flex items-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Absen Pulang
                </a>
            </div>
            @endif
        </div>
        @else
        <div class="text-center py-8">
            <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg mb-4">
                <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                <p class="font-semibold">Anda belum absen hari ini!</p>
            </div>
            <a href="{{ route('employee.attendance') }}" class="inline-flex items-center bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold px-8 py-4 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <i class="fas fa-camera mr-2 text-xl"></i>
                Absen Sekarang
            </a>
        </div>
        @endif
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Kehadiran Bulan Ini</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $monthlyStats['attendance'] ?? 0 }}</h3>
                    <p class="text-green-600 text-xs mt-2">
                        <i class="fas fa-arrow-up mr-1"></i>
                        {{ $monthlyStats['attendance_rate'] ?? 0 }}%
                    </p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Sisa Cuti</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $leaveBalance ?? 12 }}</h3>
                    <p class="text-gray-500 text-xs mt-2">
                        <i class="fas fa-calendar mr-1"></i>
                        Hari
                    </p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-umbrella-beach text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Shift -->
        <div class="bg-white rounded-xl shadow-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                    Jadwal Shift Minggu Ini
                </h3>
                <a href="{{ route('employee.attendance') }}" class="text-sm text-green-600 hover:text-green-700 font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>        <div class="space-y-2">
            @forelse($weeklySchedule ?? [] as $schedule)
            <div class="flex items-center justify-between p-4 {{ $schedule->date->isToday() ? 'bg-primary-50 border-l-4 border-primary-500' : 'bg-gray-50' }} rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="text-center">
                        <p class="text-xs text-gray-600">{{ $schedule->date->format('D') }}</p>
                        <p class="text-lg font-bold text-gray-800">{{ $schedule->date->format('d') }}</p>
                    </div>
                    <div class="h-10 w-px bg-gray-300"></div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $schedule->shift->name }}</p>
                        <p class="text-sm text-gray-600">
                            <i class="far fa-clock mr-1"></i>
                            {{ $schedule->shift->start_time }} - {{ $schedule->shift->end_time }}
                        </p>
                    </div>
                </div>
                @if($schedule->date->isToday())
                <span class="bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                    Hari Ini
                </span>
                @endif
            </div>
            @empty
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-calendar-times text-4xl mb-3"></i>
                <p>Belum ada jadwal minggu ini</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Menu Cepat -->
    <div class="bg-white rounded-xl shadow-lg p-5">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
            Menu Cepat
        </h3>
        <div class="grid grid-cols-3 gap-3">
            <a href="{{ route('employee.leaves') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-blue-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-calendar-plus text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Ajukan Cuti</span>
            </a>

            <a href="{{ route('employee.attendance') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-purple-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Ajukan Lembur</span>
            </a>

            <a href="{{ route('employee.documents') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-green-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Dokumen</span>
            </a>

            <a href="{{ route('employee.attendance') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-yellow-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-history text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Riwayat Absen</span>
            </a>

            <a href="{{ route('employee.profile') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-red-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Slip Gaji</span>
            </a>

            <a href="{{ route('employee.profile') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg hover:shadow-md transition duration-300">
                <div class="bg-indigo-500 text-white p-3 rounded-full mb-2">
                    <i class="fas fa-user-edit text-xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Edit Profil</span>
            </a>
        </div>
    </div>

    <!-- Riwayat Pengajuan -->
    <div class="bg-white rounded-xl shadow-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-list-alt text-green-600 mr-2"></i>
                Riwayat Pengajuan Terakhir
            </h3>
            <a href="{{ route('employee.leaves') }}" class="text-sm text-green-600 hover:text-green-700 font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($recentRequests ?? [] as $request)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                <div class="flex items-center space-x-3">
                    <div class="
                        {{ $request->type === 'leave' ? 'bg-blue-100 text-blue-600' : '' }}
                        {{ $request->type === 'overtime' ? 'bg-purple-100 text-purple-600' : '' }}
                        {{ $request->type === 'business_trip' ? 'bg-green-100 text-green-600' : '' }}
                        p-3 rounded-full">
                        <i class="fas 
                            {{ $request->type === 'leave' ? 'fa-calendar-times' : '' }}
                            {{ $request->type === 'overtime' ? 'fa-clock' : '' }}
                            {{ $request->type === 'business_trip' ? 'fa-plane' : '' }}
                            text-lg"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $request->title }}</p>
                        <p class="text-xs text-gray-600">{{ $request->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                    {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $request->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($request->status) }}
                </span>
            </div>
            @empty
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>Belum ada riwayat pengajuan</p>
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
