@extends('layouts.employee')

@section('title', 'Perjalanan Dinas Saya')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    <i class="fas fa-plane-departure text-blue-600 mr-2"></i>
                    Perjalanan Dinas Saya
                </h1>
                <p class="text-gray-600 mt-2">Kelola perjalanan dinas Anda</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-export-buttons :route="route('employee.business-trips.export')" title="Export Perjalanan Dinas">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                </x-export-buttons>
                <a href="{{ route('employee.business-trips.create') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-150">
                    <i class="fas fa-plus-circle mr-2"></i>
                    <span class="hidden sm:inline">Ajukan Baru</span>
                    <span class="sm:hidden">Ajukan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $summary['total'] }}</p>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <i class="fas fa-list text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['rejected'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-filter mr-2 text-blue-600"></i>
                Pencarian & Filter Perjalanan Dinas
            </h3>
            <button @click="showFilters = !showFilters" class="text-gray-600 hover:text-gray-800">
                <i class="fas" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('employee.business-trips.index') }}" x-show="showFilters" x-cloak x-transition>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>
                    Terapkan Filter
                </button>
                <a href="{{ route('employee.business-trips.index') }}"
                   class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition duration-150 flex items-center justify-center">
                    <i class="fas fa-redo mr-2"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Mobile Cards -->
    <div class="sm:hidden space-y-4">
        @forelse($businessTrips as $index => $trip)
            @php
                $statusBadges = [
                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                    'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                ];
                $badge = $statusBadges[$trip->status] ?? ['variant' => 'secondary', 'label' => ucfirst($trip->status)];
            @endphp
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-3">
                    <div>
                        <span class="text-xs text-gray-500 font-medium">#{{ $businessTrips->firstItem() + $index }}</span>
                        <div class="text-xs text-gray-400 mt-0.5 font-medium">
                            <i class="far fa-clock mr-1"></i>{{ $trip->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                    <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 flex-shrink-0">
                            <i class="fas fa-plane-departure text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400 block mb-0.5">Tujuan Perjalanan</span>
                            <span class="text-sm font-bold text-gray-900 leading-tight block truncate">{{ $trip->destination }}</span>
                            <span class="text-xs text-gray-500 line-clamp-1 italic">"{{ $trip->purpose }}"</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 grid grid-cols-2 gap-4 border border-gray-100">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i>Periode
                            </span>
                            <span class="text-xs font-bold text-indigo-700 block">{{ $trip->start_date->format('d M') }} - {{ $trip->end_date->format('d M Y') }}</span>
                            <span class="text-[10px] text-gray-400 font-medium">{{ $trip->duration_label }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                <i class="fas fa-money-bill-wave text-emerald-400 mr-1"></i>Estimasi Biaya
                            </span>
                            <span class="text-xs font-bold text-emerald-700 block">Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-gray-400 font-medium text-xs italic">Sesuai pengajuan</span>
                        </div>
                    </div>

                    @if($trip->status === 'rejected' && $trip->rejection_reason)
                        <div class="bg-red-50 rounded-lg p-3 border border-red-100">
                            <span class="text-[10px] uppercase tracking-wider font-bold text-red-500 block mb-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>Alasan Penolakan
                            </span>
                            <p class="text-xs text-red-700 italic leading-relaxed line-clamp-2">"{{ $trip->rejection_reason }}"</p>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                    @if($trip->status === 'pending')
                        <form action="{{ route('employee.business-trips.cancel', $trip->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="flex items-center justify-center w-10 h-10 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition active:scale-95" 
                                    title="Batalkan"
                                    onclick="event.preventDefault(); showConfirmAlert('Batalkan Permohonan?', 'Yakin ingin membatalkan permohonan ini?', () => this.closest('form').submit());">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('employee.business-trips.show', $trip->id) }}" 
                       class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 transition active:scale-95"
                       title="Periksa Detail">
                        <i class="fas fa-eye text-sm"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow p-10 text-center border border-dashed border-gray-300">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-briefcase text-gray-300 text-3xl"></i>
                </div>
                <p class="text-gray-600 font-bold">Belum Ada Perjalanan Dinas</p>
                <p class="text-gray-400 text-sm mt-1">Ajukan perjalanan dinas pertama Anda dengan tombol di atas.</p>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table -->
    <div class="hidden sm:block bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-map-marker-alt mr-1"></i>Tujuan
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="far fa-calendar mr-1"></i>Tanggal
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="far fa-clock mr-1"></i>Durasi
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-money-bill-wave mr-1"></i>Estimasi Biaya
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-info-circle mr-1"></i>Status
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-cog mr-1"></i>Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($businessTrips as $trip)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <p class="font-bold">{{ $trip->destination }}</p>
                                <p class="text-xs text-gray-500 italic">{{ \Illuminate\Support\Str::limit($trip->purpose, 40) }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $trip->start_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $trip->duration_label }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusBadges = [
                                        'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                        'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                        'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                        'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                                    ];
                                    $badge = $statusBadges[$trip->status] ?? ['variant' => 'secondary', 'label' => ucfirst($trip->status)];
                                @endphp
                                <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('employee.business-trips.show', $trip->id) }}"
                                       class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if($trip->status === 'pending')
                                        <form action="{{ route('employee.business-trips.cancel', $trip->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Batalkan"
                                                    onclick="event.preventDefault(); showConfirmAlert('Batalkan Permohonan?', 'Yakin ingin membatalkan permohonan ini?', () => this.closest('form').submit());">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-briefcase text-gray-300 text-6xl mb-4"></i>
                                    <p class="text-lg font-medium text-gray-500 mb-2">Belum ada perjalanan dinas</p>
                                    <p class="text-sm text-gray-400">Ajukan perjalanan dinas pertama Anda untuk melihat data di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($businessTrips->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $businessTrips->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
