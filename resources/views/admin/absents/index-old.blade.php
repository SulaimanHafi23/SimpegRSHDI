@extends('layouts.admin')

@section('title', 'Data Absensi')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Absensi</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola data kehadiran pegawai</p>
        </div>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.absents.report') }}" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                <i class="fas fa-chart-bar mr-2"></i>Laporan
            </a>
            <a href="{{ route('admin.absents.create') }}" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                <i class="fas fa-plus mr-2"></i>Absen Manual
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Hadir</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-600">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-plane text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Cuti</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-600">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Terlambat</p>
                    <p class="text-xl sm:text-2xl font-bold text-yellow-600">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-user-md text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Sakit</p>
                    <p class="text-xl sm:text-2xl font-bold text-purple-600">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Alpha</p>
                    <p class="text-2xl font-bold text-red-600">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input type="text" name="search" placeholder="Cari nama pegawai..." 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <input type="date" name="start_date" 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <input type="date" name="end_date" 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Status</option>
                <option value="Present">Hadir</option>
                <option value="Late">Terlambat</option>
                <option value="Leave">Cuti</option>
                <option value="Sick">Sakit</option>
                <option value="Absent">Alpha</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pulang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-calendar-check text-5xl mb-4 text-gray-400"></i>
                        <p>Data absensi akan ditampilkan di sini</p>
                        <p class="text-sm mt-2">Gunakan filter di atas untuk melihat data absensi</p>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
