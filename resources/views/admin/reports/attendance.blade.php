@extends('layouts.admin')

@section('title', 'Laporan Presensi')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-calendar-check text-blue-600 mr-3"></i>
            Laporan Presensi
        </h1>
        <p class="text-gray-600 mt-1">Lihat dan ekspor data presensi pegawai</p>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-green-600 mr-2"></i>
            Filter Data
        </h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                    Dari
                </label>
                <input type="date"
                       name="start_date"
                       value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                    Sampai
                </label>
                <input type="date"
                       name="end_date"
                       value="{{ $filters['date_to'] ?? '' }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user text-gray-500 mr-1"></i>
                    Pegawai
                </label>
                <select name="worker_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Pegawai</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ ($filters['worker_id'] ?? '') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                        class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition duration-200 flex items-center justify-center text-sm">
                    <i class="fas fa-search mr-1"></i>
                    <span class="hidden sm:inline">Filter</span>
                </button>
                <a href="?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'csv'])) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition duration-200 flex items-center text-sm">
                    <i class="fas fa-file-csv mr-1"></i>
                    <span class="hidden sm:inline">CSV</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Results Card -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Summary Bar -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <i class="fas fa-list text-blue-600 mr-2"></i>
                        <span class="text-sm font-medium text-gray-700">
                            Total: <span class="text-blue-600 font-bold">{{ $attendances->total() ?? 0 }}</span> data
                        </span>
                    </div>
                    @if(isset($filters['date_from']) && isset($filters['date_to']))
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-calendar-day text-gray-500 mr-2"></i>
                        {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-user mr-1 hidden sm:inline"></i> Pegawai
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            <i class="fas fa-calendar mr-1"></i> Tanggal
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            <i class="fas fa-sign-in-alt mr-1"></i> Check In
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            <i class="fas fa-sign-out-alt mr-1"></i> Check Out
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            <i class="fas fa-map-marker-alt mr-1"></i> Lokasi
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-info-circle mr-1 hidden sm:inline"></i> Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $a)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-3 sm:px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600 text-xs sm:text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $a->worker->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $a->worker->nip ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 md:hidden">{{ $a->attendance_date ? \Carbon\Carbon::parse($a->attendance_date)->format('d M') : '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                                <div class="text-xs sm:text-sm text-gray-900">
                                    {{ $a->attendance_date ? \Carbon\Carbon::parse($a->attendance_date)->format('d M Y') : '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $a->attendance_date ? \Carbon\Carbon::parse($a->attendance_date)->format('l') : '-' }}
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                                @if($a->check_in)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-clock hidden sm:inline mr-1"></i>
                                        {{ \Carbon\Carbon::parse($a->check_in)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-xs sm:text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                                @if($a->check_out)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-clock hidden sm:inline mr-1"></i>
                                        {{ \Carbon\Carbon::parse($a->check_out)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-xs sm:text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                                <div class="text-xs sm:text-sm text-gray-900 flex items-center">
                                    <i class="fas fa-map-pin text-gray-400 mr-2 hidden sm:inline"></i>
                                    {{ $a->location->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-4">
                                @php
                                    $statusColors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'excused' => 'bg-blue-100 text-blue-800',
                                    ];
                                    $statusIcons = [
                                        'present' => 'fa-check-circle',
                                        'late' => 'fa-clock',
                                        'absent' => 'fa-times-circle',
                                        'excused' => 'fa-info-circle',
                                    ];
                                    $statusLabels = [
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        'absent' => 'Tidak Hadir',
                                        'excused' => 'Izin',
                                    ];
                                    $status = strtolower($a->status ?? 'present');
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas {{ $statusIcons[$status] ?? 'fa-question' }} hidden sm:inline mr-1"></i>
                                    <span class="hidden sm:inline">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                                    <i class="fas {{ $statusIcons[$status] ?? 'fa-question' }} sm:hidden"></i>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                    <p class="text-gray-500 text-lg font-medium">Tidak ada data presensi</p>
                                    <p class="text-gray-400 text-sm mt-1">Silakan pilih filter untuk menampilkan data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($attendances, 'links') && $attendances->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
