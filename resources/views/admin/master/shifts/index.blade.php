@extends('layouts.admin')

@section('title', 'Shift')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                <i class="fas fa-clock text-green-600 mr-2"></i>
                Shift
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola data shift kerja</p>
        </div>
        <a href="{{ route('admin.master.shifts.create') }}" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
            <i class="fas fa-plus"></i>
            <span>Tambah Shift</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.master.shifts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama shift..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Cari</span>
                </button>
                <a href="{{ route('admin.master.shifts.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 text-center text-sm">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
        @forelse($shifts as $shift)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $shift->name }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            {{ number_format($shift->total_hours, 2) }} jam
                        </p>
                    </div>
                    <div>
                        @if($shift->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Tidak Aktif</span>
                        @endif
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-end space-x-3 text-sm">
                    <a href="{{ route('admin.master.shifts.show', $shift->id) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.master.shifts.edit', $shift->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.master.shifts.destroy', $shift->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow px-6 py-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Tidak ada data shift</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Shift</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Jam Masuk</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Jam Keluar</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Durasi</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($shifts as $shift)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden md:table-cell">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden md:table-cell">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            {{ number_format($shift->total_hours, 2) }} jam
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        @if($shift->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle hidden sm:inline mr-1"></i>
                                <span class="hidden sm:inline">Aktif</span>
                                <i class="fas fa-check sm:hidden"></i>
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle hidden sm:inline mr-1"></i>
                                <span class="hidden sm:inline">Tidak Aktif</span>
                                <i class="fas fa-times sm:hidden"></i>
                            </span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.master.shifts.show', $shift->id) }}"
                               class="text-blue-600 hover:text-blue-900 p-1"
                               title="Lihat Detail">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </a>
                            <a href="{{ route('admin.master.shifts.edit', $shift->id) }}"
                               class="text-yellow-600 hover:text-yellow-900 p-1"
                               title="Edit">
                                <i class="fas fa-edit text-xs sm:text-sm"></i>
                            </a>
                            <form action="{{ route('admin.master.shifts.destroy', $shift->id) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-900 p-1"
                                        title="Hapus">
                                    <i class="fas fa-trash text-xs sm:text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Tidak ada data shift</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($shifts->hasPages())
    <div class="mt-6">
        {{ $shifts->links() }}
    </div>
    @endif
</div>
@endsection
