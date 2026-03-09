@extends('exports.pdf-header', ['title' => 'Laporan Lembur'])

@section('content')
<h3>LAPORAN PERMOHONAN LEMBUR</h3>

<div class="info-box">
    <p><strong>Periode:</strong> {{ $dateFrom }} s/d {{ $dateTo }}</p>
    @if(isset($status) && $status)
    <p><strong>Status:</strong>
        @php
            $statusLabel = match($status) {
                'pending' => 'Menunggu Persetujuan',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                default => ucfirst($status)
            };
        @endphp
        {{ $statusLabel }}
    </p>
    @endif
    <p><strong>Total Data:</strong> {{ $overtimes->count() }} permohonan</p>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="15%">Pegawai</th>
            <th width="12%">Tanggal</th>
            <th width="10%">Waktu Mulai</th>
            <th width="10%">Waktu Selesai</th>
            <th width="8%" class="text-center">Durasi</th>
            <th width="12%">Status</th>
            <th width="12%">Disetujui Oleh</th>
            <th width="16%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($overtimes as $index => $overtime)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                {{ $overtime->worker->name ?? '-' }}<br>
                <small style="color: #666;">{{ $overtime->worker->nip ?? '-' }}</small>
            </td>
            <td>{{ \Carbon\Carbon::parse($overtime->overtime_date)->translatedFormat('d M Y, l') }}</td>
            <td>{{ \Carbon\Carbon::parse($overtime->start_time)->format('H:i') }}</td>
            <td>{{ \Carbon\Carbon::parse($overtime->end_time)->format('H:i') }}</td>
            <td class="text-center">{{ number_format($overtime->total_hours, 1) }} jam</td>
            <td>
                @php
                    $statusClass = match($overtime->status) {
                        'approved' => 'badge-success',
                        'pending' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($overtime->status) {
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'rejected' => 'Ditolak',
                        default => ucfirst($overtime->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                @if($overtime->approved_at)
                <br><small style="color: #666;">{{ \Carbon\Carbon::parse($overtime->approved_at)->translatedFormat('d M Y') }}</small>
                @endif
            </td>
            <td>{{ $overtime->approver->name ?? '-' }}</td>
            <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($overtime->description, 50) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data permohonan lembur
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($overtimes->count() > 0)
<div class="summary-box" style="background-color: #dbeafe; border-color: #93c5fd;">
    <p style="margin: 5px 0; font-size: 10px;"><strong>Ringkasan:</strong></p>
    <p style="margin: 5px 0; font-size: 10px;">Total Permohonan: {{ $overtimes->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Disetujui: {{ $overtimes->where('status', 'approved')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Menunggu: {{ $overtimes->where('status', 'pending')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Ditolak: {{ $overtimes->where('status', 'rejected')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Total Jam Lembur: {{ number_format($overtimes->where('status', 'approved')->sum('total_hours'), 1) }} jam</p>
</div>
@endif
@endsection
