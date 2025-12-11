@extends('layouts.admin')

@section('title', 'Detail Agama')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.master.religions.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Agama</h1>
                <p class="text-sm text-gray-600 mt-1">Informasi lengkap data agama</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.master.religions.edit', $religion->id) }}" 
               class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition duration-200">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
            <form action="{{ route('admin.master.religions.destroy', $religion->id) }}" 
                  method="POST" 
                  class="inline-block"
                  onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200">
                    <i class="fas fa-trash mr-2"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="h-20 w-20 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-mosque text-green-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $religion->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">ID: {{ $religion->id }}</p>
                </div>
            </div>

            <div class="border-t pt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Dibuat Pada</p>
                    <p class="text-base text-gray-900">{{ $religion->created_at->format('d F Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Terakhir Diubah</p>
                    <p class="text-base text-gray-900">{{ $religion->updated_at->format('d F Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
