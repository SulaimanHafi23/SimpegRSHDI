@extends('exports.pdf-header', ['title' => $title ?? 'Laporan Tukar Shift'])

@section('content')
<h3>RIWAYAT TUKAR SHIFT PEGAWAI</h3>

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
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Periode</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">
                            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') : 'Awal' }}
                            s/d
                            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') : 'Sekarang' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if(!empty($filters['status']))
    <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #a7f3d0;">
        <span style="font-size: 9px; color: #6b7280;">Filter Status:</span>
        @php
            $filterStatusLabel = match($filters['status']) {
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
        <span style="font-size: 9px; font-weight: bold; color: #0f766e;">{{ $filterStatusLabel }}</span>
    </div>
    @endif
</div>

<div class="summary-box" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
    <p class="summary-title">Ringkasan Tukar Shift</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 16%;">
                <span style="font-size: 10px;">Total: <strong>{{ $swaps->count() }}</strong></span>
            </td>
            <td style="width: 16%;">
                <span style="font-size: 10px;">Menunggu: <strong style="color: #d97706;">{{ $swaps->whereIn('status', ['pending', 'awaiting_approval'])->count() }}</strong></span>
            </td>
            <td style="width: 16%;">
                <span style="font-size: 10px;">Diterima: <strong style="color: #2563eb;">{{ $swaps->where('status', 'accepted')->count() }}</strong></span>
            </td>
            <td style="width: 16%;">
                <span style="font-size: 10px;">Disetujui: <strong style="color: #059669;">{{ $swaps->where('status', 'approved')->count() }}</strong></span>
            </td>
            <td style="width: 16%;">
                <span style="font-size: 10px;">Ditolak: <strong style="color: #dc2626;">{{ $swaps->where('status', 'rejected')->count() }}</strong></span>
            </td>
            <td style="width: 20%;">
                <span style="font-size: 10px;">Dieksekusi: <strong style="color: #7c3aed;">{{ $swaps->where('status', 'executed')->count() }}</strong></span>
            </td>
        </tr>
    </table>
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
                {{ $swap->requester->name ?? '-' }}
                @if($swap->requester->department ?? null)
                <br><span class="muted" style="font-size: 8px;">{{ $swap->requester->department->name }}</span>
                @endif
            </td>
            <td>{{ $swap->requesterShift->shift->name ?? '-' }}</td>
            <td>
                {{ $swap->targetWorker->name ?? '(Open)' }}
                @if($swap->targetWorker->department ?? null)
                <br><span class="muted" style="font-size: 8px;">{{ $swap->targetWorker->department->name }}</span>
                @endif
            </td>
            <td>{{ $swap->targetShift->shift->name ?? '-' }}</td>
            <td>
                @php
                    $statusClass = match($swap->status) {
                        'approved', 'accepted' => 'badge-success',
                        'pending' => 'badge-warning',
                        'awaiting_approval' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        'executed' => 'badge-secondary',
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
            <td>{{ \Illuminate\Support\Str::limit($swap->reason, 40) ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="empty-state">Tidak ada data tukar shift</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
