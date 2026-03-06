@extends('layouts.admin')

@section('title', 'Laporan Presensi')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Laporan Presensi"
        description="Lihat dan ekspor data presensi pegawai"
        icon="fas fa-calendar-check">
        <x-slot:actions>
            <x-export-buttons :route="route('reports.attendance.export')" title="Export Laporan Presensi" />
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('reports.attendance') }}">
        <x-form.select
            name="worker_id"
            label="Pegawai"
            :selected="$filters['worker_id'] ?? ''"
            placeholder="Semua Pegawai">
            @foreach($workers as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="month"
            label="Bulan"
            :selected="$filters['month'] ?? ''"
            placeholder="Semua Bulan">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
            @endfor
        </x-form.select>

        <x-form.select
            name="year"
            label="Tahun"
            :selected="$filters['year'] ?? ''"
            placeholder="Semua Tahun">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </x-form.select>
    </x-filter-section>

    {{-- Summary Bar --}}
    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg px-6 py-4 border border-blue-200">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium text-gray-700">
                        Total: <span class="text-blue-600 font-bold">{{ $attendances->total() ?? 0 }}</span> data
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <x-card>
        @if($attendances->isEmpty())
            <x-empty-state
                icon="fas fa-calendar-check"
                title="Tidak ada data presensi"
                description="Data presensi akan ditampilkan di sini" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Check In</x-table.cell>
                        <x-table.cell header>Check Out</x-table.cell>
                        <x-table.cell header>Lokasi</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($attendances as $attendance)
                    <x-table.row>
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $attendance->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $attendance->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</x-table.cell>

                        <x-table.cell>
                            @if($attendance->check_in)
                                <span class="text-green-600 font-medium">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            @if($attendance->check_out)
                                <span class="text-blue-600 font-medium">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>{{ $attendance->location->name ?? '-' }}</x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'present' => ['variant' => 'success', 'label' => 'Hadir'],
                                    'late' => ['variant' => 'warning', 'label' => 'Terlambat'],
                                    'absent' => ['variant' => 'danger', 'label' => 'Tidak Hadir'],
                                    'on_leave' => ['variant' => 'info', 'label' => 'Cuti'],
                                ];
                                $badge = $statusBadges[$attendance->status] ?? ['variant' => 'secondary', 'label' => ucfirst($attendance->status ?? '-')];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            @if($attendances->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$attendances" />
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
