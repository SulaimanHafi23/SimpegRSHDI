@extends('layouts.admin')

@section('title', 'Lokasi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                Lokasi
            </h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Daftar lokasi rumah sakit</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.master.locations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama atau alamat lokasi..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </div>
            <div>
                <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 text-sm">
                    <i class="fas fa-search mr-1"></i><span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('admin.master.locations.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 text-center text-sm">
                    <i class="fas fa-redo mr-1"></i><span class="hidden sm:inline">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($locations as $location)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $location->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($location->address ?? '-', 60) }}</p>
                        </div>
                    </div>
                    <div>
                        @if($location->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Tidak Aktif
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center space-x-4 text-xs text-gray-500">
                    <span><i class="fas fa-crosshairs mr-1"></i>{{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 font-medium">
                        <i class="fas fa-expand-arrows-alt mr-1"></i>{{ $location->radius }}m
                    </span>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <a href="{{ route('admin.master.locations.show', $location->id) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100">
                        <i class="fas fa-eye mr-1"></i>Lihat
                    </a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-map-marked-alt text-4xl mb-2"></i>
                <p>Tidak ada data lokasi</p>
            </div>
            @endforelse
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lokasi</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Alamat</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Koordinat</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Radius</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($locations as $location)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mr-3">
                                <i class="fas fa-map-marker-alt text-blue-600 text-xs sm:text-sm"></i>
                            </div>
                            <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $location->name }}</div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                        <div class="text-sm text-gray-600">{{ Str::limit($location->address ?? '-', 50) }}</div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                        <div class="text-xs text-gray-600 font-mono">
                            <div>{{ number_format($location->latitude, 6) }}</div>
                            <div>{{ number_format($location->longitude, 6) }}</div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                        <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800">
                            <i class="fas fa-expand-arrows-alt mr-1"></i>
                            {{ $location->radius }}m
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                        @if($location->is_active)
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
                            <a href="{{ route('admin.master.locations.show', $location->id) }}"
                               class="text-blue-600 hover:text-blue-900 p-1"
                               title="Lihat Detail">
                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-map-marked-alt text-4xl mb-2"></i>
                        <p>Tidak ada data lokasi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($locations->hasPages())
    <div class="mt-6">
        {{ $locations->links() }}
    </div>
    @endif
</div>
@endsection
