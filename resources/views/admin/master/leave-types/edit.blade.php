@extends('layouts.admin')

@section('title', 'Edit Tipe Cuti')

@section('content')
<div class="space-y-6">
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.leave-types.index') }}" class="hover:text-green-600">Tipe Cuti</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-edit text-green-600 mr-2"></i>
            Edit Tipe Cuti
        </h1>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                <div class="flex-1">
                    <p class="font-medium text-red-800">Terdapat kesalahan:</p>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @elseif(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.master.leave-types.update', $leaveType->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Tipe Cuti <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $leaveType->name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                           placeholder="Contoh: Cuti Tahunan" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $leaveType->code) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                           placeholder="CT-001">
                </div>

                <div>
                    <label for="max_days_per_year" class="block text-sm font-medium text-gray-700 mb-2">
                        Maksimal Hari <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="max_days_per_year" 
                           id="max_days_per_year" 
                           value="{{ old('max_days_per_year', $leaveType->max_days_per_year) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('max_days_per_year') border-red-500 @enderror"
                           placeholder="12" 
                           min="1"
                           required>
                    @error('max_days_per_year')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="days_notice" class="block text-sm font-medium text-gray-700 mb-2">
                        Hari Notice <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="days_notice" 
                           id="days_notice" 
                           value="{{ old('days_notice', $leaveType->days_notice ?? 0) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 @error('days_notice') border-red-500 @enderror"
                           placeholder="0" 
                           min="0"
                           required>
                    @error('days_notice')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i> Berapa hari sebelumnya cuti harus diajukan
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('description', $leaveType->description) }}</textarea>
                </div>

                <div class="md:col-span-2 space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="requires_approval" value="1" 
                               {{ old('requires_approval', $leaveType->requires_approval) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Memerlukan Persetujuan</span>
                    </label>
                    
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="requires_attachment" value="1" 
                               {{ old('requires_attachment', $leaveType->requires_attachment) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Memerlukan Lampiran</span>
                    </label>
                    
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="is_active" value="1" 
                               {{ old('is_active', $leaveType->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                <a href="{{ route('admin.master.leave-types.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
