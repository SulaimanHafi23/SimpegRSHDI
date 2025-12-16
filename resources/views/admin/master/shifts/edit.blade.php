@extends('layouts.admin')

@section('title', 'Edit Shift')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.shifts.index') }}" class="hover:text-green-600">Shift</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-edit text-green-600 mr-2"></i>
            Edit Shift
        </h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.master.shifts.update', $shift->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Shift -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Shift <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $shift->name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jam Masuk -->
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Masuk <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="start_time" 
                           id="start_time" 
                           value="{{ old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('start_time') border-red-500 @enderror"
                           required>
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jam Keluar -->
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Keluar <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="end_time" 
                           id="end_time" 
                           value="{{ old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('end_time') border-red-500 @enderror"
                           required>
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $shift->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $shift->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">Shift Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                <a href="{{ route('admin.master.shifts.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
