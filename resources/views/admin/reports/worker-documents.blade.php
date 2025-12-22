@extends('layouts.app')

@section('title', 'Laporan Dokumen Pegawai')

@section('content')
<div class="container mx-auto px-4">
    <h2 class="text-2xl font-semibold mb-4">Laporan Dokumen Pegawai</h2>

    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}" class="mt-1 block w-full" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $filters['date_to'] ?? now()->endOfMonth()->format('Y-m-d') }}" class="mt-1 block w-full" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 block w-full">
                <option value="">Semua</option>
                <option value="pending" {{ (isset($filters['status']) && $filters['status']=='pending') ? 'selected' : '' }}>Pending</option>
                <option value="verified" {{ (isset($filters['status']) && $filters['status']=='verified') ? 'selected' : '' }}>Terverifikasi</option>
                <option value="rejected" {{ (isset($filters['status']) && $filters['status']=='rejected') ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Dokumen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kadaluarsa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $doc)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($doc->worker)->name ?? $doc->worker_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($doc->documentType)->name ?? $doc->document_type_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doc->file_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($doc->status) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($doc->file_path)
                        <a href="{{ route('admin.worker-documents.download', $doc->id) }}" class="text-blue-600 hover:underline">Download</a>
                        @else
                        -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="px-6 py-4" colspan="7">Tidak ada dokumen yang sesuai filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{-- If the service returns a LengthAwarePaginator, show links --}}
        @if(method_exists($documents, 'links'))
            {{ $documents->links() }}
        @endif
    </div>
</div>
@endsection
