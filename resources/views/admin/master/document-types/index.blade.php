@extends('layouts.admin')

@section('title', 'Data Tipe Dokumen')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Tipe Dokumen</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola tipe dokumen pegawai</p>
        </div>
        <a href="{{ route('admin.master.document-types.create') }}" 
           class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
            <i class="fas fa-plus mr-2"></i>Tambah Tipe Dokumen
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ $keyword ?? '' }}" placeholder="Cari tipe dokumen..." 
                   class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
            @if($keyword ?? false)
            <a href="{{ route('admin.master.document-types.index') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
                Reset
            </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Dokumen</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documentTypes as $index => $docType)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">{{ $documentTypes->firstItem() + $index }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-file-alt text-indigo-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $docType->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                            Aktif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('admin.master.document-types.show', $docType->id) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.master.document-types.edit', $docType->id) }}" class="text-yellow-600 hover:text-yellow-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.master.document-types.destroy', $docType->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500">Tidak ada data</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($documentTypes->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $documentTypes->links() }}</div>
        @endif
    </div>
</div>
@endsection
