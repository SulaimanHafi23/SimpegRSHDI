@extends('exports.pdf-header', ['title' => 'Laporan Cuti'])

@section('content')
<h3>LAPORAN PERMOHONAN CUTI</h3>

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
    <p><strong>Total Data:</strong> {{ $leaves->count() }} permohonan</p>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="15%">Pegawai</th>
            <th width="12%">Jenis Cuti</th>
            <th width="10%">Tanggal Mulai</th>
            <th width="10%">Tanggal Selesai</th>
            <th width="8%" class="text-center">Durasi</th>
            <th width="12%">Status</th>
            <th width="12%">Disetujui Oleh</th>
            <th width="16%">Alasan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leaves as $index => $leave)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>
                {{ $leave->worker->name ?? '-' }}<br>
                <small style="color: #666;">{{ $leave->worker->nip ?? '-' }}</small>
            </td>
            <td>{{ $leave->leaveType->name ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}</td>
            <td class="text-center">{{ $leave->total_days }} hari</td>
            <td>
                @php
                    $statusClass = match($leave->status) {
                        'approved' => 'badge-success',
                        'pending' => 'badge-warning',
                        'rejected' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($leave->status) {
                        'approved' => 'Disetujui',
                        'pending' => 'Menunggu',
                        'rejected' => 'Ditolak',
                        default => ucfirst($leave->status)
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                @if($leave->approved_at)
                <br><small style="color: #666;">{{ \Carbon\Carbon::parse($leave->approved_at)->translatedFormat('d M Y') }}</small>
                @endif
            </td>
            <td>{{ $leave->approver->name ?? '-' }}</td>
            <td style="font-size: 9px;">{{ \Illuminate\Support\Str::limit($leave->reason, 50) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data permohonan cuti
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($leaves->count() > 0)
<div style="margin-top: 20px; padding: 10px; background-color: #fef3c7; border-radius: 4px;">
    <p style="margin: 5px 0; font-size: 10px;"><strong>Ringkasan:</strong></p>
    <p style="margin: 5px 0; font-size: 10px;">Total Permohonan: {{ $leaves->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Disetujui: {{ $leaves->where('status', 'approved')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Menunggu: {{ $leaves->where('status', 'pending')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Ditolak: {{ $leaves->where('status', 'rejected')->count() }}</p>
    <p style="margin: 5px 0; font-size: 10px;">Total Hari Cuti: {{ $leaves->where('status', 'approved')->sum('total_days') }} hari</p>
</div>
@endif
@endsection
