@extends('layouts.employee')

@section('title', 'Ajukan Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-3 sm:gap-4 mb-6">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Ajukan Perjalanan Dinas</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Isi formulir perjalanan dinas dengan lengkap</p>
        </div>
    </div>

    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <ul class="text-sm space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Info Banner --}}
    <div class="flex items-start gap-3 px-4 py-3.5 bg-blue-50 rounded-xl border border-blue-200 mb-5">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-blue-800 mb-1">Informasi Penting</p>
            <ul class="text-xs sm:text-sm text-blue-700 space-y-0.5 list-disc list-inside">
                <li>Diajukan minimal 1 hari sebelum keberangkatan</li>
                <li>Estimasi biaya harus realistis sesuai standar</li>
                <li>Memerlukan persetujuan atasan</li>
            </ul>
        </div>
    </div>

    <form action="{{ route('employee.business-trips.store') }}" method="POST" enctype="multipart/form-data" id="tripForm" class="space-y-4 sm:space-y-5">
        @csrf

        {{-- Card 1: Tujuan & Tanggal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Informasi Dasar</h2>
            </div>

            <div class="mb-4">
                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Tujuan Perjalanan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="destination" id="destination"
                       value="{{ old('destination') }}"
                       placeholder="Contoh: Jakarta, Surabaya, Bandung"
                       class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('destination') border-red-400 bg-red-50 @enderror"
                       required>
                @error('destination')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-3">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tanggal Keberangkatan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date"
                           value="{{ old('start_date') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('start_date') border-red-400 bg-red-50 @enderror"
                           required>
                    <p class="mt-1 text-xs text-gray-400">Minimal besok</p>
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tanggal Kembali <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date"
                           value="{{ old('end_date') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('end_date') border-red-400 bg-red-50 @enderror"
                           required>
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-3">
                <div>
                    <label for="trip_duration_type" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tipe Durasi <span class="text-red-500">*</span>
                    </label>
                    <select name="trip_duration_type" id="trip_duration_type"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('trip_duration_type') border-red-400 bg-red-50 @enderror"
                            required>
                        <option value="full_day" {{ old('trip_duration_type', 'full_day') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                        <option value="half_day" {{ old('trip_duration_type') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                    </select>
                    @error('trip_duration_type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div id="halfDaySessionWrapper" class="{{ old('trip_duration_type') === 'half_day' ? '' : 'hidden' }}">
                    <label for="half_day_session" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Sesi Setengah Hari <span class="text-red-500">*</span>
                    </label>
                    <select name="half_day_session" id="half_day_session"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('half_day_session') border-red-400 bg-red-50 @enderror">
                        <option value="">-- Pilih Sesi --</option>
                        <option value="pagi" {{ old('half_day_session') == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="siang" {{ old('half_day_session') == 'siang' ? 'selected' : '' }}>Siang</option>
                    </select>
                    @error('half_day_session')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Dynamic Session Guidance --}}
                    <div id="sessionGuidance" class="mt-3 hidden p-3 rounded-xl border border-amber-200 bg-amber-50 animate-fadeIn">
                        <div class="flex gap-2.5">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-1">Panduan Absensi</p>
                                <p id="sessionGuidanceText" class="text-xs sm:text-sm text-amber-700 leading-relaxed"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tripDayCounter" class="hidden items-center gap-2 px-3 sm:px-4 py-2.5 bg-blue-50 rounded-xl border border-blue-200">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-blue-800">Durasi perjalanan: <strong id="tripDayCount" class="font-bold"></strong></span>
            </div>
        </div>

        {{-- Card 2: Transportasi & Akomodasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Transportasi & Akomodasi</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4">
                <div>
                    <label for="transportation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Jenis Transportasi <span class="text-red-500">*</span>
                    </label>
                    <select name="transportation" id="transportation"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('transportation') border-red-400 bg-red-50 @enderror"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="Pesawat"       {{ old('transportation') == 'Pesawat'       ? 'selected' : '' }}>Pesawat</option>
                        <option value="Kereta Api"    {{ old('transportation') == 'Kereta Api'    ? 'selected' : '' }}>Kereta Api</option>
                        <option value="Bus"           {{ old('transportation') == 'Bus'           ? 'selected' : '' }}>Bus</option>
                        <option value="Mobil Dinas"   {{ old('transportation') == 'Mobil Dinas'   ? 'selected' : '' }}>Mobil Dinas</option>
                        <option value="Mobil Pribadi" {{ old('transportation') == 'Mobil Pribadi' ? 'selected' : '' }}>Mobil Pribadi</option>
                        <option value="Lainnya"       {{ old('transportation') == 'Lainnya'       ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('transportation')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="accommodation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Akomodasi <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                    </label>
                    <select name="accommodation" id="accommodation"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('accommodation') border-red-400 bg-red-50 @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="Hotel"          {{ old('accommodation') == 'Hotel'          ? 'selected' : '' }}>Hotel</option>
                        <option value="Guest House"    {{ old('accommodation') == 'Guest House'    ? 'selected' : '' }}>Guest House</option>
                        <option value="Wisma"          {{ old('accommodation') == 'Wisma'          ? 'selected' : '' }}>Wisma</option>
                        <option value="Tidak Menginap" {{ old('accommodation') == 'Tidak Menginap' ? 'selected' : '' }}>Tidak Menginap</option>
                        <option value="Lainnya"        {{ old('accommodation') == 'Lainnya'        ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('accommodation')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="estimated_cost" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Estimasi Total Biaya <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium text-sm">Rp</span>
                    </div>
                    <input type="number" name="estimated_cost" id="estimated_cost"
                           value="{{ old('estimated_cost') }}"
                           placeholder="0"
                           min="0" step="1000"
                           class="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('estimated_cost') border-red-400 bg-red-50 @enderror"
                           required>
                </div>
                <p class="mt-1 text-xs text-gray-400">Termasuk transportasi, akomodasi, dan konsumsi</p>
                @error('estimated_cost')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p id="costDisplay" class="hidden mt-2 text-sm font-semibold text-blue-700"></p>
            </div>
        </div>

        {{-- Card 3: Detail Perjalanan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Detail Perjalanan</h2>
            </div>

            <div class="mb-4">
                <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Tujuan / Keperluan <span class="text-red-500">*</span>
                </label>
                <textarea name="purpose" id="purpose" rows="4" required
                          class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none text-sm sm:text-base @error('purpose') border-red-400 bg-red-50 @enderror"
                          placeholder="Jelaskan tujuan perjalanan, agenda, atau keperluan lainnya..."
                          oninput="updatePurposeCounter(this)">{{ old('purpose') }}</textarea>
                <div class="flex justify-between items-center mt-1">
                    @error('purpose')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @else
                        <span id="purposeHint" class="text-xs text-gray-400">Minimal 50 karakter</span>
                    @enderror
                    <span id="purposeCount" class="text-xs text-gray-400 ml-auto">0 / 50</span>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Catatan Tambahan <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                </label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none text-sm sm:text-base @error('notes') border-red-400 bg-red-50 @enderror"
                          placeholder="Catatan khusus atau informasi tambahan...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="supporting_document" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Surat Tugas / Disposisi <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                </label>
                <input type="file" name="supporting_document" id="supporting_document" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm sm:text-base @error('supporting_document') border-red-400 bg-red-50 @enderror">
                <p class="mt-1 text-xs text-gray-500">Upload surat tugas/disposisi jika ada (PDF/JPG/PNG, maks 5MB).</p>
                @error('supporting_document')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1">
            <a href="{{ route('employee.business-trips.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit" id="submitBtn"
                    class="w-full sm:w-auto sm:flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Ajukan Permohonan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    var tripDurationTypeInput = document.getElementById('trip_duration_type');
    var halfDaySessionInput = document.getElementById('half_day_session');
    var halfDaySessionWrapper = document.getElementById('halfDaySessionWrapper');
    var counter = document.getElementById('tripDayCounter');
    var countText = document.getElementById('tripDayCount');
    var costInput = document.getElementById('estimated_cost');
    var costDisplay = document.getElementById('costDisplay');

    function isHalfDay() {
        return tripDurationTypeInput.value === 'half_day';
    }

    function syncHalfDayState() {
        if (isHalfDay()) {
            halfDaySessionWrapper.classList.remove('hidden');
            endDateInput.value = startDateInput.value;
            endDateInput.readOnly = true;
            endDateInput.classList.add('cursor-not-allowed', 'bg-gray-100');
        } else {
            halfDaySessionWrapper.classList.add('hidden');
            halfDaySessionInput.value = '';
            endDateInput.readOnly = false;
            endDateInput.classList.remove('cursor-not-allowed', 'bg-gray-100');
        }

        updateDateCounter();
    }

    function updateDateCounter() {
        if (startDateInput.value && endDateInput.value) {
            if (isHalfDay()) {
                var session = halfDaySessionInput.value;
                var guidance = document.getElementById('sessionGuidance');
                var guidanceText = document.getElementById('sessionGuidanceText');

                if (session === 'pagi') {
                    guidanceText.innerHTML = '<strong>Sesi Pagi:</strong> Anda diperbolehkan tidak melakukan <strong>Absen Masuk</strong>. Namun, Anda tetap wajib melakukan <strong>Absen Pulang</strong> di kantor setelah perjalanan selesai.';
                    guidance.classList.remove('hidden');
                } else if (session === 'siang') {
                    guidanceText.innerHTML = '<strong>Sesi Siang:</strong> Anda tetap wajib melakukan <strong>Absen Masuk</strong> di pagi hari. Anda diperbolehkan tidak melakukan <strong>Absen Pulang</strong> karena perjalanan berakhir di luar kantor.';
                    guidance.classList.remove('hidden');
                } else {
                    guidance.classList.add('hidden');
                }

                var sessionLabel = halfDaySessionInput.value ? ' (' + halfDaySessionInput.options[halfDaySessionInput.selectedIndex].text + ')' : '';
                countText.textContent = '0.5 hari' + sessionLabel;
                counter.classList.remove('hidden');
                counter.classList.add('flex');
                return;
            }

            var start = new Date(startDateInput.value);
            var end = new Date(endDateInput.value);
            if (end >= start) {
                var days = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
                countText.textContent = days + ' hari';
                counter.classList.remove('hidden');
                counter.classList.add('flex');
                return;
            }
        }

        counter.classList.add('hidden');
        counter.classList.remove('flex');
    }

    startDateInput.addEventListener('change', function () {
        endDateInput.min = this.value;
        if (isHalfDay()) {
            endDateInput.value = this.value;
        } else if (endDateInput.value && new Date(endDateInput.value) < new Date(this.value)) {
            endDateInput.value = '';
        }
        updateDateCounter();
    });

    endDateInput.addEventListener('change', updateDateCounter);
    tripDurationTypeInput.addEventListener('change', syncHalfDayState);
    halfDaySessionInput.addEventListener('change', updateDateCounter);
    syncHalfDayState();

    costInput.addEventListener('input', function () {
        var val = parseInt(this.value, 10);
        if (!isNaN(val) && val > 0) {
            costDisplay.textContent = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
            costDisplay.classList.remove('hidden');
        } else {
            costDisplay.classList.add('hidden');
        }
    });
    if (costInput.value) {
        costInput.dispatchEvent(new Event('input'));
    }

    var purpose = document.getElementById('purpose');
    if (purpose && purpose.value) {
        updatePurposeCounter(purpose);
    }

    document.getElementById('tripForm').addEventListener('submit', function (e) {
        var purposeVal = purpose.value.trim();
        if (purposeVal.length < 50) {
            e.preventDefault();
            purpose.focus();
            purpose.classList.add('border-red-400', 'bg-red-50');
            var hint = document.getElementById('purposeHint');
            if (hint) {
                hint.textContent = 'Minimal 50 karakter. Saat ini: ' + purposeVal.length;
                hint.classList.add('text-red-500');
                hint.classList.remove('text-gray-400');
            }
            return;
        }

        if (isHalfDay()) {
            endDateInput.value = startDateInput.value;
        }

        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Memproses...';
        setTimeout(function () {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Ajukan Permohonan';
        }, 4000);
    });
});

function updatePurposeCounter(el) {
    var count = el.value.length;
    var countEl = document.getElementById('purposeCount');
    var hintEl = document.getElementById('purposeHint');
    countEl.textContent = count + ' / 50';
    if (count >= 50) {
        countEl.classList.add('text-emerald-600');
        countEl.classList.remove('text-gray-400', 'text-red-500');
        if (hintEl) {
            hintEl.textContent = 'Sudah memenuhi minimal karakter';
            hintEl.classList.add('text-emerald-600');
            hintEl.classList.remove('text-gray-400', 'text-red-500');
        }
        el.classList.remove('border-red-400', 'bg-red-50');
    } else {
        countEl.classList.remove('text-emerald-600', 'text-red-500');
        countEl.classList.add('text-gray-400');
        if (hintEl) {
            hintEl.textContent = 'Minimal 50 karakter';
            hintEl.classList.remove('text-emerald-600', 'text-red-500');
            hintEl.classList.add('text-gray-400');
        }
    }
}
</script>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
@endsection
