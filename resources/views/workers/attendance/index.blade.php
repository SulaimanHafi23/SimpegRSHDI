@extends('layouts.workers')

@section('title', 'Absensi')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">Absensi</h1>
        <p class="mt-2 text-sm sm:text-base text-gray-600">Kelola absensi harian Anda</p>
    </div>

    <!-- Today's Attendance Card -->
    <div class="bg-white rounded-lg shadow-lg p-5 sm:p-6 lg:p-8">
        <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-4">Absensi Hari Ini</h2>
        <p class="text-xs sm:text-sm text-gray-600 mb-4">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>

        @if($todayAttendance)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border-l-4 border-green-500 bg-green-50 p-4 sm:p-5 rounded-lg">
                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Check In</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">{{ $todayAttendance->check_in?->format('H:i') ?? '-' }}</p>
                </div>
                <div class="border-l-4 border-blue-500 bg-blue-50 p-4 sm:p-5 rounded-lg">
                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Check Out</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">{{ $todayAttendance->check_out?->format('H:i') ?? 'Belum Check Out' }}</p>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-exclamation-circle text-3xl sm:text-4xl lg:text-5xl text-yellow-500 mb-4"></i>
                <p class="text-sm sm:text-base text-gray-600 mb-4">Anda belum melakukan absensi hari ini</p>
                <button class="px-6 py-3 sm:px-8 sm:py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-camera mr-2"></i>Absen Sekarang
                </button>
            </div>
        @endif
    </div>

    <!-- Monthly Stats -->
    <div class="bg-white rounded-lg shadow-lg p-5 sm:p-6 lg:p-8">
        <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-800 mb-4">Statistik Bulan Ini</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center p-4 sm:p-5 bg-gray-50 rounded-lg hover:shadow-md transition-shadow">
                <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-green-600">{{ $monthlyStats ?? 0 }}</p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Hari Hadir</p>
            </div>
            <div class="text-center p-4 sm:p-5 bg-gray-50 rounded-lg hover:shadow-md transition-shadow">
                <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-600">0</p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Terlambat</p>
            </div>
            <div class="text-center p-4 sm:p-5 bg-gray-50 rounded-lg hover:shadow-md transition-shadow">
                <p class="text-2xl sm:text-3xl lg:text-4xl font-bold text-red-600">0</p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Tidak Hadir</p>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ route('workers.attendance.history') }}" class="text-sm sm:text-base text-green-600 hover:text-green-700 font-medium transition-colors">
                Lihat Riwayat Lengkap <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>
@endsection
