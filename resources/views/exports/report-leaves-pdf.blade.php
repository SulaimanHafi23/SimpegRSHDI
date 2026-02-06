@extends('exports.pdf-header', ['title' => 'Laporan Cuti'])

@section('content')
<h3>LAPORAN PERMOHONAN CUTI</h3>

<table style="width:100%; margin-bottom: 15px; font-size: 10px;">
    <tr>
        <td style="width: 120px;"><strong>Periode</strong></td>
        <td>: {{ $filters['date_from'] ?? '-' }} s/d {{ $filters['date_to'] ?? '-' }}</td>
    </tr>
    @if(!empty($filters['status']))
    <tr>
        <td><strong>Status</strong></td>
        <td>: {{ ucfirst($filters['status']) }}</td>
    </tr>
    @endif
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 30px;">No</th>
            <th>Tgl Pengajuan</th>
            <th>NIP</th>
            <th>Nama Pegawai</th>
            <th>Jenis Cuti</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Durasi</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaves as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->worker->nip ?? '-' }}</td>
                <td>{{ $item->worker->name ?? '-' }}</td>
                <td>{{ $item->leaveType->name ?? '-' }}</td>
                <td>{{ $item->start_date?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->end_date?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align: center;">{{ $item->total_days ?? 0 }} hari</td>
                <td>
                    @switch($item->status)
                        @case('pending')
                            <span style="color: #f59e0b;">Menunggu</span>
                            @break
                        @case('approved')
                            <span style="color: #10b981;">Disetujui</span>
                            @break
                        @case('rejected')
                            <span style="color: #ef4444;">Ditolak</span>
                            @break
                        @default
                            {{ ucfirst($item->status ?? '-') }}
                    @endswitch
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; color: #999;">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 30px; font-size: 10px;">
    <p><strong>Total Data:</strong> {{ $leaves->count() }} permohonan cuti</p>
</div>
@endsection
