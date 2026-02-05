@extends('layouts.admin')

@section('title', 'Laporan Lembur')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Laporan Lembur" 
        description="Ringkasan permohonan lembur berdasarkan rentang tanggal dan status"
        icon="fas fa-business-time">
        <x-slot:actions>
            <a href="{{ route('reports.overtimes.export', request()->only(['start_date','end_date','status','worker_id'])) }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                <i class="fas fa-file-excel mr-2"></i>
                Export Excel
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('reports.overtimes') }}">
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
        @if($overtimes->isEmpty())
            <x-empty-state 
                icon="fas fa-business-time"
                title="Tidak ada data lembur"
                description="Data lembur akan ditampilkan di sini" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>Tanggal</x-table.cell>
                        <x-table.cell header>Jam</x-table.cell>
                        <x-table.cell header>Total Jam</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($overtimes as $index => $ot)
                    <x-table.row>
                        <x-table.cell>{{ $overtimes->firstItem() + $index }}</x-table.cell>
                        
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $ot->worker->name ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $ot->worker->nip ?? '-' }}</div>
                        </x-table.cell>

                        <x-table.cell>{{ $ot->overtime_date->format('d M Y') }}</x-table.cell>

                        <x-table.cell>{{ optional($ot->start_time)->format('H:i') ?? '-' }} - {{ optional($ot->end_time)->format('H:i') ?? '-' }}</x-table.cell>

                        <x-table.cell>{{ $ot->total_hours }} jam</x-table.cell>

                        <x-table.cell>
                            @php
                                $statusBadges = [
                                    'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                    'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                    'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                ];
                                $badge = $statusBadges[$ot->status] ?? ['variant' => 'secondary', 'label' => ucfirst($ot->status)];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <a href="{{ route('approvals.overtimes.show', $ot->id) }}" 
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

            @if($overtimes->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$overtimes" />
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
