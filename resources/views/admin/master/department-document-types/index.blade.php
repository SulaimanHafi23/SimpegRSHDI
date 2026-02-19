@extends('layouts.admin')

@section('title', 'Relasi Departemen - Tipe Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Document Department / Position</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Kelola relasi dokumen per departemen</p>
        </div>
        <a href="{{ route('admin.master.department-document-types.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
            <i class="fas fa-plus mr-2"></i>Tambah Relasi
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @if(isset($universalDocumentTypes) && $universalDocumentTypes->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-4 sm:p-6 border-2 border-green-300">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold flex items-center gap-3">
                            <span>Universal (Semua Departemen)</span>
                            <span class="inline-block px-2 py-0.5 text-xs bg-green-50 text-green-700 rounded">{{ $universalDocumentTypes->count() }} tipe</span>
                        </h2>
                        <p class="text-sm text-gray-600">Dokumen yang dapat diunggah oleh semua pegawai.</p>
                    </div>
                    <div class="space-x-2">
                        <a href="{{ route('admin.master.department-document-types.create', ['department_id' => 'universal']) }}" class="px-3 py-1 bg-green-600 text-white rounded">Tambah</a>
                        <a href="{{ route('admin.master.department-document-types.edit', 'universal') }}" class="px-3 py-1 bg-gray-200 text-gray-700 rounded">Edit</a>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Tipe Dokumen Universal:</h3>
                    <ul class="text-sm text-gray-700 divide-y">
                        @foreach($universalDocumentTypes as $dt)
                            <li class="py-2">
                                <div class="font-medium">{{ $dt->name }}</div>
                                @if($dt->description)
                                    <div class="text-xs text-gray-500">{{ $dt->description }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @forelse($departments as $department)
            <div class="bg-white rounded-lg shadow p-4 sm:p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold flex items-center gap-3">
                            <span>{{ $department->name }}</span>
                            <span class="inline-block px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">{{ $department->documentTypes->count() }} tipe</span>
                        </h2>
                        <p class="text-sm text-gray-600 truncate">{{ $department->description ?? '' }}</p>
                    </div>
                    <div class="space-x-2">
                        <a href="{{ route('admin.master.department-document-types.edit', $department->id) }}" class="px-3 py-1 bg-green-600 text-white rounded">Edit</a>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Tipe Dokumen yang Diperlukan:</h3>
                    @if($department->documentTypes && $department->documentTypes->isNotEmpty())
                        <ul class="text-sm text-gray-700 divide-y">
                            @foreach($department->documentTypes as $dt)
                                <li class="py-2 flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">{{ $dt->name }}</div>
                                        @if($dt->description)
                                            <div class="text-xs text-gray-500">{{ $dt->description }}</div>
                                        @endif
                                    </div>

                                    <form action="{{ route('admin.master.department-document-types.destroy', $dt->pivot->id) }}" method="POST" onsubmit="return confirm('Hapus relasi dokumen ini?');" class="inline-block ml-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">Tidak ada tipe dokumen yang ditetapkan.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-gray-500">Belum ada relasi.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>
</div>
@endsection
