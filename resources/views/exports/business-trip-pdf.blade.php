@extends('exports.pdf-header', ['title' => 'Laporan Perjalanan Dinas'])

@section('content')
<h3>LAPORAN PERJALANAN DINAS</h3>

<div class="info-box">
    <p><strong>Periode:</strong> {{ $dateFrom }} s/d {{ $dateTo }}</p>
    @if(isset($status) && $status)
    <p><strong>Status:</strong>
        @php
            $statusLabel = match($status) {
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
                default => ucfirst($status)
            };
        @endphp
        {{ $statusLabel }}
    </p>
    @endif
    <p><strong>Total Data:</strong> {{ $trips->count() }} perjalanan</p>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="14%">Pegawai</th>
            <th width="10%">Tanggal Mulai</th>
            <th width="10%">Tanggal Selesai</th>
            <th width="8%" class="text-center">Durasi</th>
            <th width="14%">Tujuan</th>
            <th width="12%">Estimasi Biaya</th>
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
                {{ $trip->worker->name ?? '-' }}<br>
                <small style="color: #666;">{{ $trip->worker->nip ?? '-' }}</small>
            </td>
            <td>{{ \Carbon\Carbon::parse($trip->start_date)->translatedFormat('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($trip->end_date)->translatedFormat('d M Y') }}</td>
            <td class="text-center">{{ $trip->duration_label }}</td>
            <td>{{ $trip->destination }}</td>
            <td>Rp {{ number_format($trip->estimated_cost ?? 0, 0, ',', '.') }}</td>
            <td>
                @php
                    $statusClass = match($trip->status) {
                        'approved' => 'badge-success',
                        'pending' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($trip->status) {
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($trip->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </td>
            <td>{{ $trip->approvedBy->name ?? '-' }}</td>
            <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($trip->purpose, 70) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data perjalanan dinas
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($trips->count() > 0)
<div style="margin-top: 20px; padding: 10px; background-color: #e0e7ff; border-radius: 4px;">
    <p style="margin: 5px 0; font-size: 10px;"><strong>Ringkasan:</strong></p>
    <p style="margin: 5px 0; font-size: 10px;">Total Perjalanan: {{ $trips->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Disetujui: {{ $trips->where('status', 'approved')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Menunggu: {{ $trips->where('status', 'pending')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Ditolak: {{ $trips->where('status', 'rejected')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Dibatalkan: {{ $trips->where('status', 'cancelled')->count() }}</p>
    @php
        $totalDays = $trips->where('status', 'approved')->sum(fn($t) => (float) $t->duration_value);
        $totalEstimatedCost = $trips->sum('estimated_cost');
    @endphp
    <p style="margin: 5px 0; font-size: 10px;">Total Hari Perjalanan: {{ rtrim(rtrim(number_format($totalDays, 1, '.', ''), '0'), '.') }} hari</p>
    <p style="margin: 5px 0; font-size: 10px;">Total Estimasi Biaya: Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}</p>
</div>
@endif
@endsection
