@extends('layouts.admin')

@section('title', 'Edit Lembur')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-600 mb-4">
                <a href="{{ route('admin.overtime.index') }}" class="hover:text-green-600">Manajemen Lembur</a>
                <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('admin.overtime.show', $overtime->id) }}" class="hover:text-green-600">Detail</a>
                <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-800">Edit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">Edit Data Lembur</h1>
            <p class="text-gray-600 mt-1">Perbarui informasi data lembur</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.overtime.update', $overtime->id) }}" method="POST" enctype="multipart/form-data" x-data="overtimeForm()">
                @csrf
                @method('PUT')

                <div class="space-y-4 sm:space-y-6">
                    <!-- Pegawai -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Pegawai <span class="text-red-500">*</span>
                        </label>
                        <select name="worker_id" 
                                required
                                class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('worker_id') border-red-500 @enderror">
                            <option value="">Pilih Pegawai</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ (old('worker_id', $overtime->worker_id) == $worker->id) ? 'selected' : '' }}>
                                    {{ $worker->name }} - {{ $worker->position->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('worker_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Lembur -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Tanggal Lembur <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="overtime_date" 
                               required
                               class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('overtime_date') border-red-500 @enderror"
                               value="{{ old('overtime_date', $overtime->overtime_date->format('Y-m-d')) }}">
                        @error('overtime_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                Waktu Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="time" 
                                   name="start_time" 
                                   x-model="startTime"
                                   @change="calculateHours"
                                   required
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('start_time') border-red-500 @enderror"
                                   value="{{ old('start_time', $overtime->start_time) }}">
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                Waktu Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="time" 
                                   name="end_time" 
                                   x-model="endTime"
                                   @change="calculateHours"
                                   required
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('end_time') border-red-500 @enderror"
                                   value="{{ old('end_time', $overtime->end_time) }}">
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Total Jam -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Total Jam Lembur
                        </label>
                        <input type="number" 
                               name="total_hours" 
                               x-model="totalHours"
                               step="0.5"
                               readonly
                               class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md bg-gray-100"
                               value="{{ old('total_hours', $overtime->total_hours) }}">
                        <p class="mt-1 text-sm text-gray-500">Akan dihitung otomatis berdasarkan waktu mulai dan selesai</p>
                    </div>

                    <!-- Alasan -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Keterangan/Alasan Lembur <span class="text-red-500">*</span>
                        </label>
                        <textarea name="reason" 
                                  rows="4" 
                                  required
                                  class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('reason') border-red-500 @enderror"
                                  placeholder="Jelaskan alasan/kegiatan lembur...">{{ old('reason', $overtime->reason) }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lampiran -->
                    <div x-data="{ fileName: '' }">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Lampiran (Opsional)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-green-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="attachment" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500">
                                        <span>Upload file</span>
                                        <input id="attachment" 
                                               name="attachment" 
                                               type="file" 
                                               class="sr-only"
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               @change="fileName = $event.target.files[0]?.name || ''">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, JPG, PNG hingga 2MB</p>
                                <p x-show="fileName" x-text="fileName" class="text-sm text-green-600 font-medium"></p>
                            </div>
                        </div>
                        @error('attachment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.overtime.show', $overtime->id) }}" 
                       class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function overtimeForm() {
    return {
        startTime: '{{ old('start_time', $overtime->start_time) }}',
        endTime: '{{ old('end_time', $overtime->end_time) }}',
        totalHours: {{ old('total_hours', $overtime->total_hours) }},
        
        calculateHours() {
            if (this.startTime && this.endTime) {
                const start = new Date('2000-01-01 ' + this.startTime);
                const end = new Date('2000-01-01 ' + this.endTime);
                
                let diffMs = end - start;
                
                // If end time is before start time, assume it crosses midnight
                if (diffMs < 0) {
                    diffMs += 24 * 60 * 60 * 1000;
                }
                
                const diffHours = diffMs / (1000 * 60 * 60);
                this.totalHours = Math.round(diffHours * 2) / 2; // Round to nearest 0.5
            }
        }
    }
}
</script>
@endsection
