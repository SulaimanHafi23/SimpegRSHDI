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
            border-bottom: 3px solid #8B5CF6;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #8B5CF6;
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
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            color: #8B5CF6;
            font-size: 13px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        th {
            background-color: #8B5CF6;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-secondary { background-color: #e5e7eb; color: #374151; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PERJALANAN DINAS</h1>
        @if(isset($worker))
        <p>Pegawai: {{ $worker->name }} ({{ $worker->nip ?? '-' }})</p>
        @endif
        <p>Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
    </div>

    <div class="info-section">
        @if(!empty($filters['date_from']) || !empty($filters['date_to']))
        <div class="info-row">
            <span class="info-label">Periode:</span>
            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') : 'Awal' }}
            s/d
            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') : 'Sekarang' }}
        </div>
        @endif
        @if(!empty($filters['status']))
        <div class="info-row">
            <span class="info-label">Status:</span>
            @php
                $statusLabel = match($filters['status']) {
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled' => 'Dibatalkan',
                    default => ucfirst($filters['status'])
                };
            @endphp
            {{ $statusLabel }}
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total Data:</span> {{ $trips->count() }} perjalanan
        </div>
    </div>

    <div class="summary">
        <h3>Ringkasan</h3>
        <div>
            <span class="summary-item">Total: {{ $trips->count() }}</span>
            <span class="summary-item">Menunggu: {{ $trips->where('status', 'pending')->count() }}</span>
            <span class="summary-item">Disetujui: {{ $trips->where('status', 'approved')->count() }}</span>
            <span class="summary-item">Ditolak: {{ $trips->where('status', 'rejected')->count() }}</span>
            <span class="summary-item">Dibatalkan: {{ $trips->where('status', 'cancelled')->count() }}</span>
            @php $totalDays = $trips->where('status', 'approved')->sum(fn($t) => $t->start_date && $t->end_date ? $t->start_date->diffInDays($t->end_date) + 1 : 0); @endphp
            <span class="summary-item">Total Hari (Disetujui): {{ $totalDays }} hari</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="18%">Tujuan</th>
                <th width="20%">Keperluan</th>
                <th width="12%">Tanggal Mulai</th>
                <th width="12%">Tanggal Selesai</th>
                <th class="text-center" width="8%">Durasi</th>
                <th class="text-right" width="13%">Estimasi Biaya</th>
                <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $index => $trip)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $trip->destination ?? '-' }}</td>
                <td style="font-size: 8px;">{{ \Illuminate\Support\Str::limit($trip->purpose, 80) ?? '-' }}</td>
                <td>{{ $trip->start_date?->translatedFormat('d M Y') ?? '-' }}</td>
                <td>{{ $trip->end_date?->translatedFormat('d M Y') ?? '-' }}</td>
                <td class="text-center">{{ $trip->start_date && $trip->end_date ? $trip->start_date->diffInDays($trip->end_date) + 1 : '-' }} hari</td>
                <td class="text-right">{{ $trip->estimated_cost ? 'Rp ' . number_format($trip->estimated_cost, 0, ',', '.') : '-' }}</td>
                <td>
                    @php
                        $statusClass = match($trip->status) {
                            'approved' => 'badge-success',
                            'pending' => 'badge-warning',
                            'rejected' => 'badge-danger',
                            default => 'badge-secondary'
                        };
                        $statusLabel = match($trip->status) {
                            'approved' => 'Disetujui',
                            'pending' => 'Menunggu',
                            'rejected' => 'Ditolak',
                            'cancelled' => 'Dibatalkan',
                            default => ucfirst($trip->status)
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #666;">
                    Tidak ada data perjalanan dinas
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh Sistem SIMPEG RSHDI</p>
        <p>{{ now()->translatedFormat('d F Y H:i:s') }} WITA</p>
    </div>
</body>
</html>
