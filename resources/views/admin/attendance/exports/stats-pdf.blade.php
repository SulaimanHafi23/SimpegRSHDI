<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Statistik Kehadiran - {{ $worker->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #374151;
            background: #fff;
        }

        /* Header Gradient */
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            color: white;
            padding: 25px 30px;
            margin: -10px -10px 20px -10px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .logo-section {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .company-subtitle {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 3px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }

        .report-subtitle {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 5px;
        }

        /* Worker Info Card */
        .worker-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #0ea5e9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }

        .worker-info {
            display: table-cell;
            vertical-align: middle;
        }

        .worker-name {
            font-size: 18px;
            font-weight: bold;
            color: #0c4a6e;
            margin-bottom: 5px;
        }

        .worker-meta {
            font-size: 11px;
            color: #0369a1;
        }

        .period-badge {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .period-box {
            display: inline-block;
            background: #0ea5e9;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
        }

        .period-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .period-dates {
            font-size: 11px;
            font-weight: bold;
            margin-top: 3px;
        }

        /* Statistics Grid */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .stats-row {
            display: table-row;
        }

        .stat-card {
            display: table-cell;
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            border: 2px solid #e5e7eb;
            vertical-align: top;
        }

        .stat-card.green {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #10b981;
        }

        .stat-card.red {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #ef4444;
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border-color: #f97316;
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #3b82f6;
        }

        .stat-card.purple {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border-color: #a855f7;
        }

        .stat-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-percent {
            font-size: 9px;
            margin-top: 5px;
            padding: 3px 8px;
            background: rgba(0,0,0,0.08);
            border-radius: 10px;
            display: inline-block;
        }

        /* Progress Bar Section */
        .progress-section {
            background: linear-gradient(135deg, #fafafa 0%, #f3f4f6 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }

        .progress-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .progress-bar-container {
            background: #e5e7eb;
            border-radius: 10px;
            height: 25px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .progress-bar-fill.green {
            background: linear-gradient(90deg, #22c55e 0%, #10b981 100%);
        }

        .progress-label {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            color: #374151;
            font-size: 11px;
        }

        /* Detail Tables */
        .detail-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            padding: 12px 15px;
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
            color: white;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }

        .detail-table th {
            background: #f9fafb;
            padding: 10px 12px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }

        .detail-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
        }

        .detail-table tr:nth-child(even) {
            background: #fafafa;
        }

        .detail-table tr:hover {
            background: #f0f9ff;
        }

        /* Summary Table */
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        .summary-col:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        .summary-box {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }

        .summary-header {
            padding: 12px 15px;
            font-weight: bold;
            color: white;
            font-size: 11px;
        }

        .summary-header.blue {
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
        }

        .summary-header.purple {
            background: linear-gradient(90deg, #8b5cf6 0%, #a78bfa 100%);
        }

        .summary-item {
            display: table;
            width: 100%;
            padding: 10px 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item-label {
            display: table-cell;
            color: #6b7280;
            font-size: 10px;
        }

        .summary-item-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-yellow {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-orange {
            background: #ffedd5;
            color: #9a3412;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 9px;
        }

        .footer-logo {
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 5px;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Icons using Unicode */
        .icon-calendar::before { content: "📅 "; }
        .icon-check::before { content: "✓ "; }
        .icon-times::before { content: "✗ "; }
        .icon-clock::before { content: "⏰ "; }
        .icon-chart::before { content: "📊 "; }
        .icon-user::before { content: "👤 "; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="company-name">RS HAJI DARJAD IBRAHIM</div>
            <div class="company-subtitle">Sistem Informasi Manajemen Kepegawaian</div>
            <div class="report-title">📊 LAPORAN STATISTIK KEHADIRAN</div>
            <div class="report-subtitle">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</div>
        </div>
    </div>

    <!-- Worker Info Card -->
    <div class="worker-card">
        <div class="worker-info">
            <div class="worker-name">👤 {{ $worker->name }}</div>
            <div class="worker-meta">
                NIP: {{ $worker->nip ?? '-' }} &nbsp;|&nbsp; 
                Departemen: {{ $worker->department->name ?? '-' }} &nbsp;|&nbsp;
                Shift: {{ $worker->shift->name ?? ($worker->workerShifts->first()?->shift?->name ?? 'Default') }}
            </div>
        </div>
        <div class="period-badge">
            <div class="period-box">
                <div class="period-label">Periode Laporan</div>
                <div class="period-dates">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Main Statistics -->
    <table class="stats-grid">
        <tr class="stats-row">
            <td class="stat-card blue" style="padding-right: 10px;">
                <div class="stat-value" style="color: #1e40af;">{{ $stats['total_work_days'] }}</div>
                <div class="stat-label">Total Hari Kerja</div>
                <div class="stat-percent">📅 Periode ini</div>
            </td>
            <td class="stat-card green" style="padding: 0 5px;">
                <div class="stat-value" style="color: #059669;">{{ $stats['total_present'] }}</div>
                <div class="stat-label">Total Hadir</div>
                <div class="stat-percent" style="background: #dcfce7; color: #166534;">{{ $stats['attendance_percentage'] }}%</div>
            </td>
            <td class="stat-card red" style="padding: 0 5px;">
                <div class="stat-value" style="color: #dc2626;">{{ $stats['total_absent'] }}</div>
                <div class="stat-label">Total Absent</div>
                <div class="stat-percent" style="background: #fee2e2; color: #991b1b;">{{ $stats['absence_percentage'] }}%</div>
            </td>
            <td class="stat-card orange" style="padding-left: 10px;">
                <div class="stat-value" style="color: #ea580c;">{{ $stats['late_arrivals'] }}</div>
                <div class="stat-label">Terlambat</div>
                <div class="stat-percent" style="background: #ffedd5; color: #9a3412;">⏰ Kali</div>
            </td>
        </tr>
    </table>

    <!-- Progress Bar -->
    <div class="progress-section">
        <div class="progress-title">📈 Tingkat Kehadiran</div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill green" style="width: {{ $stats['attendance_percentage'] }}%"></div>
            <span class="progress-label">{{ $stats['attendance_percentage'] }}% Kehadiran</span>
        </div>
    </div>

    <!-- Summary Grid -->
    <div class="summary-grid">
        <div class="summary-col">
            <div class="summary-box">
                <div class="summary-header blue">📋 Rincian Absensi</div>
                <div class="summary-item">
                    <span class="summary-item-label">✓ Check In + Check Out</span>
                    <span class="summary-item-value" style="color: #059669;">{{ $stats['complete_attendance'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">→ Check In Saja</span>
                    <span class="summary-item-value" style="color: #eab308;">{{ $stats['check_in_only'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">← Check Out Saja</span>
                    <span class="summary-item-value" style="color: #f97316;">{{ $stats['check_out_only'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">⏰ Keterlambatan</span>
                    <span class="summary-item-value" style="color: #dc2626;">{{ $stats['late_arrivals'] }}</span>
                </div>
                @if($stats['overtime_hours'] > 0)
                <div class="summary-item">
                    <span class="summary-item-label">⏱️ Total Lembur (Jam)</span>
                    <span class="summary-item-value" style="color: #8b5cf6;">{{ $stats['overtime_hours'] }}</span>
                </div>
                @endif
            </div>
        </div>
        <div class="summary-col">
            <div class="summary-box">
                <div class="summary-header purple">🗓️ Rincian Cuti & Izin</div>
                <div class="summary-item">
                    <span class="summary-item-label">🏖️ Cuti</span>
                    <span class="summary-item-value" style="color: #3b82f6;">{{ $stats['leave_days'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">🤒 Sakit</span>
                    <span class="summary-item-value" style="color: #dc2626;">{{ $stats['sick_days'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">✋ Izin</span>
                    <span class="summary-item-value" style="color: #eab308;">{{ $stats['permission_days'] }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">📊 Total Ketidakhadiran</span>
                    <span class="summary-item-value" style="color: #6b7280;">{{ $stats['leave_days'] + $stats['sick_days'] + $stats['permission_days'] + $stats['total_absent'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Table -->
    <div class="detail-section">
        <div class="section-title">📝 Detail Riwayat Kehadiran ({{ $attendances->count() }} Record)</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Tanggal</th>
                    <th style="width: 12%;">Hari</th>
                    <th style="width: 12%;">Check In</th>
                    <th style="width: 12%;">Check Out</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 26%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $attendance)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->locale('id')->isoFormat('dddd') }}</td>
                    <td>
                        @if($attendance->check_in)
                            {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                            @if($attendance->is_late)
                                <span class="badge badge-red">Terlambat</span>
                            @endif
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($attendance->check_out)
                            {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                            @if($attendance->is_early_leave)
                                <span class="badge badge-orange">Awal</span>
                            @endif
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td>
                        @switch($attendance->status)
                            @case('present')
                                <span class="badge badge-green">Hadir</span>
                                @break
                            @case('absent')
                                <span class="badge badge-red">Tidak Hadir</span>
                                @break
                            @case('leave')
                            @case('cuti')
                                <span class="badge badge-blue">Cuti</span>
                                @break
                            @case('sick')
                            @case('sakit')
                                <span class="badge badge-red">Sakit</span>
                                @break
                            @case('permission')
                            @case('izin')
                                <span class="badge badge-yellow">Izin</span>
                                @break
                            @default
                                <span class="badge">{{ ucfirst($attendance->status) }}</span>
                        @endswitch
                    </td>
                    <td>{{ $attendance->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #9ca3af;">
                        Tidak ada data kehadiran dalam periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-logo">RS HAJI DARJAD IBRAHIM - SIMPEG</div>
        <div>Dokumen ini digenerate secara otomatis oleh sistem pada {{ now()->format('d F Y H:i:s') }}</div>
        <div style="margin-top: 5px; font-size: 8px;">© {{ date('Y') }} RS Haji Darjad Ibrahim. All rights reserved.</div>
    </div>
</body>
</html>
