@extends('layouts.admin')

@section('title', 'Manajemen Absensi')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Manajemen Absensi</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola data absensi pegawai</p>
        </div>
        <a href="{{ route('admin.attendance.create') }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Input Absensi
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filter Section -->
    <div x-data="{ showFilters: false }" class="mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            {{-- Filter Header --}}
            <button @click="showFilters = !showFilters" 
                    class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-filter text-indigo-600"></i>
                    <span class="font-semibold text-gray-900">Filter & Pencarian</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform" 
                   :class="{ 'rotate-180': showFilters }"></i>
            </button>

            {{-- Filter Form --}}
            <div x-show="showFilters" 
                 x-collapse 
                 class="border-t border-gray-200">
                <form method="GET" action="{{ route('admin.attendance.index') }}" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                            <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Pegawai</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ ($filters['worker_id'] ?? '') == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="present" {{ ($filters['status'] ?? '') == 'present' ? 'selected' : '' }}>Hadir</option>
                                <option value="late" {{ ($filters['status'] ?? '') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                <option value="absent" {{ ($filters['status'] ?? '') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                <option value="permission" {{ ($filters['status'] ?? '') == 'permission' ? 'selected' : '' }}>Izin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md transition duration-150 flex items-center justify-center text-sm">
                            <i class="fas fa-search mr-1"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
                        <a href="{{ route('admin.attendance.index') }}" 
                           class="flex-1 sm:flex-none px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg shadow-md transition duration-150 flex items-center justify-center text-sm">
                            <i class="fas fa-redo mr-1"></i>
                            <span class="hidden sm:inline">Reset</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pegawai
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Tanggal
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Check In
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Check Out
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Lokasi
                        </th>
                        <th scope="col" class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 sm:px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                    @if($attendance->worker->photo_url && Storage::disk('public')->exists($attendance->worker->photo_url))
                                        <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover" 
                                             src="{{ asset('storage/' . $attendance->worker->photo_url) }}" 
                                             alt="{{ $attendance->worker->name }}">
                                    @else
                                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold text-xs sm:text-sm">
                                            {{ substr($attendance->worker->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-3 sm:ml-4">
                                    <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $attendance->worker->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attendance->worker->nip ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 md:hidden">{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 hidden md:table-cell">
                            <div class="text-xs sm:text-sm text-gray-900">{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                            <div class="text-xs sm:text-sm text-gray-900">{{ $attendance->check_in?->format('H:i') ?? '-' }}</div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                            <div class="text-xs sm:text-sm text-gray-900">{{ $attendance->check_out?->format('H:i') ?? '-' }}</div>
                        </td>
                        <td class="px-3 sm:px-6 py-4">
                            @php
                                $statusConfig = [
                                    'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir', 'icon' => 'fa-check'],
                                    'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat', 'icon' => 'fa-clock'],
                                    'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir', 'icon' => 'fa-times'],
                                    'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin', 'icon' => 'fa-info-circle'],
                                ];
                                $config = $statusConfig[$attendance->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $attendance->status, 'icon' => 'fa-question'];
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                <i class="fas {{ $config['icon'] }} hidden sm:inline mr-1"></i>
                                <span class="hidden sm:inline">{{ $config['label'] }}</span>
                                <i class="fas {{ $config['icon'] }} sm:hidden"></i>
                            </span>
                        </td>
                        <td class="px-3 sm:px-6 py-4 hidden lg:table-cell">
                            <div class="text-xs sm:text-sm text-gray-500">{{ $attendance->location->name ?? '-' }}</div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-right">
                            <div class="flex justify-end space-x-1 sm:space-x-2">
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}" 
                                   class="p-1 text-blue-600 hover:text-blue-900" title="View">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.attendance.edit', $attendance->id) }}" 
                                   class="p-1 text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.attendance.destroy', $attendance->id) }}" 
                                      method="POST" 
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-red-600 hover:text-red-900" title="Delete">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data absensi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
