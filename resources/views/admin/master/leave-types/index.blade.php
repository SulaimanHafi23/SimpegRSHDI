@extends('layouts.admin')

@section('title', 'Tipe Cuti')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                Tipe Cuti
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola jenis-jenis cuti</p>
        </div>
        <a href="{{ route('admin.master.leave-types.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg space-x-2 transition duration-200">
            <i class="fas fa-plus"></i>
            <span>Tambah Tipe Cuti</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.master.leave-types.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Cari nama tipe cuti..." 
                   class="md:col-span-3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('admin.master.leave-types.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 text-sm text-center">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Tipe</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Kode</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Max Hari</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Perlu Approval</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($leaveTypes as $type)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4">
                        <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $type->name }}</div>
                        <div class="text-xs text-gray-500 md:hidden mt-1">
                            {{ $type->max_days_per_year ?? '∞' }} hari
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                        <span class="text-xs sm:text-sm font-mono text-gray-600">{{ $type->code ?? '-' }}</span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-calendar-day hidden sm:inline mr-1"></i>
                            {{ $type->max_days_per_year ?? '∞' }} hari
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                        @if($type->requires_approval ?? true)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-check-circle mr-1"></i>Ya
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-times-circle mr-1"></i>Tidak
                            </span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-6 py-4">
                        <x-status-pill :active="$type->is_active" />
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-right">
                        <div class="flex justify-end space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.master.leave-types.show', $type->id) }}" 
                               class="p-1 text-blue-600 hover:text-blue-900" 
                               title="Lihat Detail">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </a>
                            <a href="{{ route('admin.master.leave-types.edit', $type->id) }}" 
                               class="p-1 text-yellow-600 hover:text-yellow-900" 
                               title="Edit">
                                <i class="fas fa-edit text-xs sm:text-sm"></i>
                            </a>
                            <form action="{{ route('admin.master.leave-types.destroy', $type->id) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe cuti ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-1 text-red-600 hover:text-red-900" 
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
                        <p>Tidak ada data tipe cuti</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leaveTypes->hasPages())
    <div class="mt-6">
        {{ $leaveTypes->links() }}
    </div>
    @endif
</div>
@endsection
