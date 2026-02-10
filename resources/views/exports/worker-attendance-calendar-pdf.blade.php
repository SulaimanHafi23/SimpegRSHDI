@extends('exports.pdf-header', ['title' => 'Riwayat Absensi Pegawai'])

@section('content')
<h3>RIWAYAT ABSENSI PEGAWAI</h3>

<div class="info-box">
    <table style="width: 100%; border: none; border-collapse: collapse;">
        <tr>
            <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                <table style="border: none; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Pegawai</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 11px; font-weight: bold; color: #111827;">{{ $worker->name }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">NIP</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $worker->nip }}</td>
                    </tr>
                </table>
            </td>
            <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                <table style="border: none; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 90px;">Departemen</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $worker->department->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Periode</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                        <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #a7f3d0;">
        <span style="font-size: 10px; color: #047857; font-weight: bold;">Total Hari: {{ count($rows) }} hari</span>
    </div>
</div>

@if(isset($summary))
<div class="summary-box" style="background-color: #f0fdf4; border: 1px solid #bbf7d0;">
    <p style="margin-bottom: 8px; font-size: 11px; font-weight: bold; color: #047857;">Ringkasan</p>
    <table style="width: 100%; border: none; border-collapse: collapse;">
        <tr>
            <td style="border: none; padding: 3px 8px; width: 33%;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; margin-right: 5px;"></span>
                <span style="font-size: 10px;">Hadir: <strong>{{ $summary['present'] }}</strong> hari</span>
            </td>
            <td style="border: none; padding: 3px 8px; width: 33%;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #f59e0b; border-radius: 50%; margin-right: 5px;"></span>
                <span style="font-size: 10px;">Terlambat: <strong>{{ $summary['late'] }}</strong> hari</span>
            </td>
            <td style="border: none; padding: 3px 8px; width: 33%;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #ef4444; border-radius: 50%; margin-right: 5px;"></span>
                <span style="font-size: 10px;">Tidak Hadir: <strong>{{ $summary['absent'] }}</strong> hari</span>
            </td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 8px;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #3b82f6; border-radius: 50%; margin-right: 5px;"></span>
                <span style="font-size: 10px;">Cuti: <strong>{{ $summary['leave'] }}</strong> hari</span>
            </td>
            <td style="border: none; padding: 3px 8px;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #8b5cf6; border-radius: 50%; margin-right: 5px;"></span>
                <span style="font-size: 10px;">Sakit: <strong>{{ $summary['sick'] }}</strong> hari</span>
            </td>
            <td style="border: none; padding: 3px 8px;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #6366f1; border-radius: 50%; margin-right: 5px;"></span>
                <span style="font-size: 10px;">Izin: <strong>{{ $summary['permission'] }}</strong> hari</span>
            </td>
        </tr>
    </table>
</div>
@endif

<table>
    <thead>
        <tr>
            <th class="text-center" width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="12%">Hari</th>
            <th width="12%">Shift</th>
            <th width="14%">Jadwal Shift</th>
            <th width="10%">Check In</th>
            <th width="10%">Check Out</th>
            <th width="10%">Status</th>
            <th width="8%">Terlambat</th>
            <th width="17%">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $index => $row)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['day_name'] }}</td>
            <td>{{ $row['shift_name'] }}</td>
            <td>{{ $row['shift_time'] }}</td>
            <td>{{ $row['check_in'] }}</td>
            <td>{{ $row['check_out'] }}</td>
            <td>{{ $row['status'] }}</td>
            <td class="text-center">{{ $row['late'] }}</td>
            <td>{{ $row['notes'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center" style="padding: 20px; color: #666;">
                Tidak ada data absensi
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection
