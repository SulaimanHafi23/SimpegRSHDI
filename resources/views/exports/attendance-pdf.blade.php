@extends('exports.pdf-header', ['title' => 'Laporan Absensi'])

@section('content')
<h3>Laporan Absensi Pegawai</h3>

<div class="info-box">
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Periode</strong></td>
            <td style="width: 2%;">:</td>
            <td>{{ $dateFrom }} s/d {{ $dateTo }}</td>
        </tr>
        @if(isset($worker))
        <tr>
            <td><strong>Pegawai</strong></td>
            <td>:</td>
            <td>{{ $worker->name }} ({{ $worker->nip }})</td>
        </tr>
        <tr>
            <td><strong>Departemen</strong></td>
            <td>:</td>
            <td>{{ $worker->department->name ?? '-' }}</td>
        </tr>
        @endif
        @if(isset($status) && $status)
        <tr>
            <td><strong>Filter Status</strong></td>
            <td>:</td>
            <td>
                @php
                    $statusLabel = match($status) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'leave' => 'Cuti',
                        'sick' => 'Sakit',
                        'permission' => 'Izin',
                        default => ucfirst($status)
                    };
                @endphp
                <span class="badge badge-warning">{{ $statusLabel }}</span>
            </td>
        </tr>
        @endif
        <tr>
            <td><strong>Total Data</strong></td>
            <td>:</td>
            <td><strong>{{ $attendances->count() }} data</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="14%">Tanggal</th>
            @if(!isset($worker))
            <th width="20%">Pegawai</th>
            @endif
            <th width="10%">Masuk</th>
            <th width="10%">Pulang</th>
            <th width="10%" class="text-center">Jam Kerja</th>
            <th width="14%">Status</th>
            <th>Lokasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $index => $attendance)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $attendance->attendance_date->translatedFormat('d M Y') }}</td>
            @if(!isset($worker))
            <td>
                <strong>{{ $attendance->worker->name ?? '-' }}</strong><br>
                <span class="muted">{{ $attendance->worker->nip ?? '-' }}</span>
            </td>
            @endif
            <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
            <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
            <td class="text-center">
                @if($attendance->check_in && $attendance->check_out)
                    <strong>{{ number_format($attendance->check_in->diffInHours($attendance->check_out, true), 1) }}</strong> jam
                @else
                    -
                @endif
            </td>
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
            <td colspan="{{ isset($worker) ? 7 : 8 }}" class="empty-state">
                Tidak ada data absensi untuk periode ini.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($attendances->count() > 0)
<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Hadir:</strong> {{ $attendances->where('status', 'present')->where('is_late', false)->count() }} hari</td>
            <td><strong>Terlambat:</strong> {{ $attendances->where('is_late', true)->count() }} hari</td>
        </tr>
        <tr>
            <td><strong>Tidak Hadir:</strong> {{ $attendances->where('status', 'absent')->count() }} hari</td>
            <td><strong>Cuti/Izin/Sakit:</strong> {{ $attendances->whereIn('status', ['leave', 'permission', 'sick'])->count() }} hari</td>
        </tr>
        <tr>
            <td colspan="2">
                @php
                    $totalHours = $attendances->sum(function ($a) {
                        return $a->check_in && $a->check_out ? $a->check_in->diffInHours($a->check_out, true) : 0;
                    });
                @endphp
                <strong>Total Jam Kerja:</strong> {{ number_format($totalHours, 1) }} jam
            </td>
        </tr>
    </table>
</div>
@endif
@endsection
