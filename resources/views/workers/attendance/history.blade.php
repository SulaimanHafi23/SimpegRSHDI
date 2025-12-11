@extends('layouts.workers')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Riwayat Absensi</h1>
        <p class="mt-2 text-sm text-gray-600">Lihat riwayat kehadiran Anda</p>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('workers.attendance.history') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('workers.attendance.history') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($attendances ?? [] as $attendance)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($attendance->check_in)->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($attendance->check_in)->isoFormat('dddd') }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Hadir
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>Belum ada riwayat absensi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    @if(isset($attendances) && $attendances->hasPages())
    <div class="mt-4">
        {{ $attendances->links() }}
    </div>
    @endif
</div>
@endsection
