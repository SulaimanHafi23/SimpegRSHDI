@extends('exports.pdf-header', ['title' => 'Laporan Data Pegawai'])

@section('content')
<h3>LAPORAN DATA PEGAWAI</h3>

<div class="info-box">
    <p><strong>Periode Cetak:</strong> {{ now()->translatedFormat('d F Y H:i') }}</p>
    @if(!empty($filters['status']))
    <p><strong>Status:</strong> {{ ucfirst($filters['status']) }}</p>
    @endif
    @if(!empty($filters['employment_status']))
    <p><strong>Status Kepegawaian:</strong> {{ ucfirst($filters['employment_status']) }}</p>
    @endif
    @if(!empty($filters['department_id']))
    <p><strong>Departemen:</strong> {{ $departmentName ?? '-' }}</p>
    @endif
    @if(!empty($filters['search']))
    <p><strong>Pencarian:</strong> {{ $filters['search'] }}</p>
    @endif
    <p><strong>Total Data:</strong> {{ $workers->count() }} pegawai</p>
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
            <td>{{ $worker->gender->name ?? '-' }}</td>
            <td>{{ $worker->religion->name ?? '-' }}</td>
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
@endsection
