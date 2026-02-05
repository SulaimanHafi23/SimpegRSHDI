@extends('exports.pdf-header', ['title' => 'Laporan Absensi'])

@section('content')
<h3>LAPORAN ABSENSI PEGAWAI</h3>

<div class="info-box">
    <p><strong>Periode:</strong> {{ $dateFrom }} s/d {{ $dateTo }}</p>
    @if(isset($worker))
    <p><strong>Pegawai:</strong> {{ $worker->name }} ({{ $worker->nip }})</p>
    <p><strong>Departemen:</strong> {{ $worker->department->name ?? '-' }}</p>
    @endif
    @if(isset($status) && $status)
    <p><strong>Status:</strong> {{ ucfirst($status) }}</p>
    @endif
    <p><strong>Total Data:</strong> {{ $attendances->count() }} record</p>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="15%">Tanggal</th>
            @if(!isset($worker))
            <th width="20%">Pegawai</th>
            @endif
            <th width="10%">Check In</th>
            <th width="10%">Check Out</th>
            <th width="10%" class="text-center">Jam Kerja</th>
            <th width="15%">Status</th>
            <th width="15%">Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $index => $attendance)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->translatedFormat('d M Y') }}</td>
            @if(!isset($worker))
            <td>
                {{ $attendance->worker->name ?? '-' }}<br>
                <small style="color: #666;">{{ $attendance->worker->nip ?? '-' }}</small>
            </td>
            @endif
            <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
            <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
            <td class="text-center">{{ $attendance->work_hours ? number_format($attendance->work_hours, 1) . ' jam' : '-' }}</td>
            <td>
                @php
                    $statusClass = match($attendance->status) {
                        'present' => 'badge-success',
                        'late' => 'badge-warning',
                        'absent' => 'badge-danger',
                        'leave' => 'badge-secondary',
                        'sick' => 'badge-secondary',
                        'permission' => 'badge-secondary',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($attendance->status) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'leave' => 'Cuti',
                        'sick' => 'Sakit',
                        'permission' => 'Izin',
                        default => ucfirst($attendance->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </td>
            <td>{{ $attendance->location->name ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ isset($worker) ? 7 : 8 }}" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data absensi
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($attendances->count() > 0)
<div style="margin-top: 20px; padding: 10px; background-color: #f0fdf4; border-radius: 4px;">
    <p style="margin: 5px 0; font-size: 10px;"><strong>Ringkasan:</strong></p>
    <p style="margin: 5px 0; font-size: 10px;">Total Hadir: {{ $attendances->where('status', 'present')->count() }} hari</p>
    <p style="margin: 5px 0; font-size: 10px;">Total Terlambat: {{ $attendances->where('status', 'late')->count() }} hari</p>
    <p style="margin: 5px 0; font-size: 10px;">Total Tidak Hadir: {{ $attendances->where('status', 'absent')->count() }} hari</p>
    <p style="margin: 5px 0; font-size: 10px;">Total Jam Kerja: {{ number_format($attendances->sum('work_hours'), 1) }} jam</p>
</div>
@endif
@endsection
