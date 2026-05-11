@extends('exports.pdf-header', ['title' => 'Laporan Perjalanan Dinas'])

@section('content')
<h3>LAPORAN PERJALANAN DINAS</h3>

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
                        'manager_verified' => 'Terverifikasi Manager',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
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
            <td><strong>{{ $trips->count() }} perjalanan</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="14%">Pegawai</th>
            <th width="10%">Tgl Mulai</th>
            <th width="10%">Tgl Selesai</th>
            <th width="8%" class="text-center">Durasi</th>
            <th width="14%">Tujuan</th>
            <th width="12%">Est. Biaya</th>
            <th width="9%">Status</th>
            <th width="11%">Disetujui Oleh</th>
            <th width="16%">Keperluan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($trips as $index => $trip)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <strong>{{ $trip->worker->name ?? '-' }}</strong><br>
                <span class="muted">{{ $trip->worker->nip ?? '-' }}</span>
            </td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($trip->start_date)->translatedFormat('d M Y') }}</td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($trip->end_date)->translatedFormat('d M Y') }}</td>
            <td class="text-center">{{ $trip->duration_label }}</td>
            <td>{{ $trip->destination }}</td>
            <td>Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}</td>
            <td>
                @php
                    $statusClass = match($trip->status) {
                        'approved' => 'badge-success',
                        'pending', 'manager_verified' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        'cancelled' => 'badge-secondary',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($trip->status) {
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'manager_verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($trip->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </td>
            <td class="wrap-2">{{ $trip->approvedBy->name ?? '-' }}</td>
            <td class="wrap-3">{{ \Illuminate\Support\Str::limit($trip->purpose, 70) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="empty-state">Tidak ada data perjalanan dinas untuk periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($trips->count() > 0)
<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Total Perjalanan:</strong> {{ $trips->count() }}</td>
            <td><strong>Disetujui:</strong> {{ $trips->where('status', 'approved')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Menunggu:</strong> {{ $trips->where('status', 'pending')->count() }}</td>
            <td><strong>Ditolak:</strong> {{ $trips->where('status', 'rejected')->count() }}</td>
        </tr>
        @php
            $approvedTrips = $trips->where('status', 'approved');
            $totalDays = $approvedTrips->sum(fn($t) => (float) $t->duration_value);
            $totalEstimatedCost = $trips->sum('estimated_cost');
            $totalApprovedCost = $approvedTrips->sum('estimated_cost');
        @endphp
        <tr>
            <td><strong>Total Hari (Disetujui):</strong> {{ rtrim(rtrim(number_format($totalDays, 1, '.', ''), '0'), '.') }} hari</td>
            <td><strong>Total Estimasi Biaya:</strong> Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>
@endif

@php
    $approvedTrips = $trips->where('status', 'approved');
    $totalApprovedCost = $approvedTrips->sum('estimated_cost');
    $totalApprovedDays = $approvedTrips->sum(fn($t) => (float) $t->duration_value);
@endphp

@if($approvedTrips->count() > 0)
<div class="summary-box" style="margin-top: 8px;">
    <p class="summary-title">Total Biaya Perjalanan Dinas (Disetujui)</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 33%;"><strong>Jumlah Perjalanan:</strong> {{ $approvedTrips->count() }}</td>
            <td style="width: 33%;"><strong>Total Durasi:</strong> {{ rtrim(rtrim(number_format($totalApprovedDays, 1, '.', ''), '0'), '.') }} hari</td>
            <td style="width: 34%;"><strong>Total Estimasi Biaya:</strong> <span style="color: #0f766e; font-size: 11px;">Rp {{ number_format($totalApprovedCost, 0, ',', '.') }}</span></td>
        </tr>
    </table>
</div>
@endif
@endsection

