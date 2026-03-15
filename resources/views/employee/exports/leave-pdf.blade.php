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
        .summary-item.pending .value { color: #f59e0b; }
        .summary-item.approved .value { color: #16a34a; }
        .summary-item.rejected .value { color: #dc2626; }
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
        <h3>Ringkasan Permohonan Cuti</h3>
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
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Jenis Cuti</th>
                <th style="width: 15%;">Tanggal Mulai</th>
                <th style="width: 15%;">Tanggal Selesai</th>
                <th style="width: 8%;">Durasi</th>
                <th style="width: 25%;">Alasan</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $index => $leave)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $leave->leaveType->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                <td style="text-align: center;">{{ $leave->duration }} hari</td>
                <td style="font-size: 10px;">{{ \Illuminate\Support\Str::limit($leave->reason, 50) }}</td>
                <td>
                    @if($leave->status == 'pending')
                        <span class="badge badge-pending">Pending</span>
                    @elseif($leave->status == 'approved')
                        <span class="badge badge-approved">Disetujui</span>
                    @else
                        <span class="badge badge-rejected">Ditolak</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                    Tidak ada data permohonan cuti
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: left; border: none;">
                    <p>Dokumen ini digenerate otomatis oleh SIDIA - Sistem Informasi Darlan Ismail dan Absensi</p>
                </td>
                <td style="width: 50%; text-align: right; border: none;">
                    <p>© {{ date('Y') }} Muhammad Sulaiman Hafi &amp; Muhammad Hafidl Badali x RSUD HDI. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
