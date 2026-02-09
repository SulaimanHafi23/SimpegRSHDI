@extends('exports.pdf-header', ['title' => 'Riwayat Absensi Pegawai'])

@section('content')
<h3>RIWAYAT ABSENSI PEGAWAI</h3>

<div class="info-box">
    <p><strong>Pegawai:</strong> {{ $worker->name }} ({{ $worker->nip }})</p>
    <p><strong>Departemen:</strong> {{ $worker->department->name ?? '-' }}</p>
    <p><strong>Periode:</strong> {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</p>
    <p><strong>Total Hari:</strong> {{ count($rows) }} hari</p>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="12%">Hari</th>
            <th width="12%">Shift</th>
            <th width="14%">Jadwal Shift</th>
            <th width="10%">Check In</th>
            <th width="10%">Check Out</th>
            <th width="10%">Status</th>
            <th width="8%">Terlambat</th>
            <th width="17%">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $index => $row)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['day_name'] }}</td>
            <td>{{ $row['shift_name'] }}</td>
            <td>{{ $row['shift_time'] }}</td>
            <td>{{ $row['check_in'] }}</td>
            <td>{{ $row['check_out'] }}</td>
            <td>{{ $row['status'] }}</td>
            <td class="text-center">{{ $row['late'] }}</td>
            <td>{{ $row['notes'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data absensi
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
