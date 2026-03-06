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
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            color: #16a34a;
            font-size: 13px;
        }
        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
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
            background-color: #16a34a;
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
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .badge-secondary { background-color: #e5e7eb; color: #374151; }
        .badge-purple { background-color: #ede9fe; color: #5b21b6; }
        .text-center { text-align: center; }
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
        <h1>LAPORAN TUKAR SHIFT</h1>
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
        @if(!empty($filters['partner_name']))
        <div class="info-row">
            <span class="info-label">Partner:</span> {{ $filters['partner_name'] }}
        </div>
        @endif
        @if(!empty($filters['status']))
        <div class="info-row">
            <span class="info-label">Status:</span>
            @php
                $statusLabel = match($filters['status']) {
                    'pending' => 'Menunggu',
                    'accepted' => 'Diterima',
                    'awaiting_approval' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled' => 'Dibatalkan',
                    'executed' => 'Dieksekusi',
                    default => ucfirst($filters['status'])
                };
            @endphp
            {{ $statusLabel }}
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total Data:</span> {{ $swaps->count() }} permintaan
        </div>
    </div>

    <div class="summary">
        <h3>Ringkasan</h3>
        <div>
            <span class="summary-item">Total: {{ $swaps->count() }}</span>
            <span class="summary-item">Menunggu: {{ $swaps->whereIn('status', ['pending', 'awaiting_approval'])->count() }}</span>
            <span class="summary-item">Disetujui: {{ $swaps->whereIn('status', ['approved', 'accepted'])->count() }}</span>
            <span class="summary-item">Ditolak: {{ $swaps->where('status', 'rejected')->count() }}</span>
            <span class="summary-item">Dieksekusi: {{ $swaps->where('status', 'executed')->count() }}</span>
            <span class="summary-item">Dibatalkan: {{ $swaps->where('status', 'cancelled')->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">No</th>
                <th width="8%">Tgl Ajuan</th>
                <th width="8%">Tgl Tukar</th>
                <th width="7%">Tipe</th>
                <th width="14%">Pemohon</th>
                <th width="10%">Shift Pemohon</th>
                <th width="14%">Target</th>
                <th width="10%">Shift Target</th>
                <th width="10%">Status</th>
                <th width="15%">Alasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($swaps as $index => $swap)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $swap->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                <td>{{ $swap->swap_date?->translatedFormat('d M Y') ?? '-' }}</td>
                <td>
                    @php
                        $typeLabel = match($swap->swap_type) {
                            'direct' => 'Langsung',
                            'open' => 'Open',
                            default => ucfirst($swap->swap_type ?? '-')
                        };
                    @endphp
                    {{ $typeLabel }}
                </td>
                <td>
                    {{ $swap->requester->name ?? '-' }}
                    @if($swap->requester->department ?? null)
                    <br><small style="color:#666;">{{ $swap->requester->department->name }}</small>
                    @endif
                </td>
                <td>{{ $swap->requesterShift->shift->name ?? '-' }}</td>
                <td>
                    {{ $swap->targetWorker->name ?? '(Open)' }}
                    @if($swap->targetWorker->department ?? null)
                    <br><small style="color:#666;">{{ $swap->targetWorker->department->name }}</small>
                    @endif
                </td>
                <td>{{ $swap->targetShift->shift->name ?? '-' }}</td>
                <td>
                    @php
                        $statusClass = match($swap->status) {
                            'approved', 'accepted' => 'badge-success',
                            'pending' => 'badge-warning',
                            'awaiting_approval' => 'badge-info',
                            'rejected' => 'badge-danger',
                            'executed' => 'badge-purple',
                            'cancelled' => 'badge-secondary',
                            default => 'badge-secondary'
                        };
                        $statusLabel = match($swap->status) {
                            'pending' => 'Menunggu',
                            'accepted' => 'Diterima',
                            'awaiting_approval' => 'Mng. Persetujuan',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'cancelled' => 'Dibatalkan',
                            'executed' => 'Dieksekusi',
                            default => ucfirst($swap->status ?? '-')
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td style="font-size: 8px;">{{ \Illuminate\Support\Str::limit($swap->reason, 40) ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #666;">
                    Tidak ada data tukar shift
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
        <p>UPTD RSUD Haji Darlan Ismail - Sistem Informasi Manajemen Kepegawaian</p>
    </div>
</body>
</html>
