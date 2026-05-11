@extends('exports.pdf-header', ['title' => $title ?? 'Laporan Perjalanan Dinas'])

@section('content')
    <h3>RIWAYAT PERJALANAN DINAS PEGAWAI</h3>

    <div class="info-box">
        <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 0;">
            <tr>
                <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                    <table style="border: none; border-collapse: collapse; margin-top: 0;">
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Pegawai
                            </td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 11px; font-weight: bold; color: #111827;">
                                {{ $worker->name }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">NIP</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">
                                {{ $worker->nip ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                    <table style="border: none; border-collapse: collapse; margin-top: 0;">
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">
                                Departemen</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">
                                {{ $worker->department->name ?? '-' }}</td>
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
                    $filterStatusLabel = match ($filters['status']) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($filters['status'])
                    };
                @endphp
                <span style="font-size: 9px; font-weight: bold; color: #0f766e;">{{ $filterStatusLabel }}</span>
            </div>
        @endif
    </div>

    <div class="summary-box" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
        <p class="summary-title">Ringkasan Perjalanan Dinas</p>
        <table class="summary-grid">
            <tr>
                <td style="width: 20%;">
                    <span style="font-size: 10px;">Total: <strong>{{ $trips->count() }}</strong></span>
                </td>
                <td style="width: 20%;">
                    <span style="font-size: 10px;">Menunggu: <strong
                            style="color: #d97706;">{{ $trips->where('status', 'pending')->count() }}</strong></span>
                </td>
                <td style="width: 20%;">
                    <span style="font-size: 10px;">Disetujui: <strong
                            style="color: #059669;">{{ $trips->where('status', 'approved')->count() }}</strong></span>
                </td>
                <td style="width: 20%;">
                    <span style="font-size: 10px;">Ditolak: <strong
                            style="color: #dc2626;">{{ $trips->where('status', 'rejected')->count() }}</strong></span>
                </td>
                <td style="width: 20%;">
                    <span style="font-size: 10px;">Dibatalkan: <strong
                            style="color: #6b7280;">{{ $trips->where('status', 'cancelled')->count() }}</strong></span>
                </td>
            </tr>
            <tr>
                @php
                    $totalDays = $trips->where('status', 'approved')->sum(fn($t) => (float) $t->duration_value);
                    $totalEstimatedCost = $trips->sum('estimated_cost');
                @endphp
                <td colspan="3">
                    <span style="font-size: 10px;">Total Hari (Disetujui): <strong
                            style="color: #0f766e;">{{ rtrim(rtrim(number_format($totalDays, 1, '.', ''), '0'), '.') }}
                            hari</strong></span>
                </td>
                <td colspan="2">
                    <span style="font-size: 10px;">Total Estimasi: <strong style="color: #0f766e;">Rp
                            {{ number_format($totalEstimatedCost, 0, ',', '.') }}</strong></span>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">No</th>
                <th width="16%">Tujuan</th>
                <th width="20%">Keperluan</th>
                <th width="12%">Tgl Mulai</th>
                <th width="12%">Tgl Selesai</th>
                <th class="text-center" width="8%">Durasi</th>
                <th class="text-right" width="14%">Est. Biaya</th>
                <th width="14%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $index => $trip)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $trip->destination ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($trip->purpose, 70) ?? '-' }}</td>
                    <td class="nowrap">{{ $trip->start_date?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td class="nowrap">{{ $trip->end_date?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td class="text-center">{{ $trip->duration_label }}</td>
                    <td class="text-right">
                        {{ $trip->estimated_cost ? 'Rp ' . number_format($trip->estimated_cost, 0, ',', '.') : '-' }}</td>
                    <td>
                        @php
                            $statusClass = match ($trip->status) {
                                'approved' => 'badge-success',
                                'pending', 'manager_verified' => 'badge-warning',
                                'rejected' => 'badge-danger',
                                'cancelled' => 'badge-secondary',
                                default => 'badge-secondary'
                            };
                            $statusLabel = match ($trip->status) {
                                'approved' => 'Disetujui',
                                'pending' => 'Menunggu',
                                'manager_verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($trip->status)
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-state">Tidak ada data perjalanan dinas</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $approvedTrips = $trips->where('status', 'approved');
        $totalApprovedCost = $approvedTrips->sum('estimated_cost');
        $totalApprovedDays = $approvedTrips->sum(fn($t) => (float) $t->duration_value);
    @endphp

    @if($approvedTrips->count() > 0)
        <div class="summary-box" style="margin-top: 8px;">
            <p class="summary-title">Total Biaya Perjalanan Dinas (Disetujui)</p>
            <table class="summary-grid">
                <tr>
                    <td style="width: 33%;"><strong>Jumlah Perjalanan:</strong> {{ $approvedTrips->count() }}</td>
                    <td style="width: 33%;"><strong>Total Durasi:</strong>
                        {{ rtrim(rtrim(number_format($totalApprovedDays, 1, '.', ''), '0'), '.') }} hari</td>
                    <td style="width: 34%;"><strong>Total Estimasi Biaya:</strong> <span
                            style="color: #0f766e; font-size: 11px;">Rp
                            {{ number_format($totalApprovedCost, 0, ',', '.') }}</span></td>
                </tr>
            </table>
        </div>
    @endif
@endsection