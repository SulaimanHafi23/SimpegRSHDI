@extends('layouts.admin')

@section('title', 'Edit Jadwal Shift')

@section('content')
<div class="space-y-6">
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

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="generate_rotation" id="generate_rotation" value="1"
                           {{ old('generate_rotation') ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Generate Rotasi dari Edit</span>
                </label>
                <p class="text-gray-500 text-xs mt-1 ml-6">Aktifkan jika ingin mengganti jadwal ini menjadi rotasi mingguan/bulanan</p>
            </div>

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

            <!-- Shift (Mode Tetap) -->
            <div class="mb-6" id="single-shift-section">
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

            <!-- Rotation (Mode Rotasi) -->
            <div id="rotation-section" class="hidden mb-6 border border-indigo-200 rounded-lg p-4 bg-indigo-50/40">
                <h3 class="text-sm font-semibold text-indigo-900 mb-4">Pengaturan Rotasi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Rotasi</label>
                        <select name="rotation_type" id="rotation_type"
                                class="w-full px-4 py-2 border @error('rotation_type') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">-- Pilih --</option>
                            <option value="weekly" {{ old('rotation_type') == 'weekly' ? 'selected' : '' }}>Per Minggu</option>
                            <option value="monthly" {{ old('rotation_type') == 'monthly' ? 'selected' : '' }}>Per Bulan</option>
                        </select>
                        @error('rotation_type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nonaktifkan Jadwal Lama</label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="deactivate_existing" value="1" {{ old('deactivate_existing', true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Nonaktifkan sebelum generate</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutan Shift</label>
                    <div id="sequence-list" class="space-y-2">
                        @php $sequence = old('shift_sequence', ['']); @endphp
                        @foreach($sequence as $value)
                            <div class="sequence-row flex items-center gap-2">
                                <select name="shift_sequence[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">-- Pilih Shift --</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ $value == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="remove-sequence px-3 py-2 text-red-600 hover:text-red-700" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-sequence" class="mt-2 text-sm text-indigo-600 hover:text-indigo-700">+ Tambah Urutan</button>
                    @error('shift_sequence')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Rotasi (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('notes') }}</textarea>
                </div>
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

<template id="sequence-template">
    <div class="sequence-row flex items-center gap-2">
        <select name="shift_sequence[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="">-- Pilih Shift --</option>
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</option>
            @endforeach
        </select>
        <button type="button" class="remove-sequence px-3 py-2 text-red-600 hover:text-red-700" title="Hapus">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>

<script>
// Update end date minimum when start date changes
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const generateRotationCheckbox = document.getElementById('generate_rotation');
    const singleShiftSection = document.getElementById('single-shift-section');
    const rotationSection = document.getElementById('rotation-section');
    const shiftSelect = document.getElementById('shift_id');
    const rotationTypeSelect = document.getElementById('rotation_type');
    const sequenceList = document.getElementById('sequence-list');
    const addSequenceBtn = document.getElementById('add-sequence');
    const sequenceTemplate = document.getElementById('sequence-template');

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

    function syncRemoveButtons() {
        if (!sequenceList) return;
        const rows = sequenceList.querySelectorAll('.sequence-row');
        rows.forEach(function(row) {
            const removeBtn = row.querySelector('.remove-sequence');
            if (removeBtn) {
                removeBtn.disabled = rows.length <= 1;
            }
        });
    }

    addSequenceBtn?.addEventListener('click', function() {
        const clone = document.importNode(sequenceTemplate.content, true);
        sequenceList.appendChild(clone);
        syncRemoveButtons();
    });

    sequenceList?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-sequence')) {
            const row = e.target.closest('.sequence-row');
            if (sequenceList.querySelectorAll('.sequence-row').length > 1) {
                row?.remove();
            }
            syncRemoveButtons();
        }
    });

    function toggleMode() {
        const rotationOn = generateRotationCheckbox?.checked;

        if (rotationOn) {
            singleShiftSection?.classList.add('hidden');
            rotationSection?.classList.remove('hidden');

            if (shiftSelect) {
                shiftSelect.required = false;
                shiftSelect.disabled = true;
            }

            if (rotationTypeSelect) {
                rotationTypeSelect.required = true;
            }

            const sequenceSelects = sequenceList?.querySelectorAll('select[name="shift_sequence[]"]') || [];
            sequenceSelects.forEach(select => select.required = true);
        } else {
            singleShiftSection?.classList.remove('hidden');
            rotationSection?.classList.add('hidden');

            if (shiftSelect) {
                shiftSelect.disabled = false;
                shiftSelect.required = true;
            }

            if (rotationTypeSelect) {
                rotationTypeSelect.required = false;
            }

            const sequenceSelects = sequenceList?.querySelectorAll('select[name="shift_sequence[]"]') || [];
            sequenceSelects.forEach(select => select.required = false);
        }
    }

    generateRotationCheckbox?.addEventListener('change', toggleMode);
    syncRemoveButtons();
    toggleMode();

});
</script>
@endsection
