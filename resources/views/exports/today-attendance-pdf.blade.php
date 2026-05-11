@extends('exports.pdf-header', ['title' => 'Laporan Absensi Harian - ' . $date])

@section('content')
<h3>LAPORAN ABSENSI HARIAN</h3>

<div class="info-box">
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Tanggal</strong></td>
            <td style="width: 2%;">:</td>
            <td><strong>{{ $date }}</strong></td>
        </tr>
        <tr>
            <td><strong>Total Pegawai</strong></td>
            <td>:</td>
            <td><strong>{{ $stats['total_workers'] }}</strong></td>
        </tr>
    </table>
</div>

<div class="summary-box" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
    <p class="summary-title">Ringkasan Kehadiran</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Hadir: <strong style="color: #059669;">{{ $stats['present'] }}</strong></span>
            </td>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Terlambat: <strong style="color: #d97706;">{{ $stats['late'] }}</strong></span>
            </td>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Belum Absen: <strong style="color: #dc2626;">{{ $stats['not_checked_in'] }}</strong></span>
            </td>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Libur: <strong style="color: #6b7280;">{{ $stats['off_day'] ?? 0 }}</strong></span>
            </td>
        </tr>
        <tr>
            <td>
                <span style="font-size: 10px;">Cuti: <strong style="color: #2563eb;">{{ $stats['leave'] }}</strong></span>
            </td>
            <td>
                <span style="font-size: 10px;">Sakit: <strong style="color: #dc2626;">{{ $stats['sick'] }}</strong></span>
            </td>
            <td>
                <span style="font-size: 10px;">Izin: <strong style="color: #7c3aed;">{{ $stats['permission'] }}</strong></span>
            </td>
            <td></td>
        </tr>
    </table>
</div>

@if($workers->count() > 0)
<table>
    <thead>
        <tr>
            <th class="text-center" width="3%">No</th>
            <th width="9%">NIP</th>
            <th width="15%">Nama</th>
            <th width="13%">Departemen</th>
            <th width="10%">Shift</th>
            <th class="text-center" width="8%">Check In</th>
            <th class="text-center" width="8%">Check Out</th>
            <th class="text-center" width="10%">Status</th>
            <th class="text-center" width="8%">Terlambat</th>
            <th width="16%">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($workers as $index => $worker)
        @php
            $shift = $worker->shift ?? $worker->workerShifts->first()?->shift;
            $shiftInfo = '-';
            if ($shift) {
                $schedule = $shift->getScheduleForDate($dateRaw ?? now());
                $shiftInfo = $shift->name . ' (' . \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') . '-' . \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') . ')';
            }

            $checkInDisplay = '-';
            $checkOutDisplay = '-';
            $notesDisplay = '-';

            if ($worker->leave_request) {
                $checkInDisplay = 'CUTI/IZIN';
                $checkOutDisplay = 'CUTI/IZIN';
                $notesDisplay = $worker->leave_request->leaveType->name . ' (' . $worker->leave_request->start_date->format('d/m/Y') . ' - ' . $worker->leave_request->end_date->format('d/m/Y') . ')';
            } else {
                $checkInDisplay = $worker->check_in_time ?? '-';
                $checkOutDisplay = $worker->check_out_time ?? '-';
                $notesDisplay = $worker->today_attendance?->notes ?? '-';
            }

            $statusClass = match($worker->attendance_status) {
                'present' => 'badge-success',
                'late' => 'badge-warning',
                'absent' => 'badge-danger',
                'leave', 'cuti' => 'badge-secondary',
                'sick', 'sakit' => 'badge-danger',
                'permission', 'izin' => 'badge-warning',
                'off_day' => 'badge-secondary',
                'not_checked_in' => 'badge-secondary',
                default => 'badge-secondary'
            };
        @endphp
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $worker->nip }}</td>
            <td>{{ $worker->name }}</td>
            <td>{{ $worker->department->name ?? '-' }}</td>
            <td style="font-size: 8px;">{{ $shiftInfo }}</td>
            <td class="text-center">{{ $checkInDisplay }}</td>
            <td class="text-center">{{ $checkOutDisplay }}</td>
            <td class="text-center">
                <span class="badge {{ $statusClass }}">{{ $worker->status_label }}</span>
            </td>
            <td class="text-center">
                @if($worker->leave_request)
                    -
                @elseif($worker->is_late)
                    <span style="color: #92400e; font-weight: bold;">{{ $worker->late_minutes }} mnt</span>
                @else
                    -
                @endif
            </td>
            <td>{{ $notesDisplay }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="empty-state">Tidak ada data pegawai</div>
@endif
@endsection
