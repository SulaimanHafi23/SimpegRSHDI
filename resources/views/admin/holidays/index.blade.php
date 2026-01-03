@extends('layouts.admin')

@section('title', 'Kelola Libur Nasional')

@section('content')
<div x-data="{ showFilters: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-calendar-day mr-3 text-green-600"></i>
                Kelola Libur Nasional
            </h1>
            <p class="mt-1 text-sm text-gray-600">Kelola data libur nasional Indonesia untuk kalender karyawan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.holidays.auto-generate') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-magic mr-2"></i>
                Auto Generate
            </a>
            <a href="{{ route('admin.holidays.bulk-create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-layer-group mr-2"></i>
                Tambah Bulk
            </a>
            <a href="{{ route('admin.holidays.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Tambah Libur
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <button @click="showFilters = !showFilters" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <i class="fas fa-filter text-green-600"></i>
                <span class="font-semibold text-gray-900">Filter & Pencarian</span>
            </div>
            <i class="fas fa-chevron-down transform transition-transform" :class="{ 'rotate-180': showFilters }"></i>
        </button>

        <div x-show="showFilters" x-collapse class="border-t border-gray-200">
            <form method="GET" action="{{ route('admin.holidays.index') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                        <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>
                        Terapkan
                    </button>
                    <a href="{{ route('admin.holidays.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                        <i class="fas fa-redo mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-green-600 to-green-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama Libur</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($holidays as $holiday)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration + ($holidays->currentPage() - 1) * $holidays->perPage() }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $holiday->date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $holiday->date->isoFormat('dddd') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-2">🇮🇩</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $holiday->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $holiday->description }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.holidays.edit', $holiday->id) }}" class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus libur nasional ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <i class="fas fa-calendar-times text-gray-400 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Belum ada data libur nasional</p>
                                <p class="text-gray-400 text-sm mt-2">Tambahkan libur nasional untuk tahun ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($holidays->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $holidays->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
