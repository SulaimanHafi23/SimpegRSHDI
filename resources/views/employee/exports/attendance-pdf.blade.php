<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #16a34a;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
        }
        .info-row {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            color: #16a34a;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        .summary-item .label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
        }
        .summary-item.present .value { color: #16a34a; }
        .summary-item.late .value { color: #f59e0b; }
        .summary-item.absent .value { color: #dc2626; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #16a34a;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>RSUD Haji Darlan Ismail</p>
        <p style="font-size: 10px;">Dicetak pada: {{ $generated_at }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Nama Pegawai:</span>
            <span>{{ $worker->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NIP:</span>
            <span>{{ $worker->nip }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Departemen:</span>
            <span>{{ $worker->department->name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periode:</span>
            <span>{{ \Carbon\Carbon::parse($filters['date_from'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($filters['date_to'])->format('d F Y') }}</span>
        </div>
        @if(!empty($filters['status']))
        <div class="info-row">
            <span class="info-label">Filter Status:</span>
            <span>{{ $filters['status'] }}</span>
        </div>
        @endif
    </div>

    <div class="summary">
        <h3>Ringkasan Kehadiran</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Hari</div>
                <div class="value">{{ $summary['total'] }}</div>
            </div>
            <div class="summary-item present">
                <div class="label">Hadir</div>
                <div class="value">{{ $summary['present'] }}</div>
            </div>
            <div class="summary-item late">
                <div class="label">Terlambat</div>
                <div class="value">{{ $summary['late'] }}</div>
            </div>
            <div class="summary-item absent">
                <div class="label">Tidak Hadir</div>
                <div class="value">{{ $summary['absent'] }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 12%;">Check In</th>
                <th style="width: 12%;">Check Out</th>
                <th style="width: 15%;">Shift</th>
                <th style="width: 20%;">Lokasi</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 9%;">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</td>
                <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->shift->name ?? '-' }}</td>
                <td style="font-size: 10px;">{{ $attendance->location->name ?? '-' }}</td>
                <td>
                    @if($attendance->status == 'Hadir')
                        <span class="badge badge-success">Hadir</span>
                    @elseif($attendance->status == 'Terlambat')
                        <span class="badge badge-warning">Terlambat</span>
                    @else
                        <span class="badge badge-danger">Tidak Hadir</span>
                    @endif
                </td>
                <td>{{ $attendance->work_duration ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                    Tidak ada data absensi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate otomatis oleh sistem SIMPEG RSUD Haji Darlan Ismail</p>
        <p>© {{ date('Y') }} RSUD Haji Darlan Ismail. All rights reserved.</p>
    </div>
</body>
</html>
