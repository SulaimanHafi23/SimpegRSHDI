@extends('layouts.admin')

@section('title', 'Tambah Jadwal Shift')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.worker-shifts.index') }}" class="hover:text-green-600">Jadwal Shift</a>
            <span>/</span>
            <span class="text-gray-800">Tambah Jadwal</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Tambah Jadwal Shift</h1>
        <p class="text-gray-600 mt-1">Buat jadwal shift baru untuk pegawai</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.worker-shifts.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Pegawai -->
            <div class="mb-6">
                <label for="worker_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Pegawai <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select id="worker_select" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}" data-label="{{ $worker->nip }} - {{ $worker->name }}">{{ $worker->nip }} - {{ $worker->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="add_worker_btn" class="px-4 py-2 bg-green-600 text-white rounded-lg">Tambah</button>
                </div>
                <div id="selected-workers" class="mt-3 flex flex-wrap gap-2">
                    {{-- chips for selected workers will be injected here --}}
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
                @error('worker_ids')
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
                        <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
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
                        <input type="radio" name="pattern_type" value="fixed" class="pattern-type" {{ old('pattern_type', 'fixed') == 'fixed' ? 'checked' : '' }}>
                        <span class="ml-2">Fixed (selalu pakai shift ini)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="pattern_type" value="rotating" class="pattern-type" {{ old('pattern_type') == 'rotating' ? 'checked' : '' }}>
                        <span class="ml-2">Rotating (atur per hari: Sen–Ming)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="pattern_type" value="custom" class="pattern-type" {{ old('pattern_type') == 'custom' ? 'checked' : '' }}>
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
                                    <option value="{{ $shiftOption->id }}" {{ (old('rotating_days.'.$num) == $shiftOption->id) ? 'selected' : '' }}>
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
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="custom_working_days[]" value="{{ $num }}" {{ (is_array(old('custom_working_days', [])) && in_array($num, old('custom_working_days', []))) ? 'checked' : '' }}>
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
                           value="{{ old('start_date') }}"
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
                           value="{{ old('end_date') }}"
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
                           {{ old('is_active', true) ? 'checked' : '' }}
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
                    Simpan Jadwal
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

    <!-- Info Box -->
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
                    <li>Pastikan pegawai belum memiliki jadwal shift yang aktif pada periode yang sama</li>
                    <li>Tanggal selesai boleh dikosongkan jika jadwal berlaku tanpa batas waktu</li>
                    <li>Jadwal yang tidak aktif tidak akan muncul dalam sistem absensi</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const today = new Date().toISOString().split('T')[0];
    
    startDateInput.setAttribute('min', today);
    
    // Update end date minimum when start date changes
    startDateInput.addEventListener('change', function() {
        endDateInput.setAttribute('min', this.value);
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = '';
        }
    });
    
    // Show/hide rotating/custom sections based on pattern type
    function togglePatternSections() {
        const selected = document.querySelector('input[name="pattern_type"]:checked')?.value || 'fixed';
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

    // Add worker selection behaviour (single select + Add button -> chips + hidden inputs)
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
        // avoid duplicates
        if (selectedContainer.querySelector(`[data-id="${id}"]`)) {
            // already added
            workerSelect.value = '';
            return;
        }
        const option = workerSelect.options[workerSelect.selectedIndex];
        const label = option.getAttribute('data-label') || option.text;
        const chip = createChip(id, label);
        selectedContainer.appendChild(chip);
        workerSelect.value = '';
    }

    addBtn?.addEventListener('click', addSelectedWorker);

    // delegate remove
    selectedContainer?.addEventListener('click', function(e){
        if (e.target.classList.contains('remove-worker')) {
            const chip = e.target.closest('span[data-id]');
            chip?.remove();
        }
    });
});
</script>
@endsection
