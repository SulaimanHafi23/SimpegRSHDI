@extends('layouts.admin')

@section('title', 'Riwayat Absensi Pegawai')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Riwayat Absensi: {{ $worker->name }}</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">NIP: {{ $worker->nip }} | Jabatan: {{ $worker->position->name ?? '-' }}</p>
        </div>
        <a href="{{ route('admin.attendance.history.export', $worker->id) }}?date_from={{ request('date_from') }}&date_to={{ request('date_to') }}&status={{ request('status') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </a>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendances as $attendance)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->check_in?->format('H:i') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->check_out?->format('H:i') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->location->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
