@extends('layouts.admin')

@section('title', 'Data Gaji Pegawai')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Gaji Pegawai</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola data gaji dan tunjangan pegawai</p>
        </div>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.salaries.generate') }}" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                <i class="fas fa-calculator mr-2"></i>Generate Gaji
            </a>
            <a href="{{ route('admin.salaries.create') }}" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                <i class="fas fa-plus mr-2"></i>Tambah Gaji
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" placeholder="Cari pegawai..." 
                   class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <select name="month" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Bulan</option>
                @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                @endfor
            </select>
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tunjangan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Potongan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-money-bill-wave text-5xl mb-4 text-gray-400"></i>
                        <p>Belum ada data gaji</p>
                        <p class="text-sm mt-2">Klik "Generate Gaji" untuk membuat data gaji pegawai</p>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
