@extends('exports.pdf-header', ['title' => 'Laporan Cuti'])

@section('content')
<h3>Laporan Permohonan Cuti</h3>

<div class="info-box">
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Periode</strong></td>
            <td style="width: 2%;">:</td>
            <td>{{ $filters['date_from'] ?? '-' }} s/d {{ $filters['date_to'] ?? '-' }}</td>
        </tr>
        @if(!empty($filters['status']))
        <tr>
            <td><strong>Status</strong></td>
            <td>:</td>
            <td>
                @php
                    $statusFilterLabel = match($filters['status']) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($filters['status'])
                    };
                @endphp
                <span class="badge badge-warning">{{ $statusFilterLabel }}</span>
            </td>
        </tr>
        @endif
        <tr>
            <td><strong>Total Data</strong></td>
            <td>:</td>
            <td><strong>{{ $leaves->count() }} permohonan cuti</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="11%">Tgl Pengajuan</th>
            <th width="10%">NIP</th>
            <th width="17%">Nama Pegawai</th>
            <th width="14%">Jenis Cuti</th>
            <th width="10%">Mulai</th>
            <th width="10%">Selesai</th>
            <th width="8%">Durasi</th>
            <th width="12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaves as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="nowrap">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td class="muted nowrap">{{ $item->worker->nip ?? '-' }}</td>
                <td class="wrap-2"><strong>{{ $item->worker->name ?? '-' }}</strong></td>
                <td class="wrap-2">{{ $item->leaveType->name ?? '-' }}</td>
                <td class="nowrap">{{ $item->start_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="nowrap">{{ $item->end_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="text-center">{{ $item->total_days ?? 0 }} hari</td>
                <td>
                    @php
                        $statusClass = match($item->status) {
                            'approved' => 'badge-success',
                            'pending' => 'badge-warning',
                            'rejected' => 'badge-danger',
                            default => 'badge-secondary'
                        };
                        $statusLabel = match($item->status) {
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => ucfirst($item->status ?? '-')
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="empty-state">Tidak ada data permohonan cuti untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Total Permohonan:</strong> {{ $leaves->count() }}</td>
            <td><strong>Disetujui:</strong> {{ $leaves->where('status', 'approved')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Menunggu:</strong> {{ $leaves->where('status', 'pending')->count() }}</td>
            <td><strong>Ditolak:</strong> {{ $leaves->where('status', 'rejected')->count() }}</td>
        </tr>
    </table>
</div>
@endsection
