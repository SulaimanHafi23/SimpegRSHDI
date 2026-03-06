@extends('layouts.admin')

@section('title', 'Laporan Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Laporan Cuti"
        description="Ringkasan permohonan cuti berdasarkan rentang tanggal dan status"
        icon="fas fa-calendar-times">
        <x-slot:actions>
            <x-export-buttons :route="route('reports.leaves.export')" title="Export Laporan Cuti" />
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('reports.leaves') }}">
        <x-form.select
            name="worker_id"
            label="Pegawai"
            :selected="request('worker_id') ?? ''"
            placeholder="Semua Pegawai">
            @if(isset($workers))
                @foreach($workers as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            @endif
        </x-form.select>

        <x-form.select
            name="leave_type_id"
            label="Jenis Cuti"
            :selected="request('leave_type_id') ?? ''"
            placeholder="Semua Jenis">
            @if(isset($leaveTypes))
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            @endif
        </x-form.select>

        <x-form.select
            name="status"
            label="Status"
            :options="[
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak'
            ]"
            :selected="request('status') ?? ''"
            placeholder="Semua Status" />

        <x-form.select
            name="month"
            label="Bulan"
            :selected="request('month') ?? ''"
            placeholder="Semua Bulan">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
            @endfor
        </x-form.select>

        <x-form.select
            name="year"
            label="Tahun"
            :selected="request('year') ?? ''"
            placeholder="Semua Tahun">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </x-form.select>
    </x-filter-section>

    {{-- Table --}}
    <x-card>
        @if($leaves->isEmpty())
            <x-empty-state
                icon="fas fa-calendar-times"
                title="Tidak ada data cuti"
                description="Data cuti akan ditampilkan di sini" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Jenis Cuti</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Total Hari</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($leaves as $index => $leave)
                    <x-table.row>
                        <x-table.cell>{{ $leaves->firstItem() + $index }}</x-table.cell>

                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $leave->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $leave->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $leave->leaveType->name ?? '-' }}</x-table.cell>

                        <x-table.cell>
                            <div class="text-sm">{{ $leave->start_date->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">s/d {{ $leave->end_date->format('d M Y') }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $leave->total_days }} hari</x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                ];
                                $badge = $statusBadges[$leave->status] ?? ['variant' => 'secondary', 'label' => ucfirst($leave->status)];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <a href="{{ route('approvals.leaves.show', $leave->id) }}"
                               class="text-blue-600 hover:text-blue-900"
                               title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            @if($leaves->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$leaves" />
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
