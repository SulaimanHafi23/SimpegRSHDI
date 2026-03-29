@extends('exports.pdf-header', ['title' => 'Laporan Presensi'])

@section('content')
<h3>Laporan Presensi Pegawai</h3>

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
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'leave' => 'Cuti',
                        'sick' => 'Sakit',
                        'permission' => 'Izin',
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
            <td><strong>{{ $attendances->count() }} presensi</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center" width="4%">No</th>
            <th width="11%">Tanggal</th>
            <th width="10%">NIP</th>
            <th width="17%">Nama Pegawai</th>
            <th width="8%">Masuk</th>
            <th width="8%">Pulang</th>
            <th width="18%">Lokasi</th>
            <th width="11%">Status</th>
            <th width="13%">Terlambat</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="nowrap">{{ $item->attendance_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="muted nowrap">{{ $item->worker->nip ?? '-' }}</td>
                <td class="wrap-2"><strong>{{ $item->worker->name ?? '-' }}</strong></td>
                <td class="text-center nowrap">{{ $item->check_in?->format('H:i') ?? '-' }}</td>
                <td class="text-center nowrap">{{ $item->check_out?->format('H:i') ?? '-' }}</td>
                <td class="wrap-2">{{ config('attendance.location.name', '-') }}</td>
                <td>
                    @php
                        $statusClass = match($item->status) {
                            'present' => 'badge-success',
                            'late' => 'badge-warning',
                            'absent' => 'badge-danger',
                            'leave', 'sick', 'permission' => 'badge-secondary',
                            default => 'badge-secondary'
                        };

                        $statusLabel = match($item->status) {
                            'present' => 'Hadir',
                            'late' => 'Terlambat',
                            'absent' => 'Tidak Hadir',
                            'leave' => 'Cuti',
                            'sick' => 'Sakit',
                            'permission' => 'Izin',
                            default => ucfirst($item->status ?? '-')
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="text-center nowrap">{{ $item->is_late ? ($item->late_minutes . ' menit') : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="empty-state">Tidak ada data presensi untuk periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="summary-box">
    <p class="summary-title">Ringkasan</p>
    <table class="summary-grid">
        <tr>
            <td style="width: 50%;"><strong>Total Presensi:</strong> {{ $attendances->count() }}</td>
            <td><strong>Hadir:</strong> {{ $attendances->where('status', 'present')->count() }}</td>
        </tr>
        <tr>
            <td><strong>Terlambat:</strong> {{ $attendances->where('is_late', true)->count() }}</td>
            <td><strong>Tidak Hadir:</strong> {{ $attendances->where('status', 'absent')->count() }}</td>
        </tr>
    </table>
</div>
@endsection
