@extends('exports.pdf-header', ['title' => $title ?? 'Laporan Cuti'])

@section('content')
<h3>RIWAYAT PERMOHONAN CUTI PEGAWAI</h3>

<div class="info-box">
    <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 0;">
        <tr>
            <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                <table style="border: none; border-collapse: collapse; margin-top: 0;">
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Pegawai</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 11px; font-weight: bold; color: #111827;">{{ $worker->name }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">NIP</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $worker->nip ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                <table style="border: none; border-collapse: collapse; margin-top: 0;">
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Departemen</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $worker->department->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Tahun</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $filters['year'] ?? date('Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if(!empty($filters['status']))
    <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #a7f3d0;">
        <span style="font-size: 9px; color: #6b7280;">Filter Status:</span>
        <span style="font-size: 9px; font-weight: bold; color: #0f766e;">{{ ucfirst($filters['status']) }}</span>
    </div>
    @endif
</div>

@if(isset($summary))
<div class="summary-box" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
    <p class="summary-title">Ringkasan Permohonan Cuti</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Total: <strong>{{ $summary['total'] ?? 0 }}</strong></span>
            </td>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Menunggu: <strong style="color: #d97706;">{{ $summary['pending'] ?? 0 }}</strong></span>
            </td>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Disetujui: <strong style="color: #059669;">{{ $summary['approved'] ?? 0 }}</strong></span>
            </td>
            <td style="width: 25%;">
                <span style="font-size: 10px;">Ditolak: <strong style="color: #dc2626;">{{ $summary['rejected'] ?? 0 }}</strong></span>
            </td>
        </tr>
    </table>
</div>
@endif

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="20%">Jenis Cuti</th>
            <th width="14%">Tanggal Mulai</th>
            <th width="14%">Tanggal Selesai</th>
            <th class="text-center" width="8%">Durasi</th>
            <th width="27%">Alasan</th>
            <th width="12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaves as $index => $leave)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $leave->leaveType->name ?? '-' }}</td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}</td>
            <td class="nowrap">{{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}</td>
            <td class="text-center">{{ $leave->total_days }} hari</td>
            <td>{{ \Illuminate\Support\Str::limit($leave->reason, 60) }}</td>
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
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="empty-state">Tidak ada data permohonan cuti</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
