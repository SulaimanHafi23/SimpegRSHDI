@extends('layouts.employee')

@section('title', 'Buat Permintaan Tukar Shift')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Buat Permintaan Tukar Shift</h1>
                <p class="text-gray-600 mt-1">Ajukan permintaan tukar shift dengan rekan kerja</p>
            </div>
            <a href="{{ route('employee.shift-swaps.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>


    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="font-bold">Terjadi kesalahan:</div>
            <ul class="list-disc list-inside mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.shift-swaps.store') }}" method="POST">
            @csrf

            <!-- Info Banner -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Ketentuan Tukar Shift:</h3>
                        <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                            <li>Pilih tanggal tertentu, rentang tanggal, atau beberapa tanggal spesifik</li>
                            <li><strong>Minimal 48 jam (2 hari)</strong> sebelum shift dimulai</li>
                            <li>Minimal 12 jam istirahat antara shift</li>
                            <li>Tidak boleh double shift dalam satu hari</li>
                            <li><strong>Semua tukar shift:</strong> Memerlukan persetujuan HR</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            <!-- Your Shift Selection -->
            <div class="mb-6">
                <label for="requester_shift_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-clock text-green-600 mr-1"></i>
                    Shift Anda Yang Akan Ditukar
                    <span class="text-red-500">*</span>
                </label>
                <select name="requester_shift_id"
                        id="requester_shift_id"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('requester_shift_id') border-red-500 @enderror">
                    <option value="">-- Pilih Shift Anda --</option>
                    @foreach($requesterShifts as $ws)
                        <option value="{{ $ws->id }}" {{ old('requester_shift_id') == $ws->id ? 'selected' : '' }}>
                            {{ $ws->shift?->name ?? 'N/A' }}
                            ({{ $ws->shift ? \Carbon\Carbon::parse($ws->shift->start_time)->format('H:i') : '' }} - {{ $ws->shift ? \Carbon\Carbon::parse($ws->shift->end_time)->format('H:i') : '' }})
                            — Berlaku sejak {{ $ws->effective_from->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
                @error('requester_shift_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Target Worker Selection -->
            <div class="mb-6">
                <label for="target_worker_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user-friends text-blue-600 mr-1"></i>
                    Rekan Kerja Target (Opsional)
                </label>
                <select name="target_worker_id"
                        id="target_worker_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('target_worker_id') border-red-500 @enderror">
                    <option value="">-- Biarkan Kosong untuk Open Request --</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}"
                                data-dept="{{ $w->department->name ?? 'N/A' }}"
                                {{ old('target_worker_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }} - {{ $w->department->name ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Open request berarti siapa saja bisa menerima tukar shift.<br>
                    <span class="text-emerald-600">🔔 Jika memilih rekan kerja target, sistem akan mengirim notifikasi otomatis ke rekan tersebut.</span><br>
                    <span class="text-blue-600">ℹ️ Semua permintaan tukar shift memerlukan persetujuan HR.</span>
                </p>
                @error('target_worker_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Swap Type Selection -->
            <div class="mb-6 xl:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    <i class="fas fa-calendar-check text-indigo-600 mr-1"></i>
                    Jenis Tukar Shift
                    <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="radio" id="single_date" name="swap_type" value="single_date"
                               class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500"
                               {{ old('swap_type', 'single_date') == 'single_date' ? 'checked' : '' }}>
                        <label for="single_date" class="ml-3 block text-sm text-gray-700">
                            <span class="font-medium">Tanggal Tertentu</span>
                            <span class="text-gray-500 block text-xs">Tukar shift untuk satu hari tertentu</span>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" id="date_range" name="swap_type" value="date_range"
                               class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500"
                               {{ old('swap_type') == 'date_range' ? 'checked' : '' }}>
                        <label for="date_range" class="ml-3 block text-sm text-gray-700">
                            <span class="font-medium">Rentang Tanggal</span>
                            <span class="text-gray-500 block text-xs">Tukar shift untuk beberapa hari berturut-turut</span>
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" id="recurring" name="swap_type" value="recurring"
                               class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500"
                               {{ old('swap_type') == 'recurring' ? 'checked' : '' }}>
                        <label for="recurring" class="ml-3 block text-sm text-gray-700">
                            <span class="font-medium">Tanggal Spesifik</span>
                            <span class="text-gray-500 block text-xs">Tukar shift untuk beberapa tanggal tertentu</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Single Date Selection -->
            <div id="single_date_section" class="mb-6 swap-date-section xl:col-span-2">
                <label for="swap_date" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-day text-green-600 mr-1"></i>
                    Tanggal Tukar Shift
                    <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       name="swap_date"
                       id="swap_date"
                       min="{{ date('Y-m-d', strtotime("+{$minDays} days")) }}"
                       value="{{ old('swap_date') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('swap_date') border-red-500 @enderror">
                <p class="text-xs text-gray-500 mt-1">Minimal {{ $minHours }} jam ({{ $minDays }} hari) dari sekarang</p>
                @error('swap_date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Range Selection -->
            <div id="date_range_section" class="mb-6 swap-date-section xl:col-span-2" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="swap_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day text-blue-600 mr-1"></i>
                            Tanggal Mulai
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               name="swap_start_date"
                               id="swap_start_date"
                               min="{{ date('Y-m-d', strtotime("+{$minDays} days")) }}"
                               value="{{ old('swap_start_date') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('swap_start_date') border-red-500 @enderror">
                        @error('swap_start_date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="swap_end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day text-purple-600 mr-1"></i>
                            Tanggal Akhir
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               name="swap_end_date"
                               id="swap_end_date"
                               min="{{ date('Y-m-d', strtotime("+{$minDays} days")) }}"
                               value="{{ old('swap_end_date') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('swap_end_date') border-red-500 @enderror">
                        @error('swap_end_date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Recurring Dates Selection -->
            <div id="recurring_section" class="mb-6 swap-date-section xl:col-span-2" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-plus text-orange-600 mr-1"></i>
                    Tanggal-Tanggal Spesifik
                    <span class="text-red-500">*</span>
                </label>
                <div id="recurring_dates" class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="date"
                               name="swap_dates[]"
                               min="{{ date('Y-m-d', strtotime("+{$minDays} days")) }}"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <button type="button" onclick="addDateInput()"
                                class="px-3 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addDateInput()"
                        class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Tanggal
                </button>
                <p class="text-xs text-gray-500 mt-1">Minimal {{ $minHours }} jam ({{ $minDays }} hari) dari sekarang</p>
                @error('swap_dates')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Target Shift Selection -->
            <div class="mb-6">
                <label for="target_shift_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-purple-600 mr-1"></i>
                    Shift Target (Opsional)
                </label>
                <select name="target_shift_id"
                        id="target_shift_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('target_shift_id') border-red-500 @enderror">
                    <option value="">-- Pilih Rekan Kerja Terlebih Dahulu --</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih shift yang ingin Anda ambil dari rekan kerja</p>
                @error('target_shift_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-comment-alt text-amber-600 mr-1"></i>
                    Alasan Tukar Shift (Opsional)
                </label>
                <textarea name="reason"
                          id="reason"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('reason') border-red-500 @enderror"
                          rows="4"
                          placeholder="Contoh: Ada keperluan keluarga mendadak, perlu ke dokter, dll...">{{ old('reason') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Alasan yang jelas meningkatkan kemungkinan disetujui</p>
                @error('reason')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Kirim Permintaan
                </button>
                <a href="{{ route('employee.shift-swaps.index') }}"
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150 text-center">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
            </div>

            <!-- HR Approval Notice (Always shown) -->
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-blue-800">Informasi Penting</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            Semua permintaan tukar shift memerlukan persetujuan dari HR sebelum dapat diproses.
                            Pastikan alasan yang Anda berikan jelas dan dapat dipertanggungjawabkan.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Handle swap type selection
document.querySelectorAll('input[name="swap_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Hide all date sections
        document.querySelectorAll('.swap-date-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show relevant section
        const selectedType = this.value;
        const sectionId = selectedType + '_section';
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'block';
        }
    });
});

// Initialize display based on selected value
document.addEventListener('DOMContentLoaded', function() {
    const selectedRadio = document.querySelector('input[name="swap_type"]:checked');
    if (selectedRadio) {
        selectedRadio.dispatchEvent(new Event('change'));
    }

    // Set min date for end date based on start date
    const startDateInput = document.getElementById('swap_start_date');
    const endDateInput = document.getElementById('swap_end_date');

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() + 1); // Minimum next day
            const minEndDate = startDate.toISOString().split('T')[0];
            endDateInput.min = minEndDate;

            // Reset end date if it's before new minimum
            if (endDateInput.value && endDateInput.value < minEndDate) {
                endDateInput.value = '';
            }
        });
    }
});

// Function to add new date input for recurring dates
function addDateInput() {
    const container = document.getElementById('recurring_dates');
    const newDateDiv = document.createElement('div');
    newDateDiv.className = 'flex items-center gap-2';

    const minDate = '{{ date("Y-m-d", strtotime("+{$minDays} days")) }}';

    newDateDiv.innerHTML = `
        <input type="date"
               name="swap_dates[]"
               min="${minDate}"
               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        <button type="button" onclick="removeDateInput(this)"
                class="px-3 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <i class="fas fa-minus"></i>
        </button>
    `;

    container.appendChild(newDateDiv);
}

// Function to remove date input
function removeDateInput(button) {
    const container = document.getElementById('recurring_dates');
    const dateInputs = container.querySelectorAll('.flex.items-center');

    // Don't remove if it's the last one
    if (dateInputs.length > 1) {
        button.parentElement.remove();
    }
}

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const swapType = document.querySelector('input[name="swap_type"]:checked')?.value;
    const targetWorkerId = document.getElementById('target_worker_id')?.value;
    const targetShiftId = document.getElementById('target_shift_id')?.value;

    if (!swapType) {
        e.preventDefault();
        window.showWarningAlert('Validasi', 'Pilih jenis tukar shift.');
        return false;
    }

    if (targetWorkerId && !targetShiftId) {
        e.preventDefault();
        window.showWarningAlert('Validasi', 'Pilih shift target jika Anda memilih rekan kerja tertentu.');
        return false;
    }

    // Clear irrelevant fields before submit to avoid validation conflicts
    if (swapType !== 'single_date') {
        const swapDateInput = document.getElementById('swap_date');
        if (swapDateInput) swapDateInput.disabled = true;
    }

    if (swapType !== 'date_range') {
        const startDateInput = document.getElementById('swap_start_date');
        const endDateInput = document.getElementById('swap_end_date');
        if (startDateInput) startDateInput.disabled = true;
        if (endDateInput) endDateInput.disabled = true;
    }

    if (swapType !== 'recurring') {
        const dateInputs = document.querySelectorAll('input[name="swap_dates[]"]');
        dateInputs.forEach(input => input.disabled = true);
    }

    // Validate based on swap type
    if (swapType === 'single_date') {
        const swapDate = document.getElementById('swap_date').value;
        if (!swapDate) {
            e.preventDefault();
            window.showWarningAlert('Validasi', 'Pilih tanggal untuk tukar shift.');
            return false;
        }
    } else if (swapType === 'date_range') {
        const startDate = document.getElementById('swap_start_date').value;
        const endDate = document.getElementById('swap_end_date').value;
        if (!startDate || !endDate) {
            e.preventDefault();
            window.showWarningAlert('Validasi', 'Pilih tanggal mulai dan akhir untuk rentang tukar shift.');
            return false;
        }
    } else if (swapType === 'recurring') {
        const dateInputs = document.querySelectorAll('input[name="swap_dates[]"]:not([disabled])');
        const hasValidDate = Array.from(dateInputs).some(input => input.value.trim() !== '');
        if (!hasValidDate) {
            e.preventDefault();
            window.showWarningAlert('Validasi', 'Pilih minimal satu tanggal untuk tukar shift berulang.');
            return false;
        }
    }

    return true;
});

// Load shifts when worker is selected
document.getElementById('target_worker_id').addEventListener('change', function() {
    const workerId = this.value;
    const targetShiftSelect = document.getElementById('target_shift_id');

    // Clear existing shift options
    targetShiftSelect.innerHTML = '<option value="">-- Memuat shift... --</option>';
    targetShiftSelect.disabled = true;

    if (!workerId) {
        targetShiftSelect.innerHTML = '<option value="">-- Pilih Rekan Kerja Terlebih Dahulu --</option>';
        targetShiftSelect.required = false;
        targetShiftSelect.disabled = false;
        return;
    }

    targetShiftSelect.required = true;

    // Fetch shifts for selected worker
    fetch(`/api/workers/${workerId}/future-shifts`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            targetShiftSelect.innerHTML = '<option value="">-- Pilih Shift Target --</option>';

            if (data.length === 0) {
                targetShiftSelect.innerHTML = '<option value="">-- Tidak ada shift tersedia --</option>';
            } else {
                data.forEach(shift => {
                    const option = document.createElement('option');
                    option.value = shift.id;
                    option.textContent = `${shift.date} - ${shift.shift_name} (${shift.start_time} - ${shift.end_time})`;
                    targetShiftSelect.appendChild(option);
                });
            }
            targetShiftSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading shifts:', error);
            targetShiftSelect.innerHTML = '<option value="">-- Gagal memuat shift --</option>';
            targetShiftSelect.disabled = false;

            // Show user-friendly error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'mt-2 p-3 bg-red-100 text-red-700 text-sm rounded-lg';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data shift. Silakan refresh halaman atau coba lagi.';

            // Remove existing error message
            const existingError = targetShiftSelect.parentNode.querySelector('.error-message');
            if (existingError) existingError.remove();

            errorDiv.classList.add('error-message');
            targetShiftSelect.parentNode.appendChild(errorDiv);

            // Auto remove error message after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) errorDiv.remove();
            }, 5000);
        });
});
</script>
@endsection
