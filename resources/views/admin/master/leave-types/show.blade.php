@extends('layouts.admin')

@section('title', 'Detail Tipe Cuti')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.leave-types.index') }}" class="hover:text-green-600">Tipe Cuti</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Detail</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-info-circle text-green-600 mr-2"></i>
                Detail Tipe Cuti
            </h1>
            <div class="flex space-x-2">
                @can('leave-type.edit')
                <a href="{{ route('admin.master.leave-types.edit', $leaveType->id) }}" 
                   class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                @endcan
                @can('leave-type.delete')
                <form action="{{ route('admin.master.leave-types.destroy', $leaveType->id) }}" 
                      method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe cuti ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white">{{ $leaveType->name }}</h2>
            @if($leaveType->code)
                <p class="text-green-100 text-sm">{{ $leaveType->code }}</p>
            @endif
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Maksimal Hari</label>
                    <p class="text-lg font-semibold text-gray-800">
                        @if($leaveType->max_days)
                            {{ $leaveType->max_days }} hari
                        @else
                            <span class="text-blue-600">Tidak terbatas</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status Persetujuan</label>
                    <p class="text-lg">
                        @if($leaveType->requires_approval)
                            <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Memerlukan Persetujuan
                            </span>
                        @else
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-bolt mr-1"></i>Otomatis Disetujui
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status Aktif</label>
                    <p class="text-lg">
                        @if($leaveType->is_active)
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                <i class="fas fa-times-circle mr-1"></i>Tidak Aktif
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat</label>
                    <p class="text-gray-800">
                        <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                        {{ $leaveType->created_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>

            @if($leaveType->description)
            <div class="border-t pt-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Deskripsi</label>
                <p class="text-gray-700 leading-relaxed">{{ $leaveType->description }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
