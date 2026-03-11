@extends('layouts.admin')

@section('title', 'Data Jabatan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Data Jabatan</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola data jabatan pegawai</p>
        </div>
        <a href="{{ route('admin.master.positions.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow transition duration-200">
            <i class="fas fa-plus mr-2"></i>
            Tambah Jabatan
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.master.positions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari jabatan..."
                   class="md:col-span-3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('admin.master.positions.index') }}" class="flex-1 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm text-center">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Jabatan</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Jumlah Pegawai</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($positions as $index => $position)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-briefcase text-blue-600 text-xs sm:text-sm"></i>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $position->name }}</div>
                                <div class="text-xs text-gray-500 md:hidden">{{ $position->workers_count ?? 0 }} pegawai</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            <i class="fas fa-users hidden sm:inline mr-1"></i>
                            {{ $position->workers_count ?? 0 }} pegawai
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-right">
                        <div class="flex justify-end space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.master.positions.show', $position->id) }}" class="p-1 text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </a>
                            <a href="{{ route('admin.master.positions.edit', $position->id) }}" class="p-1 text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-edit text-xs sm:text-sm"></i>
                            </a>
                            <form action="{{ route('admin.master.positions.destroy', $position->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash text-xs sm:text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500">Tidak ada data</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($positions->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $positions->links() }}</div>
        @endif
    </div>
</div>
@endsection
