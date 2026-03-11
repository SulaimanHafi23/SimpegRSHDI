@extends('layouts.admin')

@section('title', 'Departemen')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                <i class="fas fa-building text-green-600 mr-2"></i>
                Departemen
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola data departemen rumah sakit</p>
        </div>
        <a href="{{ route('admin.master.departments.create') }}" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
            <i class="fas fa-plus"></i>
            <span>Tambah Departemen</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.master.departments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama atau kode departemen..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
            </div>
            <div>
                <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('admin.master.departments.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 text-center text-sm">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($departments as $department)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $department->code }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $department->name }}</span>
                        </div>
                        @if($department->description)
                        <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($department->description, 80) }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end space-y-1">
                        @if($department->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Tidak Aktif
                            </span>
                        @endif
                        @if($department->requires_holiday_attendance)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-hospital mr-1"></i>Standby
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-users mr-1"></i>{{ $department->workers_count ?? 0 }} Pegawai
                    </span>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <a href="{{ route('admin.master.departments.show', $department->id) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100">
                        <i class="fas fa-eye mr-1"></i>Lihat
                    </a>
                    <a href="{{ route('admin.master.departments.edit', $department->id) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-yellow-50 text-yellow-700 rounded-lg text-xs font-medium hover:bg-yellow-100">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <form action="{{ route('admin.master.departments.destroy', $department->id) }}"
                          method="POST"
                          class="inline"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-xs font-medium hover:bg-red-100">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>Tidak ada data departemen</p>
            </div>
            @endforelse
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Departemen</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Deskripsi</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Jumlah Pegawai</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($departments as $department)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        <span class="text-xs sm:text-sm font-mono text-gray-900">{{ $department->code }}</span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $department->name }}</div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                        <div class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($department->description ?? '-', 50) }}</div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                        <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-users mr-1"></i>
                            {{ $department->workers_count ?? 0 }}
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        <x-status-pill :active="$department->is_active" />
                        @if($department->requires_holiday_attendance)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 ml-1" title="Wajib hadir saat hari libur nasional">
                                <i class="fas fa-hospital mr-1"></i>
                                <span class="hidden sm:inline">Standby</span>
                            </span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.master.departments.show', $department->id) }}"
                               class="text-blue-600 hover:text-blue-900 p-1"
                               title="Lihat Detail">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </a>
                            <a href="{{ route('admin.master.departments.edit', $department->id) }}"
                               class="text-yellow-600 hover:text-yellow-900 p-1"
                               title="Edit">
                                <i class="fas fa-edit text-xs sm:text-sm"></i>
                            </a>
                            <form action="{{ route('admin.master.departments.destroy', $department->id) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?')">
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
                        <p>Tidak ada data departemen</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($departments->hasPages())
    <div class="mt-6">
        {{ $departments->links() }}
    </div>
    @endif
</div>
@endsection
