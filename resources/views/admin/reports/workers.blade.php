@extends('layouts.admin')

@section('title', 'Laporan Pegawai')

@section('content')
<div class="space-y-6">
    <x-page-header title="Laporan Pegawai" description="Daftar pegawai dan ekspor" icon="fas fa-user" />

    <x-card>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
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

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 text-white rounded text-sm"><i class="fas fa-filter mr-1"></i><span class="hidden sm:inline">Filter</span></button>
                <a href="?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'csv'])) }}" class="px-4 py-2 border rounded text-sm"><i class="fas fa-file-csv mr-1"></i><span class="hidden sm:inline">CSV</span></a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left">
                        <th class="px-3 sm:px-6 py-3 text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-3 sm:px-6 py-3 text-xs font-medium text-gray-500 uppercase hidden md:table-cell">NIP</th>
                        <th class="px-3 sm:px-6 py-3 text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Email</th>
                        <th class="px-3 sm:px-6 py-3 text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Departemen</th>
                        <th class="px-3 sm:px-6 py-3 text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Status Kepegawaian</th>
                        <th class="px-3 sm:px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($workers as $w)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-6 py-4">
                                <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $w->name }}</div>
                                <div class="text-xs text-gray-500 md:hidden">{{ $w->nip }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden md:table-cell">{{ $w->nip }}</td>
                            <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden lg:table-cell">{{ $w->email }}</td>
                            <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden lg:table-cell">{{ $w->department->name ?? '-' }}</td>
                            <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900 hidden lg:table-cell">{{ $w->employment_status ?? '-' }}</td>
                            <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-900">{{ $w->status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(method_exists($workers, 'links'))
            <div class="mt-4">{{ $workers->appends($filters ?? [])->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
