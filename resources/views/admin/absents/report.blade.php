@extends('layouts.admin')

@section('title', 'Laporan Absensi')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Absensi</h1>
            <p class="text-sm text-gray-600 mt-1">Rekap kehadiran pegawai</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
            <a href="#" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="type" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="daily">Harian</option>
                <option value="monthly">Bulanan</option>
                <option value="range">Rentang Tanggal</option>
            </select>
            <input type="date" name="start_date" 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <input type="date" name="end_date" 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                <i class="fas fa-search mr-2"></i>Tampilkan
            </button>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total Hadir</p>
                    <p class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-check-circle text-4xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Terlambat</p>
                    <p class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-clock text-4xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Cuti</p>
                    <p class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-plane text-4xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Sakit</p>
                    <p class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-user-md text-4xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Alpha</p>
                    <p class="text-3xl font-bold">0</p>
                </div>
                <i class="fas fa-times-circle text-4xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Rekap Kehadiran</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hadir</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Terlambat</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cuti</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sakit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Alpha</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">%</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-chart-bar text-5xl mb-4 text-gray-400"></i>
                            <p>Pilih periode untuk melihat laporan absensi</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Detail Table (Optional) -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Detail Harian</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                        <!-- Days will be generated dynamically -->
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500">1</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500">2</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500">3</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500">...</th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500">31</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="33" class="px-6 py-12 text-center text-gray-500">
                            <p>Detail harian akan ditampilkan di sini</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Keterangan</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="flex items-center">
                <span class="w-6 h-6 bg-green-500 rounded flex items-center justify-center text-white text-xs font-bold mr-2">H</span>
                <span class="text-sm text-gray-700">Hadir</span>
            </div>
            <div class="flex items-center">
                <span class="w-6 h-6 bg-yellow-500 rounded flex items-center justify-center text-white text-xs font-bold mr-2">T</span>
                <span class="text-sm text-gray-700">Terlambat</span>
            </div>
            <div class="flex items-center">
                <span class="w-6 h-6 bg-blue-500 rounded flex items-center justify-center text-white text-xs font-bold mr-2">C</span>
                <span class="text-sm text-gray-700">Cuti</span>
            </div>
            <div class="flex items-center">
                <span class="w-6 h-6 bg-purple-500 rounded flex items-center justify-center text-white text-xs font-bold mr-2">S</span>
                <span class="text-sm text-gray-700">Sakit</span>
            </div>
            <div class="flex items-center">
                <span class="w-6 h-6 bg-red-500 rounded flex items-center justify-center text-white text-xs font-bold mr-2">A</span>
                <span class="text-sm text-gray-700">Alpha</span>
            </div>
        </div>
    </div>
</div>
@endsection
