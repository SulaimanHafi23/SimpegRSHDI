@extends('layouts.admin')

@section('title', 'Data Lokasi')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Data Lokasi</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">Kelola data lokasi kerja</p>
        </div>
        <a href="{{ route('admin.master.locations.create') }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow text-sm">
            <i class="fas fa-plus mr-2"></i>Tambah Lokasi
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input type="text" name="search" value="{{ $keyword ?? '' }}" placeholder="Cari lokasi..." 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Cari</span>
                </button>
                @if($keyword ?? false)
                <a href="{{ route('admin.master.locations.index') }}" class="flex-1 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-center text-sm">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">No</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lokasi</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Koordinat</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Radius</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Geofence</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($locations as $index => $location)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm hidden lg:table-cell">{{ $locations->firstItem() + $index }}</td>
                    <td class="px-3 sm:px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-green-600 text-xs sm:text-sm"></i>
                            </div>
                            <div class="ml-2 sm:ml-3">
                                <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $location->name }}</div>
                                @if($location->address)
                                <div class="text-xs text-gray-500 mt-0.5 hidden sm:block">{{ Str::limit($location->address, 30) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                        <div class="text-xs text-gray-600 font-mono">
                            <div>{{ number_format($location->latitude, 6) }}</div>
                            <div>{{ number_format($location->longitude, 6) }}</div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm hidden lg:table-cell">
                        <span class="text-gray-900 font-medium">{{ $location->radius }}m</span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm hidden md:table-cell">
                        @if($location->enforce_geofence)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            <i class="fas fa-shield-alt mr-1"></i>Aktif
                        </span>
                        @else
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                            Nonaktif
                        </span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm">
                        @if($location->is_active)
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                            <i class="fas fa-check hidden sm:inline mr-1"></i>
                            <span class="hidden sm:inline">Aktif</span>
                            <i class="fas fa-check sm:hidden"></i>
                        </span>
                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                            <i class="fas fa-times hidden sm:inline mr-1"></i>
                            <span class="hidden sm:inline">Nonaktif</span>
                            <i class="fas fa-times sm:hidden"></i>
                        </span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-right">
                        <div class="flex justify-end items-center space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.master.locations.show', $location->id) }}" 
                               class="text-blue-600 hover:text-blue-900 p-1" 
                               title="Detail">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </a>
                            <a href="{{ route('admin.master.locations.edit', $location->id) }}" 
                               class="text-yellow-600 hover:text-yellow-900 p-1"
                               title="Edit">
                                <i class="fas fa-edit text-xs sm:text-sm"></i>
                            </a>
                            <form action="{{ route('admin.master.locations.destroy', $location->id) }}" 
                                  method="POST" 
                                  class="inline" 
                                  onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
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
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-map-marked-alt text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg">Tidak ada data lokasi</p>
                            <p class="text-gray-400 text-sm mt-1">Silakan tambahkan lokasi baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($locations->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $locations->links() }}</div>
        @endif
    </div>
</div>
@endsection
