@extends('layouts.admin')

@section('title', 'Laporan Kehadiran')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Laporan Kehadiran</h1>
        <p class="text-gray-600">Ringkasan kehadiran berdasarkan rentang tanggal dan filter departemen.</p>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('reports.attendance') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $filters['date_to'] ?? now()->endOfMonth()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm text-gray-500 mb-1">Karyawan</label>
                <select name="worker_id" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Karyawan</option>
                    @foreach($filters['workers'] ?? [] as $w)
                        <option value="{{ $w->id }}" {{ (request('worker_id') == $w->id) ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filter</button>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Kehadiran</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $attendances->total() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Terlambat</p>
            <p class="text-2xl font-bold text-yellow-600 mt-2">{{ $filters['late'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Absent (estimated)</p>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ $filters['absent'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Attendance Rate</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $filters['attendance_rate'] ?? 'N/A' }}%</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($attendances as $att)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $att->attendance_date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $att->worker->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $att->shift->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ optional($att->check_in)->format('H:i') ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ optional($att->check_out)->format('H:i') ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $att->is_late ? 'Late' : 'On Time' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($attendances->hasPages())
    <div class="mt-4">{{ $attendances->links() }}</div>
    @endif
</div>
@endsection
