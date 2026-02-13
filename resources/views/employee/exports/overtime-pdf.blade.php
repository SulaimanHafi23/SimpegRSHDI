<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #7c3aed;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #7c3aed;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 15px;
            background: #f3f4f6;
            padding: 12px;
            border-radius: 5px;
        }
        .info-row {
            margin: 4px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
        .summary {
            margin: 15px 0;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            color: #7c3aed;
            font-size: 14px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
            padding: 8px;
            background: white;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        .summary-item .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 4px;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
        }
        .summary-item.pending .value { color: #f59e0b; }
        .summary-item.approved .value { color: #16a34a; }
        .summary-item.rejected .value { color: #dc2626; }
        .summary-item.hours .value { color: #7c3aed; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #7c3aed;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none; margin-bottom: 5px;">
            <tr>
                <td style="width: 80px; text-align: center; border: none; vertical-align: middle;">
                    @if(file_exists(public_path('images/logo-rs.png')))
                        <img src="{{ public_path('images/logo-rs.png') }}" alt="Logo" style="max-width: 65px; max-height: 65px;">
                    @endif
                </td>
                <td style="text-align: center; border: none; vertical-align: middle; padding-left: 10px;">
                    <p style="margin: 0; font-size: 11px; font-weight: normal;">PEMERINTAH KABUPATEN TANAH LAUT</p>
                    <p style="margin: 0; font-size: 11px; font-weight: normal;">DINAS KESEHATAN</p>
                    <h2 style="margin: 2px 0; font-size: 16px; font-weight: bold;">UPTD RSUD HAJI DARLAN ISMAIL</h2>
                    <p style="margin: 0; font-size: 9px;">Jl. Swadaya RT.003 Desa Bumi Harapan Kecamatan Bumi Makmur</p>
                    <p style="margin: 0; font-size: 9px;">Kabupaten Tanah Laut Kode Pos 70853</p>
                    <p style="margin: 0; font-size: 9px;">Email: Rsudhajidarlanismail@gmail.com</p>
                </td>
                <td style="width: 80px; border: none;"></td>
            </tr>
        </table>
        <div style="border-bottom: 3px double #000; margin-bottom: 15px;"></div>
        <h1>{{ $title }}</h1>
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
            <span class="info-label">Tahun:</span>
            <span>{{ $filters['year'] ?? date('Y') }}</span>
        </div>
        @if(!empty($filters['status']))
        <div class="info-row">
            <span class="info-label">Filter Status:</span>
            <span>{{ ucfirst($filters['status']) }}</span>
        </div>
        @endif
    </div>

    <div class="summary">
        <h3>Ringkasan Permohonan Lembur</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total</div>
                <div class="value">{{ $summary['total'] }}</div>
            </div>
            <div class="summary-item pending">
                <div class="label">Pending</div>
                <div class="value">{{ $summary['pending'] }}</div>
            </div>
            <div class="summary-item approved">
                <div class="label">Disetujui</div>
                <div class="value">{{ $summary['approved'] }}</div>
            </div>
            <div class="summary-item rejected">
                <div class="label">Ditolak</div>
                <div class="value">{{ $summary['rejected'] }}</div>
            </div>
            <div class="summary-item hours">
                <div class="label">Total Jam</div>
                <div class="value">{{ number_format($summary['total_hours'], 1) }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Waktu Mulai</th>
                <th style="width: 10%;">Waktu Selesai</th>
                <th style="width: 7%;">Total Jam</th>
                <th style="width: 14%;">Shift</th>
                <th style="width: 23%;">Deskripsi</th>
                <th style="width: 12%;">Disetujui Oleh</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($overtimes as $index => $overtime)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}</td>
                <td style="text-align: center;">{{ number_format($overtime->total_hours, 1) }}</td>
                <td style="font-size: 9px;">
                    @if($overtime->actual_shift)
                        {{ $overtime->actual_shift->name }}
                        ({{ \Carbon\Carbon::parse($overtime->actual_shift->start_time)->format('H:i') }}-
                        {{ \Carbon\Carbon::parse($overtime->actual_shift->end_time)->format('H:i') }})
                    @else
                        -
                    @endif
                </td>
                <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($overtime->description, 60) }}</td>
                <td style="font-size: 9px;">{{ $overtime->approver->name ?? '-' }}</td>
                <td>
                    @if($overtime->status == 'pending')
                        <span class="badge badge-pending">Pending</span>
                    @elseif($overtime->status == 'approved')
                        <span class="badge badge-approved">Disetujui</span>
                    @else
                        <span class="badge badge-rejected">Ditolak</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 15px; color: #999;">
                    Tidak ada data permohonan lembur
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: left; border: none;">
                    <p>Dokumen ini digenerate otomatis oleh sistem SIMPEG</p>
                </td>
                <td style="width: 50%; text-align: right; border: none;">
                    <p>© {{ date('Y') }} RS Haji Darlan Ismail</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
