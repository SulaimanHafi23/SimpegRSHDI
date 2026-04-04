@extends('layouts.admin')

@section('title', 'Generate Rotasi Shift')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.worker-shifts.index') }}" class="hover:text-green-600">Jadwal Shift</a>
            <span>/</span>
            <span class="text-gray-800">Generate Rotasi</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Generate Rotasi Shift</h1>
        <p class="text-gray-600 mt-1">Buat jadwal shift bergilir otomatis per minggu atau per bulan</p>
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

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.worker-shifts.generate.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Pegawai -->
            <div class="mb-6">
                <label for="worker_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Pegawai <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select id="worker_select" class="flex-1 px-4 py-2 border @if($errors->has('worker_id') || $errors->has('worker_ids')) border-red-500 @else border-gray-300 @endif rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}"
                                    data-label="{{ $worker->nip }} - {{ $worker->name }}">
                                {{ $worker->nip }} - {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="add_worker_btn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Tambah</button>
                </div>
                <div id="selected-workers" class="mt-3 flex flex-wrap gap-2">
                    @php $oldWorkers = old('worker_ids', []); @endphp
                    @if(is_array($oldWorkers) && count($oldWorkers))
                        @foreach($oldWorkers as $wId)
                            @php $w = collect($workers)->firstWhere('id', $wId); @endphp
                            @if($w)
                                <span class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full" data-id="{{ $w->id }}">
                                    {{ $w->nip }} - {{ $w->name }}
                                    <button type="button" class="ml-2 text-green-700 remove-worker">&times;</button>
                                    <input type="hidden" name="worker_ids[]" value="{{ $w->id }}">
                                </span>
                            @endif
                        @endforeach
                    @endif
                </div>
                @error('worker_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @error('worker_ids')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rotasi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Rotasi</label>
                    <select name="rotation_type" class="w-full px-4 py-2 border @error('rotation_type') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
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
                        <input type="checkbox" name="deactivate_existing" value="1" {{ old('deactivate_existing', true) ? 'checked' : '' }} class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Nonaktifkan sebelum generate</span>
                    </label>
                </div>
            </div>

            <!-- Urutan Shift -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Urutan Shift</label>
                <div id="sequence-list" class="space-y-2">
                    @php $sequence = old('shift_sequence', ['']); @endphp
                    @foreach($sequence as $value)
                        <div class="sequence-row flex items-center gap-2">
                            <select name="shift_sequence[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
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

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date"
                           value="{{ old('start_date') }}"
                           class="w-full px-4 py-2 border @error('start_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Selesai <span class="text-gray-500">(Opsional)</span>
                    </label>
                    <input type="date" name="end_date" id="end_date"
                           value="{{ old('end_date') }}"
                           class="w-full px-4 py-2 border @error('end_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Jika kosong, sistem akan membuat rotasi untuk 12 periode ke depan.</p>
                </div>
            </div>

            <!-- Estimasi Generate -->
            <div id="estimation-box" class="mb-6 bg-gradient-to-r from-purple-50 to-indigo-50 border border-indigo-200 rounded-lg p-4 hidden">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-indigo-900 mb-2">Estimasi Generate Rotasi</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div class="bg-white rounded-md p-2 border border-indigo-100">
                                <div class="text-gray-600 text-xs">Pegawai Terpilih</div>
                                <div class="font-bold text-indigo-700 text-lg" id="est-workers">0</div>
                            </div>
                            <div class="bg-white rounded-md p-2 border border-indigo-100">
                                <div class="text-gray-600 text-xs">Jumlah Periode</div>
                                <div class="font-bold text-indigo-700 text-lg" id="est-periods">0</div>
                            </div>
                            <div class="bg-white rounded-md p-2 border border-indigo-100">
                                <div class="text-gray-600 text-xs">Total Jadwal Dibuat</div>
                                <div class="font-bold text-green-700 text-lg" id="est-total">0 <span class="text-xs text-gray-500">data</span></div>
                            </div>
                            <div class="bg-white rounded-md p-2 border border-indigo-100">
                                <div class="text-gray-600 text-xs">Rotasi Berlaku Sampai</div>
                                <div class="font-bold text-red-600 text-base" id="est-end-date">-</div>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-indigo-700 bg-indigo-100 rounded px-2 py-1" id="est-description">
                            Menunggu input data...
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('notes') }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t">
                <button type="submit"
                        class="inline-flex justify-center items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Generate Rotasi
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

    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Catatan:</strong>
                </p>
                <ul class="list-disc list-inside text-sm text-blue-700 mt-2 space-y-1">
                    <li>Rotasi dibuat sebagai jadwal per periode (worker_shifts) sesuai urutan shift.</li>
                    <li>Jam kerja per hari tetap mengikuti pengaturan shift.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<template id="sequence-template">
    <div class="sequence-row flex items-center gap-2">
        <select name="shift_sequence[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
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
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add_worker_btn');
    const workerSelect = document.getElementById('worker_select');
    const selectedContainer = document.getElementById('selected-workers');

    function createChip(id, label) {
        const span = document.createElement('span');
        span.className = 'inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full';
        span.setAttribute('data-id', id);
        span.innerHTML = `${label} <button type="button" class="ml-2 text-green-700 remove-worker">&times;</button>`;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'worker_ids[]';
        input.value = id;
        span.appendChild(input);
        return span;
    }

    function addSelectedWorker() {
        const id = workerSelect.value;
        if (!id) return;
        if (selectedContainer.querySelector(`[data-id="${id}"]`)) {
            workerSelect.value = '';
            return;
        }
        const option = workerSelect.options[workerSelect.selectedIndex];
        const label = option.getAttribute('data-label') || option.text;
        const chip = createChip(id, label);
        selectedContainer.appendChild(chip);
        workerSelect.value = '';
        updateEstimation();
    }

    addBtn?.addEventListener('click', addSelectedWorker);

    selectedContainer?.addEventListener('click', function(e){
        if (e.target.classList.contains('remove-worker')) {
            const chip = e.target.closest('span[data-id]');
            chip?.remove();
            updateEstimation();
        }
    });

    const sequenceList = document.getElementById('sequence-list');
    const sequenceTemplate = document.getElementById('sequence-template');
    const addSequenceBtn = document.getElementById('add-sequence');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const rotationTypeSelect = document.querySelector('select[name="rotation_type"]');

    // Estimation elements
    const estimationBox = document.getElementById('estimation-box');
    const estWorkers = document.getElementById('est-workers');
    const estPeriods = document.getElementById('est-periods');
    const estTotal = document.getElementById('est-total');
    const estEndDate = document.getElementById('est-end-date');
    const estDescription = document.getElementById('est-description');

    function calculatePeriods(startDate, endDate, rotationType) {
        if (!startDate || !rotationType) return 0;

        const start = new Date(startDate);
        const end = endDate ? new Date(endDate) : null;

        if (!end) {
            // Jika tidak ada end date, gunakan default 12 periode
            return 12;
        }

        if (start > end) return 0;

        let periods = 0;
        let current = new Date(start);

        if (rotationType === 'weekly') {
            // Hitung berapa minggu
            while (current <= end) {
                periods++;
                current.setDate(current.getDate() + 7);
            }
        } else if (rotationType === 'monthly') {
            // Hitung berapa bulan
            while (current <= end) {
                periods++;
                // Pindah ke bulan berikutnya
                const nextMonth = new Date(current);
                nextMonth.setMonth(nextMonth.getMonth() + 1);
                nextMonth.setDate(1);
                current = nextMonth;
            }
        }

        return periods;
    }

    function calculateEndDate(startDate, periods, rotationType) {
        if (!startDate || !periods || !rotationType) return null;

        const start = new Date(startDate);
        let end = new Date(start);

        if (rotationType === 'weekly') {
            // Tambah (periods * 7) - 1 hari
            end.setDate(end.getDate() + (periods * 7) - 1);
        } else if (rotationType === 'monthly') {
            // Untuk bulanan, hitung end dari periode terakhir
            let current = new Date(start);
            for (let i = 0; i < periods; i++) {
                if (i === 0) {
                    // Periode pertama: sampai akhir bulan
                    current = new Date(current.getFullYear(), current.getMonth() + 1, 0);
                } else {
                    // Periode berikutnya: akhir bulan
                    current = new Date(current.getFullYear(), current.getMonth() + 2, 0);
                }
            }
            end = current;
        }

        return end;
    }

    function formatEstimatedTime(totalRecords) {
        if (totalRecords === 0) return '~1 detik';
        if (totalRecords <= 50) return '~1-2 detik';
        if (totalRecords <= 100) return '~2-3 detik';
        if (totalRecords <= 200) return '~3-5 detik';
        return '~5-10 detik';
    }

    function updateEstimation() {
        const workerCount = selectedContainer.querySelectorAll('span[data-id]').length;
        const rotationType = rotationTypeSelect?.value || '';
        const startDate = startDateInput?.value || '';
        const endDate = endDateInput?.value || '';

        // Update jumlah pegawai
        estWorkers.textContent = workerCount;

        // Hitung periode
        const periods = calculatePeriods(startDate, endDate, rotationType);
        estPeriods.textContent = periods;

        // Hitung total
        const total = workerCount * periods;
        estTotal.innerHTML = `${total} <span class="text-xs text-gray-500">data</span>`;

        // Hitung tanggal akhir estimasi
        let estimatedEnd = null;
        if (endDate) {
            estimatedEnd = new Date(endDate);
        } else if (startDate && rotationType && periods > 0) {
            estimatedEnd = calculateEndDate(startDate, periods, rotationType);
        }

        // Format tanggal akhir
        if (estimatedEnd) {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            const day = estimatedEnd.getDate();
            const month = monthNames[estimatedEnd.getMonth()];
            const year = estimatedEnd.getFullYear();
            estEndDate.innerHTML = `<span class="text-sm">${day} ${month} ${year}</span>`;
        } else {
            estEndDate.textContent = '-';
        }

        // Deskripsi
        let desc = '';
        if (workerCount === 0) {
            desc = 'Belum ada pegawai terpilih';
        } else if (!rotationType) {
            desc = 'Pilih tipe rotasi terlebih dahulu';
        } else if (!startDate) {
            desc = 'Tentukan tanggal mulai';
        } else {
            const rotationLabel = rotationType === 'weekly' ? 'mingguan' : 'bulanan';
            const endLabel = endDate ? ` sampai ${new Date(endDate).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})}` : ' (12 periode default)';
            const durationText = estimatedEnd ? ` — berlaku sampai ${estimatedEnd.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}` : '';
            desc = `${workerCount} pegawai × ${periods} periode ${rotationLabel}${endLabel}${durationText}`;
        }
        estDescription.textContent = desc;

        // Tampilkan atau sembunyikan box
        if (workerCount > 0 || startDate || rotationType) {
            estimationBox?.classList.remove('hidden');
        } else {
            estimationBox?.classList.add('hidden');
        }
    }

    function syncRemoveButtons() {
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

    syncRemoveButtons();

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.setAttribute('min', this.value);
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = '';
            }
            updateEstimation();
        });

        if (startDateInput.value) {
            endDateInput.setAttribute('min', startDateInput.value);
        }

        endDateInput.addEventListener('change', updateEstimation);
    }

    // Event listener untuk rotation type
    rotationTypeSelect?.addEventListener('change', updateEstimation);

    // Initial estimation update (untuk old input)
    updateEstimation();
});
</script>
@endsection
