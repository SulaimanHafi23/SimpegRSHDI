@extends('exports.pdf-header', ['title' => 'Laporan Presensi'])

@section('content')
<h3>LAPORAN PRESENSI PEGAWAI</h3>

<table style="width:100%; margin-bottom: 15px; font-size: 10px;">
    <tr>
        <td style="width: 120px;"><strong>Periode</strong></td>
        <td>: {{ $filters['date_from'] ?? '-' }} s/d {{ $filters['date_to'] ?? '-' }}</td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 30px;">No</th>
            <th>Tanggal</th>
            <th>NIP</th>
            <th>Nama Pegawai</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Lokasi</th>
            <th>Status</th>
            <th>Terlambat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->attendance_date?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $item->worker->nip ?? '-' }}</td>
                <td>{{ $item->worker->name ?? '-' }}</td>
                <td>{{ $item->check_in?->format('H:i') ?? '-' }}</td>
                <td>{{ $item->check_out?->format('H:i') ?? '-' }}</td>
                <td>{{ $item->location->name ?? '-' }}</td>
                <td>{{ ucfirst($item->status ?? '-') }}</td>
                <td>{{ $item->is_late ? $item->late_minutes . ' menit' : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; color: #999;">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 30px; font-size: 10px;">
    <p><strong>Total Data:</strong> {{ $attendances->count() }} presensi</p>
</div>
@endsection
