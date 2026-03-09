@extends('exports.pdf-header', ['title' => 'Laporan Lembur'])

@section('content')
<h3>Laporan Permohonan Lembur</h3>

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
            <td><strong>{{ $overtimes->count() }} permohonan lembur</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="10%">Tgl Pengajuan</th>
            <th width="10%">NIP</th>
            <th width="16%">Nama Pegawai</th>
            <th width="11%">Tgl Lembur</th>
            <th width="8%">Mulai</th>
            <th width="8%">Selesai</th>
            <th width="21%">Alasan</th>
            <th width="12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($overtimes as $index => $item)
            @php
                $startTime = $item->start_time ? \Carbon\Carbon::parse($item->start_time) : null;
                $endTime = $item->end_time ? \Carbon\Carbon::parse($item->end_time) : null;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="nowrap">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td class="muted nowrap">{{ $item->worker->nip ?? '-' }}</td>
                <td class="wrap-2"><strong>{{ $item->worker->name ?? '-' }}</strong></td>
                <td class="nowrap">{{ $item->overtime_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="text-center nowrap">{{ $startTime?->format('H:i') ?? '-' }}</td>
                <td class="text-center nowrap">{{ $endTime?->format('H:i') ?? '-' }}</td>
                <td class="wrap-3">{{ \Illuminate\Support\Str::limit($item->reason ?? '-', 90) }}</td>
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
                <td colspan="9" class="empty-state">Tidak ada data permohonan lembur untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

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
    </table>
</div>
@endsection
