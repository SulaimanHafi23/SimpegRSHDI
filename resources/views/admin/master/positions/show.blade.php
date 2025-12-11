@extends('layouts.admin')

@section('title', 'Detail Jabatan')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.master.positions.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Jabatan</h1>
                <p class="text-sm text-gray-600 mt-1">Informasi lengkap jabatan</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.master.positions.edit', $position->id) }}" 
               class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <form action="{{ route('admin.master.positions.destroy', $position->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
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
            <div class="h-20 w-20 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-briefcase text-blue-600 text-3xl"></i>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $position->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">ID: {{ $position->id }}</p>
            </div>
        </div>

        <div class="border-t pt-6 grid grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Jumlah Pegawai</p>
                <p class="text-xl sm:text-xl sm:text-2xl font-bold text-blue-600">{{ $position->workers_count ?? 0 }} orang</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Status</p>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    Aktif
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Dibuat Pada</p>
                <p class="text-base text-gray-900">{{ $position->created_at->format('d F Y, H:i') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Terakhir Diubah</p>
                <p class="text-base text-gray-900">{{ $position->updated_at->format('d F Y, H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
