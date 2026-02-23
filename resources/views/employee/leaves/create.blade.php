@extends('layouts.employee')

@section('title', 'Ajukan Cuti')

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center gap-3 sm:gap-4 mb-6">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Ajukan Cuti</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Isi formulir pengajuan cuti dengan lengkap</p>
        </div>
    </div>

    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5">
            <svg class="w-5 h-5 mt-0.5 shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <ul class="text-sm space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employee.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
        @csrf

        {{-- Card 1: Jenis Cuti --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6"
             x-data="leaveTypeSelector()" x-init="init()">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Jenis Cuti</h2>
            </div>
            <label for="leave_type_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                Pilih Jenis Cuti <span class="text-red-500">*</span>
            </label>
            <select name="leave_type_id" id="leave_type_id" required
                    x-model="selected" @change="onChange()"
                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition text-sm sm:text-base @error('leave_type_id') border-red-400 bg-red-50 @enderror">
                <option value="">-- Pilih Jenis Cuti --</option>
                @foreach($leaveTypes as $type)
                    @php
                        $maxDays   = $type->max_days_per_year ?? 0;
                        $usedCount = $usedDays[$type->id] ?? 0;
                        $remaining = max(0, $maxDays - $usedCount);
                    @endphp
                    <option value="{{ $type->id }}"
                            data-max="{{ $maxDays }}"
                            data-used="{{ $usedCount }}"
                            data-remaining="{{ $remaining }}"
                            {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                        @if($maxDays > 0)
                            — sisa {{ $remaining }}/{{ $maxDays }} hari
                        @endif
                    </option>
                @endforeach
            </select>
            @error('leave_type_id')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            {{-- Quota info bar (shown once user picks a type) --}}
            <div x-show="selected" x-cloak x-transition class="mt-3">
                {{-- Progress bar --}}
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs text-gray-500">Kuota tahun {{ now()->year }}</span>
                    <span class="text-xs font-semibold"
                          :class="remaining === 0 ? 'text-red-600' : (remaining <= 3 ? 'text-amber-600' : 'text-emerald-600')"
                          x-text="remaining + ' hari tersisa dari ' + maxDays + ' hari'"></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500"
                         :class="remaining === 0 ? 'bg-red-400' : (remaining <= 3 ? 'bg-amber-400' : 'bg-emerald-500')"
                         :style="'width:' + (maxDays > 0 ? Math.round((remaining / maxDays) * 100) : 0) + '%'"></div>
                </div>
                {{-- Warning if exhausted --}}
                <div x-show="remaining === 0" x-cloak
                     class="mt-2 flex items-center gap-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                    <svg class="w-3.5 h-3.5 shrink-0" width="14" height="14" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Kuota cuti jenis ini sudah habis untuk tahun {{ now()->year }}
                </div>
                {{-- Warning if almost exhausted --}}
                <div x-show="remaining > 0 && remaining <= 3" x-cloak
                     class="mt-2 flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    <svg class="w-3.5 h-3.5 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Sisa kuota hampir habis, gunakan dengan bijak
                </div>
            </div>
        </div>

        {{-- Card 2: Periode Cuti --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Periode Cuti</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-3">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" required
                           value="{{ old('start_date') }}"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition text-sm sm:text-base @error('start_date') border-red-400 bg-red-50 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" required
                           value="{{ old('end_date') }}"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition text-sm sm:text-base @error('end_date') border-red-400 bg-red-50 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div id="dayCounter" class="hidden items-center gap-2 px-3 sm:px-4 py-2.5 bg-emerald-50 rounded-xl border border-emerald-200">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-emerald-800">Durasi cuti: <strong id="dayCount" class="font-bold"></strong></span>
            </div>
        </div>

        {{-- Card 3: Alasan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Alasan Cuti</h2>
            </div>
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1.5">
                Alasan <span class="text-red-500">*</span>
            </label>
            <textarea name="reason" id="reason" rows="4" required
                      class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none text-sm sm:text-base @error('reason') border-red-400 bg-red-50 @enderror"
                      placeholder="Jelaskan alasan pengajuan cuti Anda..."
                      oninput="document.getElementById('reasonCount').textContent = this.value.length + ' karakter'">{{ old('reason') }}</textarea>
            <div class="flex justify-between items-center mt-1">
                @error('reason')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @else
                    <span class="text-xs text-gray-400">Jelaskan dengan jelas</span>
                @enderror
                <span id="reasonCount" class="text-xs text-gray-400 ml-auto">0 karakter</span>
            </div>
        </div>

        {{-- Card 4: Dokumen Pendukung --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-purple-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm sm:text-base font-semibold text-gray-800">Dokumen Pendukung</h2>
                    <p class="text-xs text-gray-400">Opsional</p>
                </div>
            </div>
            <label for="document"
                   class="relative flex flex-col items-center justify-center w-full h-28 sm:h-36 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-emerald-50 hover:border-emerald-400 transition-all group">
                <div id="uploadPlaceholder" class="flex flex-col items-center py-3 text-center pointer-events-none px-4">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300 group-hover:text-emerald-400 mb-1.5 transition" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-xs sm:text-sm text-gray-500"><span class="font-semibold text-emerald-600">Pilih file</span> atau seret ke sini</p>
                    <p class="text-xs text-gray-400 mt-0.5">PDF, JPG, JPEG, PNG (maks. 2MB)</p>
                </div>
                <div id="uploadFileName" class="hidden items-center gap-2 py-3 pointer-events-none px-4 max-w-full">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span id="uploadFileNameText" class="text-sm font-medium text-emerald-700 truncate"></span>
                </div>
                <input type="file" name="document" id="document" accept=".pdf,.jpg,.jpeg,.png"
                       class="absolute inset-0 opacity-0 cursor-pointer"
                       onchange="handleFileSelect(this)">
            </label>
            @error('document')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1">
            <a href="{{ route('employee.leaves.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="w-full sm:w-auto sm:flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Ajukan Cuti
            </button>
        </div>
    </form>
</div>

<script>
function handleFileSelect(input) {
    const placeholder = document.getElementById('uploadPlaceholder');
    const fileBox     = document.getElementById('uploadFileName');
    const fileText    = document.getElementById('uploadFileNameText');
    if (input.files && input.files[0]) {
        placeholder.classList.add('hidden');
        fileBox.classList.remove('hidden');
        fileBox.classList.add('flex');
        fileText.textContent = input.files[0].name;
    } else {
        placeholder.classList.remove('hidden');
        fileBox.classList.add('hidden');
        fileBox.classList.remove('flex');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('start_date');
    const endInput   = document.getElementById('end_date');
    const counter    = document.getElementById('dayCounter');
    const countText  = document.getElementById('dayCount');

    function updateCounter() {
        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end   = new Date(endInput.value);
            if (end >= start) {
                const days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
                countText.textContent = days + ' hari';
                counter.classList.remove('hidden');
                counter.classList.add('flex');
                return;
            }
        }
        counter.classList.add('hidden');
        counter.classList.remove('flex');
    }

    startInput.addEventListener('change', function () {
        endInput.min = this.value;
        if (endInput.value && new Date(endInput.value) < new Date(this.value)) endInput.value = '';
        updateCounter();
    });
    endInput.addEventListener('change', updateCounter);

    const reason = document.getElementById('reason');
    if (reason.value) document.getElementById('reasonCount').textContent = reason.value.length + ' karakter';
    updateCounter();
});

function leaveTypeSelector() {
    return {
        selected: '{{ old('leave_type_id', '') }}',
        maxDays: 0,
        used: 0,
        remaining: 0,

        init() {
            if (this.selected) this.readOption(this.selected);
        },

        onChange() {
            this.readOption(this.selected);
        },

        readOption(val) {
            if (!val) { this.maxDays = 0; this.used = 0; this.remaining = 0; return; }
            const opt = document.querySelector('#leave_type_id option[value="' + val + '"]');
            if (opt) {
                this.maxDays   = parseInt(opt.dataset.max   || 0);
                this.used      = parseInt(opt.dataset.used  || 0);
                this.remaining = parseInt(opt.dataset.remaining || 0);
            }
        }
    };
}
</script>
@endsection
