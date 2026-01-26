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
        @extends('layouts.admin')

        @section('title', 'Manajemen Absensi')

        @section('content')
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Manajemen Absensi</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">Daftar pegawai dan rekap absensi</p>
                </div>
                <form method="GET" action="" class="w-full sm:w-auto flex items-center gap-2 mt-4 sm:mt-0">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/NIP/email..." class="px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold">Cari</button>
                </form>
            </div>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Absen</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($workers as $worker)
                            @php
                                $absenCount = app('App\\Services\\Attendance\\AttendanceService')->getByWorkerId($worker->id, [])->count();
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $worker->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $worker->nip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $worker->position->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-center text-sm text-indigo-700 font-bold">{{ $absenCount }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.attendance.history', $worker->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow text-xs font-semibold mr-2">
                                        <i class="fas fa-list mr-1"></i> Detail Absensi
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endsection
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
                                    'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit', 'icon' => 'fa-medkit'],
                                    'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin', 'icon' => 'fa-info-circle'],
                                    'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti', 'icon' => 'fa-umbrella-beach'],
                                ];
                                $config = $statusConfig[$attendance->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($attendance->status), 'icon' => 'fa-question'];
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
