@extends('layouts.admin')

@section('title', 'Laporan Pegawai')

@section('content')
<div class="space-y-6">
    <x-page-header title="Laporan Pegawai" description="Daftar pegawai dan ekspor" icon="fas fa-user" />

    <x-card>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Departemen</label>
                <select name="department_id" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">Semua</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ ($filters['department_id'] ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">Semua</option>
                    <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Pencarian</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau NIP" class="mt-1 block w-full border rounded px-3 py-2">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Filter</button>
                <a href="?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'csv'])) }}" class="px-4 py-2 border rounded">Export CSV</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="text-left">
                        <th class="px-3 py-2">Nama</th>
                        <th class="px-3 py-2">NIP</th>
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Departemen</th>
                        <th class="px-3 py-2">Status Kepegawaian</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workers as $w)
                        <tr>
                            <td class="px-3 py-2">{{ $w->name }}</td>
                            <td class="px-3 py-2">{{ $w->nip }}</td>
                            <td class="px-3 py-2">{{ $w->email }}</td>
                            <td class="px-3 py-2">{{ $w->department->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $w->employment_status ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $w->status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(method_exists($workers, 'links'))
            <div class="mt-4">{{ $workers->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
