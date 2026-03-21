@extends('layouts.admin')

@section('title', 'Detail Tipe Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Tipe Dokumen</h1>
            <p class="text-sm text-gray-600 mt-1">Informasi lengkap tipe dokumen</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.master.document-types.edit', $documentType->id) }}" 
               class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <form action="{{ route('admin.master.document-types.destroy', $documentType->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    <i class="fas fa-trash mr-2"></i>Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex items-center space-x-4 mb-6">
            <div class="h-20 w-20 bg-indigo-100 rounded-full flex items-center justify-center">
                <i class="fas fa-file-alt text-indigo-600 text-3xl"></i>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $documentType->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">ID: {{ $documentType->id }}</p>
            </div>
        </div>

        <div class="border-t pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Status</p>
                @if(!empty($documentType->is_active))
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Aktif</span>
                @else
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Tidak Aktif</span>
                @endif
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Kategori</p>
                <p class="text-base text-gray-900">{{ $documentType->employment_category_label ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Proses</p>
                <p class="text-base text-gray-900">{{ $documentType->process_type_label ?? '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Format File</p>
                @if(!empty($documentType->file_format))
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $documentType->file_format) as $fmt)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm bg-blue-50 text-blue-700">{{ trim($fmt) }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-base text-gray-900">-</p>
                @endif
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Ukuran Maks (KB)</p>
                <p class="text-base text-gray-900">{{ $documentType->max_file_size ? $documentType->max_file_size . ' KB' : '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Buffer Kadaluarsa</p>
                <p class="text-base text-gray-900">{{ (int) ($documentType->expiration_buffer_days ?? 0) }} hari</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Sifat Aturan</p>
                <p class="text-base text-gray-900">{{ $documentType->is_required ? 'Wajib' : 'Opsional' }}</p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm font-medium text-gray-500 mb-1">Catatan Aturan</p>
                <p class="text-base text-gray-900">{{ $documentType->requirement_notes ?: '-' }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Dibuat Pada</p>
                <p class="text-base text-gray-900">{{ $documentType->created_at->format('d F Y, H:i') }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Terakhir Diubah</p>
                <p class="text-base text-gray-900">{{ $documentType->updated_at->format('d F Y, H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
