<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi - {{ $date }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px 25px;
        }

        .letterhead {
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
            text-align: center;
        }

        .letterhead h1 {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
        }

        .letterhead h2 {
            font-size: 18px;
            font-weight: bold;
            color: #1a5490;
            margin-bottom: 5px;
        }

        .letterhead p {
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #4472C4;
            color: white;
            /* border-radius: 5px; */
        }

        .header h3 {
            font-size: 14px;
            margin-bottom: 3px;
            font-weight: bold;
            color: white;
        }

        .header .date {
            font-size: 11px;
            margin-top: 3px;
            color: white;
        }

        .summary {
            margin-bottom: 12px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #4472C4;
        }

        .summary h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #4472C4;
            font-weight: bold;
            text-align: center;
        }

        .stats-grid {
            display: table;
            width: 100%;
        }

        .stats-item {
            display: table-cell;
            width: 25%;
            padding: 6px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            margin: 3px;
        }

        .stats-item .label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }

        .stats-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .stats-item.total { border-left: 3px solid #6c757d; }
        .stats-item.present { border-left: 3px solid #28a745; }
        .stats-item.late { border-left: 3px solid #ffc107; }
        .stats-item.absent { border-left: 3px solid #dc3545; }
        .stats-item.leave { border-left: 3px solid #17a2b8; }
        .stats-item.sick { border-left: 3px solid #fd7e14; }
        .stats-item.permission { border-left: 3px solid #007bff; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background: white;
        }

        table thead {
            background: #4472C4;
            color: white;
        }

        table thead th {
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #4472C4;
            color: white;
        }

        table tbody td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
            color: #333;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 50px;
        }

        .status-present { background-color: #d4edda; color: #155724; }
        .status-late { background-color: #fff3cd; color: #856404; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-leave { background-color: #d1ecf1; color: #0c5460; }
        .status-sick { background-color: #f8d7da; color: #721c24; }
        .status-permission { background-color: #cfe2ff; color: #084298; }
        .status-not_checked_in { background-color: #e2e3e5; color: #383d41; }

        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 2px solid #4472C4;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Letterhead -->
    <div class="letterhead">
        <h1>PEMERINTAH KABUPATEN TANAH LAUT</h1>
        <h2>RSUD HAJI DARLAN ISMAIL</h2>
        <p>
            Bumi Harapan, Kec. Bumi Makmur, Kabupaten Tanah Laut, Kalimantan Selatan<br>
            <!-- Telp: (0511) 4774673 | Email: rsud.hdi@tapinkab.go.id | Website: www.rsudhjdarlanismail.co.id -->
        </p>
    </div>

    <div class="header">
        <h3>LAPORAN ABSENSI HARIAN</h3>
        <div class="date">{{ $date }}</div>
    </div>

    <div class="summary">
        <h3>RINGKASAN KEHADIRAN</h3>
        <table style="border: none;">
            <tr>
                <td class="stats-item total" style="width: 25%;">
                    <div class="label">Total Pegawai</div>
                    <div class="value">{{ $stats['total_workers'] }}</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="stats-item present" style="width: 25%;">
                    <div class="label">Hadir</div>
                    <div class="value">{{ $stats['present'] }}</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="stats-item late" style="width: 25%;">
                    <div class="label">Terlambat</div>
                    <div class="value">{{ $stats['late'] }}</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="stats-item absent" style="width: 25%;">
                    <div class="label">Belum Absen</div>
                    <div class="value">{{ $stats['not_checked_in'] }}</div>
                </td>
            </tr>
        </table>
        <table style="border: none; margin-top: 6px;">
            <tr>
                <td class="stats-item leave" style="width: 33%;">
                    <div class="label">Cuti</div>
                    <div class="value">{{ $stats['leave'] }}</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="stats-item sick" style="width: 33%;">
                    <div class="label">Sakit</div>
                    <div class="value">{{ $stats['sick'] }}</div>
                </td>
                <td style="width: 2%;"></td>
                <td class="stats-item permission" style="width: 33%;">
                    <div class="label">Izin</div>
                    <div class="value">{{ $stats['permission'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if($workers->count() > 0)
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th class="text-center" style="width: 9%;">NIP</th>
                <th class="text-center" style="width: 15%;">Nama</th>
                <th class="text-center" style="width: 13%;">Departemen</th>
                <th class="text-center" style="width: 10%;">Shift</th>
                <th class="text-center" style="width: 8%;">Check In</th>
                <th class="text-center" style="width: 8%;">Check Out</th>
                <th class="text-center" style="width: 10%;">Status</th>
                <th class="text-center" style="width: 8%;">Terlambat</th>
                <th class="text-center" style="width: 16%;">Catatan</th>
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
                
                // Jika ada leave request, gunakan info dari leave request
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
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $worker->nip }}</td>
                <td>{{ $worker->name }}</td>
                <td>{{ $worker->department->name ?? '-' }}</td>
                <td style="font-size: 7px;">{{ $shiftInfo }}</td>
                <td class="text-center">{{ $checkInDisplay }}</td>
                <td class="text-center">{{ $checkOutDisplay }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $worker->attendance_status }}">
                        {{ $worker->status_label }}
                    </span>
                </td>
                <td class="text-center">
                    @if($worker->leave_request)
                        -
                    @elseif($worker->is_late)
                        <span style="color: #856404; font-weight: bold;">{{ $worker->late_minutes }} mnt</span>
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
    <div class="no-data">
        Tidak ada data pegawai
    </div>
    @endif

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh Sistem SIMPEG RSHDI</p>
        <p>Dicetak pada: {{ now()->translatedFormat('l, d F Y - H:i:s') }} WIB</p>
    </div>
</body>
</html>
