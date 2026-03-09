@extends('exports.pdf-header', ['title' => 'Laporan Lembur'])

@section('content')
<h3>Laporan Permohonan Lembur</h3>

<div class="info-box">
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Periode</strong></td>
            <td style="width: 2%;">:</td>
            <td>{{ $dateFrom }} s/d {{ $dateTo }}</td>
        </tr>
        @if(isset($status) && $status)
        <tr>
            <td><strong>Status</strong></td>
            <td>:</td>
            <td>
                @php
                    $statusLabel = match($status) {
                        'pending' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
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
            <td><strong>{{ $overtimes->count() }} permohonan</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="16%">Pegawai</th>
            <th width="14%">Tanggal</th>
            <th width="8%">Mulai</th>
            <th width="8%">Selesai</th>
            <th width="8%" class="text-center">Durasi</th>
            <th width="11%">Status</th>
            <th width="13%">Disetujui Oleh</th>
            <th width="18%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($overtimes as $index => $overtime)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <strong>{{ $overtime->worker->name ?? '-' }}</strong><br>
                <span class="muted">{{ $overtime->worker->nip ?? '-' }}</span>
            </td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($overtime->overtime_date)->translatedFormat('d M Y') }}</td>
            <td class="text-center nowrap">{{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}</td>
            <td class="text-center nowrap">{{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}</td>
            <td class="text-center nowrap">{{ number_format($overtime->total_hours, 1) }} jam</td>
            <td>
                @php
                    $statusClass = match($overtime->status) {
                        'approved' => 'badge-success',
                        'pending' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($overtime->status) {
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'rejected' => 'Ditolak',
                        default => ucfirst($overtime->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                @if($overtime->approved_at)
                <br><span class="muted">{{ \Carbon\Carbon::parse($overtime->approved_at)->translatedFormat('d M Y') }}</span>
                @endif
            </td>
            <td class="wrap-2">{{ $overtime->approver->name ?? '-' }}</td>
            <td class="wrap-3">{{ \Illuminate\Support\Str::limit($overtime->description, 80) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="empty-state">Tidak ada data permohonan lembur untuk periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($overtimes->count() > 0)
<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Total Permohonan:</strong> {{ $overtimes->count() }}</td>
            <td><strong>Disetujui:</strong> {{ $overtimes->where('status', 'approved')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Menunggu:</strong> {{ $overtimes->where('status', 'pending')->count() }}</td>
            <td><strong>Ditolak:</strong> {{ $overtimes->where('status', 'rejected')->count() }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Total Jam Lembur Disetujui:</strong> {{ number_format($overtimes->where('status', 'approved')->sum('total_hours'), 1) }} jam</td>
        </tr>
    </table>
</div>
@endif
@endsection
