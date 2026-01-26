@extends('layouts.admin')

@section('title', 'Daftar Pegawai - Manajemen Absensi')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Daftar Pegawai</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Pilih pegawai untuk melihat riwayat absensi</p>
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
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hadir</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Terlambat</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tidak Hadir</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Izin</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sakit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cuti</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($workers as $worker)
                    @php
                        $month = now()->month;
                        $year = now()->year;
                        $summary = app('App\\Services\\Attendance\\AttendanceService')->getMonthlyReport($worker->id, $month, $year);
                        $hadir = $summary->where('status', 'present')->count();
                        $terlambat = $summary->where('status', 'late')->count();
                        $tidakHadir = $summary->whereIn('status', ['absent', 'sick', 'permission', 'leave'])->count();
                        $izin = $summary->where('status', 'permission')->count();
                        $sakit = $summary->where('status', 'sick')->count();
                        $cuti = $summary->where('status', 'leave')->count();
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $worker->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $worker->nip }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $worker->position->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center text-sm text-green-700 font-bold">{{ $hadir }}</td>
                        <td class="px-6 py-4 text-center text-sm text-yellow-700 font-bold">{{ $terlambat }}</td>
                        <td class="px-6 py-4 text-center text-sm text-red-700 font-bold">{{ $tidakHadir }}</td>
                        <td class="px-6 py-4 text-center text-sm text-blue-700 font-bold">{{ $izin }}</td>
                        <td class="px-6 py-4 text-center text-sm text-orange-700 font-bold">{{ $sakit }}</td>
                        <td class="px-6 py-4 text-center text-sm text-purple-700 font-bold">{{ $cuti }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.attendance.history', $worker->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow text-xs font-semibold mr-2">
                                <i class="fas fa-list mr-1"></i> Riwayat Absensi
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
