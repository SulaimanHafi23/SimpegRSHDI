@extends('layouts.admin')

@section('title', 'Daftar Dokumen Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold">Daftar Dokumen Pegawai</h1>
        <a href="{{ route('admin.worker-documents.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">Unggah Dokumen</a>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <select name="worker_id" class="w-full rounded border px-3 py-2">
                    <option value="">Semua Pegawai</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ request('worker_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="document_type_id" class="w-full rounded border px-3 py-2">
                    <option value="">Semua Tipe</option>
                    @foreach($documentTypes as $dt)
                        <option value="{{ $dt->id }}" {{ request('document_type_id') == $dt->id ? 'selected' : '' }}>{{ $dt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full rounded border px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ request('status')=='verified' ? 'selected' : '' }}>Verified</option>
                    <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Filter</button>
                <a href="{{ route('admin.worker-documents.index') }}" class="px-4 py-2 bg-gray-200 rounded">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="text-left text-sm text-gray-600">
                        <th class="py-2">Pegawai</th>
                        <th class="py-2">Tipe Dokumen</th>
                        <th class="py-2">File</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Diupload</th>
                        <th class="py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr class="border-t">
                            <td class="py-3">{{ $doc->worker->name ?? '-' }}</td>
                            <td class="py-3">{{ $doc->documentType->name ?? '-' }}</td>
                            <td class="py-3">
                                <a href="{{ route('admin.worker-documents.download', $doc->id) }}" class="text-blue-600">{{ $doc->file_name }}</a>
                            </td>
                            <td class="py-3">
                                @if($doc->status === 'pending')
                                    <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($doc->status === 'verified')
                                    <span class="px-2 py-1 rounded bg-green-100 text-green-800">Verified</span>
                                @else
                                    <span class="px-2 py-1 rounded bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </td>
                            <td class="py-3">{{ $doc->created_at->format('d M Y') }}</td>
                            <td class="py-3">
                                <a href="{{ route('admin.worker-documents.show', $doc->id) }}" class="px-3 py-1 bg-gray-100 rounded">Lihat</a>
                                @if(auth()->user()->can('verify-worker-documents') && $doc->status === 'pending')
                                    <form action="{{ route('admin.worker-documents.verify', $doc->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded">Verifikasi</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-500">Belum ada dokumen</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $documents->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
