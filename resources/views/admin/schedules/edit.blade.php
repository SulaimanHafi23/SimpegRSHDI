@extends('layouts.admin')

@section('title', 'Edit Jadwal Shift')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.worker-shifts.index') }}" class="hover:text-green-600">Jadwal Shift</a>
            <span>/</span>
            <span class="text-gray-800">Edit Jadwal</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Edit Jadwal Shift</h1>
        <p class="text-gray-600 mt-1">Ubah data jadwal shift pegawai</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="font-bold">Terdapat kesalahan pada form!</strong>
                    <ul class="mt-2 ml-4 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.worker-shifts.update', $workerShift->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Pegawai -->
            <div class="mb-6">
                <label for="worker_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Pegawai <span class="text-red-500">*</span>
                </label>
                <select name="worker_id" id="worker_id" 
                        class="w-full px-4 py-2 border @error('worker_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($workers as $worker)
                        <option value="{{ $worker->id }}" {{ (old('worker_id', $workerShift->worker_id) == $worker->id) ? 'selected' : '' }}>
                            {{ $worker->nip }} - {{ $worker->name }}
                        </option>
                    @endforeach
                </select>
                @error('worker_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Shift -->
            <div class="mb-6">
                <label for="shift_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Shift <span class="text-red-500">*</span>
                </label>
                <select name="shift_id" id="shift_id" 
                        class="w-full px-4 py-2 border @error('shift_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ (old('shift_id', $workerShift->shift_id) == $shift->id) ? 'selected' : '' }}>
                            {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                        </option>
                    @endforeach
                </select>
                @error('shift_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pattern Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pola</label>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="pattern_type" value="fixed" class="pattern-type" {{ old('pattern_type', $workerShift->pattern_type) == 'fixed' ? 'checked' : '' }}>
                        <span class="ml-2">Fixed (selalu pakai shift ini)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="pattern_type" value="rotating" class="pattern-type" {{ old('pattern_type', $workerShift->pattern_type) == 'rotating' ? 'checked' : '' }}>
                        <span class="ml-2">Rotating (atur per hari: Sen–Ming)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="pattern_type" value="custom" class="pattern-type" {{ old('pattern_type', $workerShift->pattern_type) == 'custom' ? 'checked' : '' }}>
                        <span class="ml-2">Custom (pilih hari kerja)</span>
                    </label>
                </div>
            </div>

            <!-- Rotating mapping -->
            <div id="rotating-section" class="mb-6" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rotating Days (mapping hari → shift)</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @php $dayNames = ['1'=>'Senin','2'=>'Selasa','3'=>'Rabu','4'=>'Kamis','5'=>'Jumat','6'=>'Sabtu','7'=>'Minggu']; @endphp
                    @foreach($dayNames as $num => $label)
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">{{ $label }}</label>
                            <select name="rotating_days[{{ $num }}]" class="w-full px-2 py-2 border border-gray-300 rounded">
                                <option value="">-- Tidak ada --</option>
                                @foreach($shifts as $shiftOption)
                                    @php
                                        $current = old('rotating_days.'.$num) ?? ($workerShift->rotating_days[$num] ?? null);
                                    @endphp
                                    <option value="{{ $shiftOption->id }}" {{ ($current == $shiftOption->id) ? 'selected' : '' }}>
                                        {{ $shiftOption->name }} ({{ $shiftOption->start_time }} - {{ $shiftOption->end_time }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Custom working days -->
            <div id="custom-section" class="mb-6" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari Kerja (Custom)</label>
                <div class="flex flex-wrap gap-3">
                    @php $dayNames = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu']; @endphp
                    @foreach($dayNames as $num => $label)
                        @php $checked = in_array($num, old('custom_working_days', $workerShift->custom_working_days ?? [])) ? true : false; @endphp
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="custom_working_days[]" value="{{ $num }}" {{ $checked ? 'checked' : '' }}>
                            <span class="ml-2">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-gray-500 text-xs mt-2">Kosongkan Minggu (7) jika tidak masuk kerja hari Minggu.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tanggal Mulai -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" 
                           value="{{ old('start_date', $workerShift->start_date) }}"
                           class="w-full px-4 py-2 border @error('start_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Selesai <span class="text-gray-500">(Opsional)</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" 
                           value="{{ old('end_date', $workerShift->end_date) }}"
                           class="w-full px-4 py-2 border @error('end_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Kosongkan jika jadwal berlaku tanpa batas waktu</p>
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="mb-6 mt-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $workerShift->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Status Aktif</span>
                </label>
                <p class="text-gray-500 text-xs mt-1 ml-6">Jadwal yang tidak aktif tidak akan ditampilkan dalam sistem absensi</p>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t">
                <button type="submit" 
                        class="inline-flex justify-center items-center px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Jadwal
                </button>
                <a href="{{ route('admin.worker-shifts.index') }}" 
                   class="inline-flex justify-center items-center px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Update end date minimum when start date changes
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        endDateInput.setAttribute('min', this.value);
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = '';
        }
    });
    
    // Set initial minimum on load
    if (startDateInput.value) {
        endDateInput.setAttribute('min', startDateInput.value);
    }

    // Show/hide rotating/custom sections based on pattern type
    function togglePatternSections() {
        const selected = document.querySelector('input[name="pattern_type"]:checked')?.value || '{{ $workerShift->pattern_type }}';
        const rot = document.getElementById('rotating-section');
        const cust = document.getElementById('custom-section');
        if (rot) rot.style.display = (selected === 'rotating') ? 'block' : 'none';
        if (cust) cust.style.display = (selected === 'custom') ? 'block' : 'none';
    }

    document.querySelectorAll('.pattern-type').forEach(function(el){
        el.addEventListener('change', togglePatternSections);
    });

    // Initialize visibility
    togglePatternSections();
});
</script>
@endsection
