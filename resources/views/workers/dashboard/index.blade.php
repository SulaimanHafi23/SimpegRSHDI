{{-- filepath: resources/views/workers/dashboard/index.blade.php --}}
@extends('layouts.workers')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Welcome Card dengan Info Pegawai -->
    <div class="relative p-6 sm:p-8 overflow-hidden text-white shadow-xl bg-gradient-to-br from-green-600 via-green-700 to-green-800 rounded-2xl">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-32 h-32 -mt-16 -mr-16 bg-yellow-300 rounded-full"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 -mb-12 -ml-12 bg-yellow-400 rounded-full"></div>
        </div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-4">
            <img src="{{ auth()->user()->worker->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->worker->name ?? auth()->user()->name) }}"
                 alt="Profile"
                 class="object-cover w-20 h-20 sm:w-24 sm:h-24 border-4 border-yellow-300 rounded-full shadow-lg">
            <div class="flex-1 text-center sm:text-left">
                <h2 class="mb-1 text-xl sm:text-2xl lg:text-3xl font-bold">{{ auth()->user()->worker->name ?? auth()->user()->name }}</h2>
                <p class="mb-3 text-sm sm:text-base text-yellow-100">
                    <i class="mr-1 fas fa-briefcase"></i>
                    {{ auth()->user()->worker->position->name ?? 'Pegawai' }}
                </p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs sm:text-sm">
                    <span class="px-3 py-1 rounded-full bg-white/20">
                        <i class="mr-1 fas fa-id-badge"></i>
                        {{ auth()->user()->worker->nik ?? '-' }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-white/20">
                        <i class="mr-1 fas fa-calendar"></i>
                        Bergabung {{ auth()->user()->worker->hire_date?->format('Y') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Absensi Hari Ini -->
    <div class="p-5 sm:p-6 lg:p-8 bg-white shadow-lg rounded-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800">
                <i class="mr-2 text-green-600 fas fa-calendar-check"></i>
                Status Kehadiran Hari Ini
            </h3>
            <span class="text-xs sm:text-sm text-gray-500">{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}</span>
        </div>

        @if($todayAttendance ?? false)
        <div class="space-y-3">
            <div class="flex items-center justify-between p-4 border-l-4 border-green-500 rounded-lg bg-green-50">
                <div class="flex items-center space-x-3">
                    <div class="p-3 text-white bg-green-500 rounded-full">
                        <i class="text-xl fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jam Masuk</p>
                        <p class="text-xl font-bold text-gray-800">{{ $todayAttendance->check_in?->format('H:i') ?? '-' }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-bold text-white bg-green-500 rounded-full">
                    {{ $todayAttendance->check_in_status === 'on_time' ? 'Tepat Waktu' : 'Terlambat' }}
                </span>
            </div>

            @if($todayAttendance->check_out)
            <div class="flex items-center justify-between p-4 border-l-4 border-blue-500 rounded-lg bg-blue-50">
                <div class="flex items-center space-x-3">
                    <div class="p-3 text-white bg-blue-500 rounded-full">
                        <i class="text-xl fas fa-sign-out-alt"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jam Pulang</p>
                        <p class="text-xl font-bold text-gray-800">{{ $todayAttendance->check_out->format('H:i') }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-bold text-white bg-blue-500 rounded-full">
                    Selesai
                </span>
            </div>
            @else
            <div class="py-4 text-center">
                <a href="{{ route('workers.attendance.index') }}" class="inline-flex items-center px-6 py-3 font-bold text-white transition duration-300 rounded-lg shadow-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl">
                    <i class="mr-2 fas fa-sign-out-alt"></i>
                    Absen Pulang
                </a>
            </div>
            @endif
        </div>
        @else
        <div class="py-8 text-center">
            <div class="p-4 mb-4 text-yellow-800 bg-yellow-100 rounded-lg">
                <i class="mb-2 text-3xl fas fa-exclamation-triangle"></i>
                <p class="font-semibold">Anda belum absen hari ini!</p>
            </div>
            <a href="{{ route('workers.attendance.index') }}" class="inline-flex items-center px-8 py-4 font-bold text-white transition duration-300 rounded-lg shadow-lg bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 hover:shadow-xl">
                <i class="mr-2 text-xl fas fa-camera"></i>
                Absen Sekarang
            </a>
        </div>
        @endif
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 sm:p-6 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs sm:text-sm text-gray-600">Kehadiran Bulan Ini</p>
                    <h3 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">{{ $monthlyStats['attendance'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs sm:text-sm text-green-600">
                        <i class="mr-1 fas fa-arrow-up"></i>
                        {{ $monthlyStats['attendance_rate'] ?? 0 }}%
                    </p>
                </div>
                <div class="p-3 sm:p-4 bg-green-100 rounded-full">
                    <i class="text-xl sm:text-2xl text-green-600 fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs sm:text-sm text-gray-600">Sisa Cuti</p>
                    <h3 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">{{ $leaveBalance ?? 12 }}</h3>
                    <p class="mt-2 text-xs sm:text-sm text-gray-500">
                        <i class="mr-1 fas fa-calendar"></i>
                        Hari
                    </p>
                </div>
                <div class="p-3 sm:p-4 bg-yellow-100 rounded-full">
                    <i class="text-xl sm:text-2xl text-yellow-600 fas fa-umbrella-beach"></i>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs sm:text-sm text-gray-600">Total Lembur</p>
                    <h3 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">{{ $monthlyStats['overtime_hours'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs sm:text-sm text-purple-600">
                        <i class="mr-1 fas fa-clock"></i>
                        Jam
                    </p>
                </div>
                <div class="p-3 sm:p-4 bg-purple-100 rounded-full">
                    <i class="text-xl sm:text-2xl text-purple-600 fas fa-business-time"></i>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="mb-1 text-xs sm:text-sm text-gray-600">Pengajuan Pending</p>
                    <h3 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">{{ $monthlyStats['pending_requests'] ?? 0 }}</h3>
                    <p class="mt-2 text-xs sm:text-sm text-orange-600">
                        <i class="mr-1 fas fa-hourglass-half"></i>
                        Menunggu
                    </p>
                </div>
                <div class="p-3 sm:p-4 bg-orange-100 rounded-full">
                    <i class="text-xl sm:text-2xl text-orange-600 fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Shift -->
    <div class="p-5 sm:p-6 lg:p-8 bg-white shadow-lg rounded-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800">
                <i class="mr-2 text-green-600 fas fa-calendar-alt"></i>
                Jadwal Shift Minggu Ini
            </h3>
            <a href="{{ route('workers.schedule') }}" class="text-sm sm:text-base font-medium text-green-600 hover:text-green-700 transition-colors">
                Lihat Semua <i class="ml-1 fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="space-y-2 sm:space-y-3">
            @forelse($weeklySchedule ?? [] as $schedule)
            <div class="flex items-center justify-between p-4 {{ $schedule->date->isToday() ? 'bg-primary-50 border-l-4 border-primary-500' : 'bg-gray-50' }} rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="text-center">
                        <p class="text-xs text-gray-600">{{ $schedule->date->format('D') }}</p>
                        <p class="text-lg font-bold text-gray-800">{{ $schedule->date->format('d') }}</p>
                    </div>
                    <div class="w-px h-10 bg-gray-300"></div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $schedule->shift->name }}</p>
                        <p class="text-sm text-gray-600">
                            <i class="mr-1 far fa-clock"></i>
                            {{ $schedule->shift->start_time }} - {{ $schedule->shift->end_time }}
                        </p>
                    </div>
                </div>
                @if($schedule->date->isToday())
                <span class="px-3 py-1 text-xs font-bold text-white rounded-full bg-primary-500">
                    Hari Ini
                </span>
                @endif
            </div>
            @empty
            <div class="py-8 text-center text-gray-500">
                <i class="mb-3 text-4xl fas fa-calendar-times"></i>
                <p>Belum ada jadwal minggu ini</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Menu Cepat -->
    <div class="p-5 sm:p-6 lg:p-8 bg-white shadow-lg rounded-xl">
        <h3 class="mb-4 sm:mb-6 text-base sm:text-lg lg:text-xl font-bold text-gray-800">
            <i class="mr-2 text-yellow-500 fas fa-bolt"></i>
            Menu Cepat
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <a href="{{ route('workers.leaves.create') }}" class="flex flex-col items-center p-3 sm:p-4 transition duration-300 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 hover:shadow-lg hover:scale-105">
                <div class="p-2 sm:p-3 mb-2 text-white bg-blue-500 rounded-full">
                    <i class="text-lg sm:text-xl fas fa-calendar-plus"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-center text-gray-700">Ajukan Cuti</span>
            </a>

            <a href="{{ route('workers.overtimes.create') }}" class="flex flex-col items-center p-3 sm:p-4 transition duration-300 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 hover:shadow-lg hover:scale-105">
                <div class="p-2 sm:p-3 mb-2 text-white bg-purple-500 rounded-full">
                    <i class="text-lg sm:text-xl fas fa-clock"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-center text-gray-700">Ajukan Lembur</span>
            </a>

            <a href="{{ route('workers.documents') }}" class="flex flex-col items-center p-3 sm:p-4 transition duration-300 rounded-lg bg-gradient-to-br from-green-50 to-green-100 hover:shadow-lg hover:scale-105">
                <div class="p-2 sm:p-3 mb-2 text-white bg-green-500 rounded-full">
                    <i class="text-lg sm:text-xl fas fa-file-alt"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-center text-gray-700">Dokumen</span>
            </a>

            <a href="{{ route('workers.attendance.history') }}" class="flex flex-col items-center p-3 sm:p-4 transition duration-300 rounded-lg bg-gradient-to-br from-yellow-50 to-yellow-100 hover:shadow-lg hover:scale-105">
                <div class="p-2 sm:p-3 mb-2 text-white bg-yellow-500 rounded-full">
                    <i class="text-lg sm:text-xl fas fa-history"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-center text-gray-700">Riwayat Absen</span>
            </a>

            <a href="{{ route('workers.profile') }}" class="flex flex-col items-center p-3 sm:p-4 transition duration-300 rounded-lg bg-gradient-to-br from-red-50 to-red-100 hover:shadow-lg hover:scale-105">
                <div class="p-2 sm:p-3 mb-2 text-white bg-red-500 rounded-full">
                    <i class="text-lg sm:text-xl fas fa-money-bill-wave"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-center text-gray-700">Slip Gaji</span>
            </a>

            <a href="{{ route('workers.profile') }}" class="flex flex-col items-center p-3 sm:p-4 transition duration-300 rounded-lg bg-gradient-to-br from-indigo-50 to-indigo-100 hover:shadow-lg hover:scale-105">
                <div class="p-2 sm:p-3 mb-2 text-white bg-indigo-500 rounded-full">
                    <i class="text-lg sm:text-xl fas fa-user-edit"></i>
                </div>
                <span class="text-xs sm:text-sm font-medium text-center text-gray-700">Edit Profil</span>
            </a>
        </div>
    </div>

    <!-- Riwayat Pengajuan -->
    <div class="p-5 sm:p-6 lg:p-8 bg-white shadow-lg rounded-xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800">
                <i class="mr-2 text-green-600 fas fa-list-alt"></i>
                Riwayat Pengajuan Terakhir
            </h3>
            <a href="{{ route('workers.leaves.index') }}" class="text-sm sm:text-base font-medium text-green-600 hover:text-green-700 transition-colors">
                Lihat Semua <i class="ml-1 fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($recentRequests ?? [] as $request)
            <div class="flex items-center justify-between p-4 transition duration-200 rounded-lg bg-gray-50 hover:bg-gray-100">
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
                        <p class="text-sm font-semibold text-gray-800">{{ $request->title }}</p>
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
            <div class="py-8 text-center text-gray-500">
                <i class="mb-3 text-4xl fas fa-inbox"></i>
                <p>Belum ada riwayat pengajuan</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Info Penting -->
    <div class="p-5 sm:p-6 border-l-4 border-yellow-500 rounded-lg bg-gradient-to-r from-yellow-50 to-orange-50 shadow-md">
        <div class="flex flex-col sm:flex-row items-start space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="p-2 sm:p-3 text-white bg-yellow-500 rounded-full flex-shrink-0">
                <i class="text-lg sm:text-xl fas fa-bullhorn"></i>
            </div>
            <div class="flex-1">
                <h4 class="mb-2 text-base sm:text-lg font-bold text-gray-800">
                    <i class="mr-1 fas fa-info-circle"></i>
                    Pengumuman Penting
                </h4>
                <p class="text-sm sm:text-base leading-relaxed text-gray-700">
                    Reminder: Pastikan untuk melakukan absensi tepat waktu setiap hari. 
                    Jika ada kendala, segera hubungi bagian HRD RSUD Haji Darlan Ismail.
                </p>
                <p class="mt-2 text-xs sm:text-sm text-gray-600">
                    <i class="mr-1 far fa-calendar"></i>
                    Diperbarui: {{ \Carbon\Carbon::now()->format('d M Y') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
