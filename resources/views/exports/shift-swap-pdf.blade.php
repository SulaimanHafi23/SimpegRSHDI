@extends('exports.pdf-header', ['title' => 'Laporan Tukar Shift'])

@section('content')
<h3>LAPORAN PERMINTAAN TUKAR SHIFT</h3>

<div class="info-box">
    @if(!empty($dateFrom) || !empty($dateTo))
    <p><strong>Periode:</strong> {{ $dateFrom ?? 'Awal' }} s/d {{ $dateTo ?? 'Sekarang' }}</p>
    @endif
    @if(!empty($status))
    <p><strong>Status:</strong>
        @php
            $statusLabel = match($status) {
                'pending' => 'Menunggu',
                'accepted' => 'Diterima',
                'awaiting_approval' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
                'executed' => 'Dieksekusi',
                default => ucfirst($status)
            };
        @endphp
        {{ $statusLabel }}
    </p>
    @endif
    <p><strong>Total Data:</strong> {{ $swaps->count() }} permintaan</p>
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
                {{ $swap->requester->name ?? '-' }}<br>
                <small style="color: #666;">{{ $swap->requester->department->name ?? '-' }}</small>
            </td>
            <td>{{ $swap->requesterShift->shift->name ?? '-' }}</td>
            <td>
                {{ $swap->targetWorker->name ?? '(Open)' }}<br>
                <small style="color: #666;">{{ $swap->targetWorker->department->name ?? '-' }}</small>
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
            <td>
                @if($swap->manager_approved_at)
                    {{ \Carbon\Carbon::parse($swap->manager_approved_at)->translatedFormat('d M Y') }}
                @else
                    -
                @endif
            </td>
            <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($swap->reason, 40) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data tukar shift
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($swaps->count() > 0)
<div style="margin-top: 20px; padding: 10px; background-color: #dbeafe; border-radius: 4px;">
    <p style="margin: 5px 0; font-size: 10px;"><strong>Ringkasan:</strong></p>
    <p style="margin: 5px 0; font-size: 10px;">Total Permintaan: {{ $swaps->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Menunggu: {{ $swaps->whereIn('status', ['pending', 'awaiting_approval'])->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Disetujui: {{ $swaps->whereIn('status', ['approved', 'accepted'])->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Ditolak: {{ $swaps->where('status', 'rejected')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Dieksekusi: {{ $swaps->where('status', 'executed')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Dibatalkan: {{ $swaps->where('status', 'cancelled')->count() }}</p>
</div>
@endif
@endsection
