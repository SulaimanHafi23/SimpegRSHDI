@extends('layouts.admin')

@section('title', isset($report) ? 'Laporan Absensi Bulanan' : 'Laporan Absensi Harian')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Laporan Absensi"
        description="Laporan absensi pegawai (harian / bulanan)"
        icon="fas fa-chart-bar">
        <x-slot:actions>
            {{-- Export placeholder --}}
            <x-button variant="secondary" icon="fas fa-file-export" onclick="alert('Export belum tersedia')">Export</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <form method="GET" action="{{ route('admin.attendance.report.monthly') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                <select name="worker_id" class="w-full px-3 py-2 border rounded">
                    <option value="">Semua Pegawai</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ (request('worker_id') == $w->id || (isset($workerId) && $workerId == $w->id)) ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" class="w-full px-3 py-2 border rounded">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (request('month') == $m || (isset($month) && $month == $m)) ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" class="w-full px-3 py-2 border rounded">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ (request('year') == $y || (isset($year) && $year == $y)) ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-end">
                <x-button type="submit" variant="primary" icon="fas fa-filter">Filter</x-button>
            </div>
        </form>
    </x-card>

    <x-card>
        @if(isset($report) && $report->isNotEmpty())
            <!-- Mobile Card Layout -->
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($report as $i => $row)
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-semibold text-gray-900">{{ $row->attendance_date?->format('d M Y') ?? '-' }}</div>
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ ucfirst($row->status ?? '-') }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mb-2">Shift: {{ $row->shift->name ?? '-' }}</div>
                    <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                        <div><span class="text-gray-500">Check-in:</span> <span class="font-medium">{{ $row->check_in?->format('H:i') ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Check-out:</span> <span class="font-medium">{{ $row->check_out?->format('H:i') ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Telat:</span> <span class="font-medium">{{ $row->late_minutes ?? 0 }} mnt</span></div>
                        <div><span class="text-gray-500">Lembur:</span> <span class="font-medium">{{ $row->overtime_minutes ?? 0 }} mnt</span></div>
                    </div>
                    @if($row->notes)
                        <div class="text-xs text-gray-500"><span class="font-medium">Catatan:</span> {{ $row->notes }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telat (menit)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lembur (menit)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report as $i => $row)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->attendance_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->shift->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->check_in?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->check_out?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($row->status ?? '-') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->late_minutes ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->overtime_minutes ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $row->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(isset($report) && $report->isEmpty())
            <x-empty-state icon="fas fa-inbox" title="Tidak ada data" description="Tidak ditemukan data absensi untuk filter yang dipilih" />
        @elseif(isset($attendances) && $attendances->isNotEmpty())
            <!-- Mobile Card Layout -->
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($attendances as $i => $a)
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-semibold text-gray-900">{{ $a->worker->name ?? '-' }}</div>
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ ucfirst($a->status ?? '-') }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div><span class="text-gray-500">Shift:</span> <span class="font-medium">{{ $a->shift->name ?? '-' }}</span></div>
                        <div><span class="text-gray-500">In:</span> <span class="font-medium">{{ $a->check_in?->format('H:i') ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Out:</span> <span class="font-medium">{{ $a->check_out?->format('H:i') ?? '-' }}</span></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attendances as $i => $a)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $a->worker->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $a->shift->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $a->check_in?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $a->check_out?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($a->status ?? '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state icon="fas fa-inbox" title="Belum ada data" description="Pilih filter di atas untuk menampilkan laporan." />
        @endif
    </x-card>
</div>

@endsection
