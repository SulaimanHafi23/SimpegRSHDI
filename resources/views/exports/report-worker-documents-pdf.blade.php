@extends('exports.pdf-header', ['title' => 'Laporan Dokumen Pegawai'])

@section('content')
<h3>Laporan Dokumen Pegawai</h3>

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
                        'verified' => 'Terverifikasi',
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
            <td><strong>{{ $documents->count() }} dokumen</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="11%">NIP</th>
            <th width="16%">Nama Pegawai</th>
            <th width="16%">Jenis Dokumen</th>
            <th width="12%">Tgl Upload</th>
            <th width="12%">Kadaluarsa</th>
            <th width="11%">Status</th>
            <th width="18%">Diverifikasi Oleh</th>
        </tr>
    </thead>
    <tbody>
        @forelse($documents as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="muted nowrap">{{ $item->worker->nip ?? '-' }}</td>
                <td class="wrap-2"><strong>{{ $item->worker->name ?? '-' }}</strong></td>
                <td class="wrap-2">{{ $item->documentType->name ?? '-' }}</td>
                <td class="nowrap">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td class="nowrap">{{ $item->expired_date?->format('d/m/Y') ?? '-' }}</td>
                <td>
                    @php
                        $statusClass = match($item->status) {
                            'verified' => 'badge-success',
                            'pending' => 'badge-warning',
                            'rejected' => 'badge-danger',
                            default => 'badge-secondary'
                        };
                        $statusLabel = match($item->status) {
                            'pending' => 'Menunggu',
                            'verified' => 'Terverifikasi',
                            'rejected' => 'Ditolak',
                            default => ucfirst($item->status ?? '-')
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="wrap-2">{{ $item->verifier->name ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="empty-state">Tidak ada data dokumen untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Total Dokumen:</strong> {{ $documents->count() }}</td>
            <td><strong>Terverifikasi:</strong> {{ $documents->where('status', 'verified')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Menunggu:</strong> {{ $documents->where('status', 'pending')->count() }}</td>
            <td><strong>Ditolak:</strong> {{ $documents->where('status', 'rejected')->count() }}</td>
        </tr>
    </table>
</div>
@endsection
