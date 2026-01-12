@extends('layouts.employee')

@section('title', 'Perjalanan Dinas Saya')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Perjalanan Dinas</h1>
            <p class="text-gray-600 mt-1">Kelola perjalanan dinas Anda</p>
        </div>
        <a href="{{ route('employee.business-trips.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-plus mr-2"></i>Ajukan Baru
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-filter text-blue-600"></i>Filter
            </h3>
            <button @click="showFilters = !showFilters" class="text-gray-600 hover:text-gray-800">
                <i class="fas" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('employee.business-trips.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4" x-show="showFilters" x-cloak x-transition>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('employee.business-trips.index') }}" 
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Mobile Cards -->
    <div class="sm:hidden space-y-4">
        @forelse($businessTrips as $trip)
            <div class="bg-white rounded-lg shadow p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-500">Tujuan</p>
                        <p class="font-semibold text-gray-900">{{ $trip->destination }}</p>
                        <p class="text-sm text-gray-700">{{ Str::limit($trip->purpose, 80) }}</p>
                    </div>
                    @if($trip->status === 'pending')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Pending</span>
                    @elseif($trip->status === 'approved')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Approved</span>
                    @elseif($trip->status === 'rejected')
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i>Rejected</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-ban mr-1"></i>Cancelled</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                    <div>
                        <p class="text-xs text-gray-500">Tanggal</p>
                        <p class="font-medium">{{ $trip->start_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Durasi</p>
                        <p class="font-medium">{{ $trip->start_date->diffInDays($trip->end_date) + 1 }} hari</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Estimasi Biaya</p>
                        <p class="font-medium">Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div>
                    <a href="{{ route('employee.business-trips.show', $trip->id) }}" 
                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-eye mr-2"></i>Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <i class="fas fa-briefcase text-gray-300 text-5xl mb-3"></i>
                <p class="text-gray-600 font-medium">Belum ada perjalanan dinas</p>
                <p class="text-gray-400 text-sm">Ajukan perjalanan dinas pertama Anda</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table -->
    <div class="hidden sm:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Biaya</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($businessTrips as $trip)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $trip->destination }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($trip->purpose, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $trip->start_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $trip->start_date->diffInDays($trip->end_date) + 1 }}
                            </span>
                            <span class="text-xs text-gray-500">hari</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($trip->status === 'pending')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            @elseif($trip->status === 'approved')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Approved
                                </span>
                            @elseif($trip->status === 'rejected')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Rejected
                                </span>
                            @elseif($trip->status === 'cancelled')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-ban mr-1"></i>Cancelled
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('employee.business-trips.show', $trip->id) }}" 
                               class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-eye mr-2"></i>Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-briefcase text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Belum ada perjalanan dinas</p>
                                <p class="text-gray-400 text-sm">Ajukan perjalanan dinas pertama Anda</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($businessTrips->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            {{ $businessTrips->links() }}
        </div>
        @endif
    </div>
</div>
@endsection