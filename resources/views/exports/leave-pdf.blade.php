@extends('exports.pdf-header', ['title' => 'Laporan Cuti'])

@section('content')
<h3>LAPORAN PERMOHONAN CUTI</h3>

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
            <td><strong>{{ $leaves->count() }} permohonan</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="16%">Pegawai</th>
            <th width="14%">Jenis Cuti</th>
            <th width="10%">Mulai</th>
            <th width="10%">Selesai</th>
            <th width="8%" class="text-center">Durasi</th>
            <th width="11%">Status</th>
            <th width="13%">Disetujui Oleh</th>
            <th width="14%">Alasan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaves as $index => $leave)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                <strong>{{ $leave->worker->name ?? '-' }}</strong><br>
                <span class="muted">{{ $leave->worker->nip ?? '-' }}</span>
            </td>
            <td>{{ $leave->leaveType->name ?? '-' }}</td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}</td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}</td>
            <td class="text-center nowrap">{{ $leave->total_days }} hari</td>
            <td>
                @php
                    $statusClass = match($leave->status) {
                        'approved' => 'badge-success',
                        'pending', 'manager_verified' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        'cancelled' => 'badge-secondary',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($leave->status) {
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'manager_verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($leave->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                @if($leave->approved_at)
                <br><span class="muted">{{ \Carbon\Carbon::parse($leave->approved_at)->translatedFormat('d M Y') }}</span>
                @endif
            </td>
            <td class="wrap-2">{{ $leave->approver->name ?? '-' }}</td>
            <td class="wrap-3">{{ \Illuminate\Support\Str::limit($leave->reason, 70) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="empty-state">Tidak ada data permohonan cuti untuk periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($leaves->count() > 0)
<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Total Permohonan:</strong> {{ $leaves->count() }}</td>
            <td><strong>Disetujui:</strong> {{ $leaves->where('status', 'approved')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Menunggu:</strong> {{ $leaves->whereIn('status', ['pending', 'manager_verified'])->count() }}</td>
            <td><strong>Ditolak:</strong> {{ $leaves->where('status', 'rejected')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Dibatalkan:</strong> {{ $leaves->where('status', 'cancelled')->count() }}</td>
            <td><strong>Total Hari Disetujui:</strong> {{ $leaves->where('status', 'approved')->sum('total_days') }} hari</td>
        </tr>
    </table>
</div>
@endif
@endsection
