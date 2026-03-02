@extends('exports.pdf-header', ['title' => 'Riwayat Absensi Pegawai'])

@section('content')
<style>
    @page {
        margin: 25px 35px 35px 35px;
    }
    .page-padding {
        padding: 10px 15px;
    }
    .report-banner {
        background: #1d4ed8;
        color: #ffffff;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
        padding: 8px 10px;
        border-radius: 4px;
        margin-bottom: 12px;
    }
    .summary-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
        margin-top: 0;
    }
    .summary-card {
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-left: 4px solid #9ca3af;
        border-radius: 4px;
        padding: 8px 10px;
    }
    .summary-card .label {
        font-size: 9px;
        color: #6b7280;
        margin-bottom: 2px;
    }
    .summary-card .value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }
    .summary-green { border-left-color: #16a34a; }
    .summary-yellow { border-left-color: #f59e0b; }
    .summary-red { border-left-color: #dc2626; }
    .summary-blue { border-left-color: #2563eb; }
    .summary-purple { border-left-color: #7c3aed; }
    .summary-indigo { border-left-color: #4f46e5; }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        border: 1px solid #9ca3af;
    }
    .attendance-table thead th {
        background: #2563eb;
        color: #ffffff;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #1d4ed8;
        padding: 7px 5px;
    }
    .attendance-table tbody td {
        border: 1px solid #d1d5db;
        font-size: 9px;
        color: #1f2937;
        padding: 6px 5px;
        vertical-align: top;
    }
    .attendance-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    .text-center { text-align: center; }

    .status-pill {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .pill-hadir { background: #dcfce7; color: #166534; border-color: #86efac; }
    .pill-terlambat { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .pill-absen { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .pill-cuti { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
    .pill-sakit { background: #ede9fe; color: #5b21b6; border-color: #c4b5fd; }
    .pill-izin { background: #e0e7ff; color: #3730a3; border-color: #a5b4fc; }
    .pill-libur { background: #f3f4f6; color: #374151; border-color: #d1d5db; }

    .muted { color: #6b7280; }
</style>

<div class="page-padding">
    <div class="report-banner">LAPORAN ABSENSI PER PEGAWAI</div>
    <h3 style="margin-bottom: 10px;">RIWAYAT ABSENSI PEGAWAI</h3>

<div class="info-box">
    <table style="width: 100%; border: none; border-collapse: collapse;">
        <tr>
            <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                <table style="border: none; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Pegawai</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 11px; font-weight: bold; color: #111827;">{{ $worker->name }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">NIP</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $worker->nip }}</td>
                    </tr>
                </table>
            </td>
            <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                <table style="border: none; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Departemen</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $worker->department->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Periode</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #a7f3d0; text-align: right;">
        <span style="font-size: 10px; color: #047857; font-weight: bold;">Total Hari: {{ count($rows) }} hari</span>
    </div>
</div>

@if(isset($summary))
<div class="summary-box" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
    <p style="margin-bottom: 8px; font-size: 11px; font-weight: bold; color: #047857;">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="border: none; padding: 0; width: 33%;">
                <div class="summary-card summary-green">
                    <div class="label">Hadir</div>
                    <div class="value">{{ $summary['present'] }}</div>
                </div>
            </td>
            <td style="border: none; padding: 0; width: 33%;">
                <div class="summary-card summary-yellow">
                    <div class="label">Terlambat</div>
                    <div class="value">{{ $summary['late'] }}</div>
                </div>
            </td>
            <td style="border: none; padding: 0; width: 33%;">
                <div class="summary-card summary-red">
                    <div class="label">Tidak Hadir</div>
                    <div class="value">{{ $summary['absent'] }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 0;">
                <div class="summary-card summary-blue">
                    <div class="label">Cuti</div>
                    <div class="value">{{ $summary['leave'] }}</div>
                </div>
            </td>
            <td style="border: none; padding: 0;">
                <div class="summary-card summary-purple">
                    <div class="label">Sakit</div>
                    <div class="value">{{ $summary['sick'] }}</div>
                </div>
            </td>
            <td style="border: none; padding: 0;">
                <div class="summary-card summary-indigo">
                    <div class="label">Izin</div>
                    <div class="value">{{ $summary['permission'] }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>
@endif

    <table class="attendance-table">
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
                <td class="text-center">{{ $row['check_in'] }}</td>
                <td class="text-center">{{ $row['check_out'] }}</td>
                <td class="text-center">
                    @php
                        $status = strtolower($row['status'] ?? '');
                        $statusClass = 'pill-libur';
                        if ($status === 'hadir') $statusClass = 'pill-hadir';
                        elseif ($status === 'terlambat') $statusClass = 'pill-terlambat';
                        elseif ($status === 'tidak hadir') $statusClass = 'pill-absen';
                        elseif ($status === 'cuti') $statusClass = 'pill-cuti';
                        elseif ($status === 'sakit') $statusClass = 'pill-sakit';
                        elseif ($status === 'izin') $statusClass = 'pill-izin';
                        elseif ($status === 'libur') $statusClass = 'pill-libur';
                    @endphp
                    <span class="status-pill {{ $statusClass }}">{{ $row['status'] }}</span>
                </td>
                <td class="text-center">{{ $row['late'] }}</td>
                <td class="muted">{{ $row['notes'] }}</td>
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
</div>
@endsection
