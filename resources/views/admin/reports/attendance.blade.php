@extends('layouts.admin')

@section('title', 'Laporan Presensi')

@section('content')
<div class="space-y-6">
    <x-page-header title="Laporan Presensi" description="Lihat dan ekspor data presensi" icon="fas fa-calendar-check" />

    <x-card>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Dari</label>
                <input type="date" name="start_date" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Sampai</label>
                <input type="date" name="end_date" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Pegawai</label>
                <select name="worker_id" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">Semua</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ ($filters['worker_id'] ?? '') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Filter</button>
                <a href="?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'csv'])) }}" class="px-4 py-2 border rounded">Export CSV</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="text-left">
                        <th class="px-3 py-2">Pegawai</th>
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Check In</th>
                        <th class="px-3 py-2">Check Out</th>
                        <th class="px-3 py-2">Lokasi</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $a)
                        <tr>
                            <td class="px-3 py-2">{{ $a->worker->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $a->attendance_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $a->check_in?->format('H:i') ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $a->check_out?->format('H:i') ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $a->location->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $a->status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(method_exists($attendances, 'links'))
            <div class="mt-4">{{ $attendances->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
