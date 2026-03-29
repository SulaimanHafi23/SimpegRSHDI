@extends('exports.pdf-header', ['title' => 'Laporan Data Pegawai'])

@section('content')
<style>
    @page {
        margin: 25px 35px 35px 35px;
    }
    .page-padding {
        padding: 10px 15px;
    }
</style>

<div class="page-padding">
    <h3>LAPORAN DATA PEGAWAI</h3>

    <div class="info-box">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                    <table style="border: none; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 120px;">Periode Cetak</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ now()->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Status</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ !empty($filters['status']) ? ucfirst($filters['status']) : '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Pencarian</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ !empty($filters['search']) ? $filters['search'] : '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="border: none; padding: 4px 0; width: 50%; vertical-align: top;">
                    <table style="border: none; border-collapse: collapse;">
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280; width: 120px;">Departemen</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; width: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ $departmentName ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #6b7280;">Status Kepegawaian</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px;">:</td>
                            <td style="border: none; padding: 2px 0; font-size: 10px; color: #111827;">{{ !empty($filters['employment_status']) ? ucfirst($filters['employment_status']) : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #a7f3d0;">
            <span style="font-size: 10px; color: #047857; font-weight: bold;">Total Data: {{ $workers->count() }} pegawai</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">No</th>
                <th width="10%">NIP</th>
                <th width="14%">Nama</th>
                <th width="12%">Email</th>
                <th width="8%">Telepon</th>
                <th width="6%">JK</th>
                <th width="8%">Agama</th>
                <th width="8%">Tgl Lahir</th>
                <th width="12%">Departemen</th>
                <th width="9%">Kepegawaian</th>
                <th width="6%">Status</th>
                <th width="8%">Tgl Masuk</th>
                <th width="8%">Tgl Resign</th>
            </tr>
        </thead>
        <tbody>
            @forelse($workers as $index => $worker)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $worker->nip }}</td>
                <td>{{ $worker->name }}</td>
                <td>{{ $worker->email }}</td>
                <td>{{ $worker->phone_number ?? '-' }}</td>
                <td>{{ $worker->gender ?? '-' }}</td>
                <td>{{ $worker->religion ?? '-' }}</td>
                <td>{{ $worker->birth_date ? $worker->birth_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $worker->department->name ?? '-' }}</td>
                <td>{{ ucfirst($worker->employment_status ?? '-') }}</td>
                <td>{{ ucfirst($worker->status ?? '-') }}</td>
                <td>{{ $worker->hire_date ? $worker->hire_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $worker->resign_date ? $worker->resign_date->format('d/m/Y') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center" style="padding: 20px; color: #666;">
                    Tidak ada data pegawai
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
