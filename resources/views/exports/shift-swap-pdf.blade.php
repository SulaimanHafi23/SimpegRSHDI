@extends('exports.pdf-header', ['title' => 'Laporan Tukar Shift'])

@section('content')
<h3>LAPORAN PERMINTAAN TUKAR SHIFT</h3>

<div class="info-box">
    <table class="meta-table">
        @if(!empty($dateFrom) || !empty($dateTo))
        <tr>
            <td style="width: 18%;"><strong>Periode</strong></td>
            <td style="width: 2%;">:</td>
            <td>{{ $dateFrom ?? 'Awal' }} s/d {{ $dateTo ?? 'Sekarang' }}</td>
        </tr>
        @endif
        @if(!empty($status))
        <tr>
            <td><strong>Status</strong></td>
            <td>:</td>
            <td>
                @php
                    $statusLabel = match($status) {
                        'pending' => 'Menunggu Respon Pegawai',
                        'accepted' => 'Diterima',
                        'awaiting_approval' => 'Menunggu Persetujuan Atasan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        'executed' => 'Dieksekusi',
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
            <td><strong>{{ $swaps->count() }} permintaan</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="8%">Tgl Ajuan</th>
            <th width="8%">Tgl Tukar</th>
            <th width="6%">Tipe</th>
            <th width="12%">Pemohon</th>
            <th width="9%">Shift Pemohon</th>
            <th width="12%">Target</th>
            <th width="9%">Shift Target</th>
            <th width="9%">Status</th>
            <th width="10%">Disetujui</th>
            <th width="13%">Alasan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($swaps as $index => $swap)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="nowrap">{{ $swap->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
            <td class="nowrap">{{ $swap->swap_date?->translatedFormat('d M Y') ?? '-' }}</td>
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
                <strong>{{ $swap->requester->name ?? '-' }}</strong><br>
                <span class="muted">{{ $swap->requester->department->name ?? '-' }}</span>
            </td>
            <td>{{ $swap->requesterShift->shift->name ?? '-' }}</td>
            <td>
                <strong>{{ $swap->targetWorker->name ?? '(Open)' }}</strong><br>
                <span class="muted">{{ $swap->targetWorker->department->name ?? '-' }}</span>
            </td>
            <td>{{ $swap->targetShift->shift->name ?? '-' }}</td>
            <td>
                @php
                    $statusClass = match($swap->status) {
                        'approved', 'accepted' => 'badge-success',
                        'pending' => 'badge-warning',
                        'awaiting_approval' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        'executed' => 'badge-success',
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
            <td class="nowrap">
                @if($swap->manager_approved_at)
                    {{ \Carbon\Carbon::parse($swap->manager_approved_at)->translatedFormat('d M Y') }}
                @else
                    -
                @endif
            </td>
            <td class="wrap-2">{{ \Illuminate\Support\Str::limit($swap->reason, 40) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="empty-state">Tidak ada data tukar shift untuk periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($swaps->count() > 0)
<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 33%;"><strong>Total Permintaan:</strong> {{ $swaps->count() }}</td>
            <td style="width: 33%;"><strong>Menunggu Respon:</strong> {{ $swaps->where('status', 'pending')->count() }}</td>
            <td style="width: 34%;"><strong>Menunggu Persetujuan:</strong> {{ $swaps->where('status', 'awaiting_approval')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Disetujui:</strong> {{ $swaps->whereIn('status', ['approved', 'accepted'])->count() }}</td>
            <td><strong>Ditolak:</strong> {{ $swaps->where('status', 'rejected')->count() }}</td>
            <td><strong>Dieksekusi:</strong> {{ $swaps->where('status', 'executed')->count() }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>Dibatalkan:</strong> {{ $swaps->where('status', 'cancelled')->count() }}</td>
        </tr>
    </table>
</div>
@endif
@endsection
