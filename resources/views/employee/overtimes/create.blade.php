@extends('layouts.employee')

@section('title', 'Ajukan Lembur')

@section('content')
<div class="max-w-2xl mx-auto" x-data="overtimeForm()">
    {{-- Page Header --}}
    <div class="flex items-center gap-3 sm:gap-4 mb-6">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Ajukan Lembur</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Isi formulir pengajuan lembur dengan lengkap</p>
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

    <form action="{{ route('employee.overtimes.store') }}" method="POST" class="space-y-4 sm:space-y-5">
        @csrf

        {{-- Card 1: Tanggal Lembur --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-orange-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Tanggal Lembur</h2>
            </div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1.5">
                Tanggal <span class="text-red-500">*</span>
            </label>
            <input type="date" name="date" id="date" required
                   x-model="overtimeDate" @change="fetchShiftTime()"
                   min="{{ now()->format('Y-m-d') }}"
                   class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-sm sm:text-base @error('date') border-red-400 bg-red-50 @enderror">
            @error('date')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            {{-- Fetching spinner --}}
            <div x-show="fetching" x-cloak x-transition class="mt-3 flex items-center gap-2 text-sm text-gray-500">
                <svg class="w-4 h-4 animate-spin text-orange-500" width="16" height="16" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Memuat informasi shift...
            </div>

            {{-- Shift info banner --}}
            <div x-show="shiftInfo && !fetching" x-cloak x-transition
                 class="mt-3 flex items-center gap-2 px-3 sm:px-4 py-2.5 bg-orange-50 rounded-xl border border-orange-200">
                <svg class="w-4 h-4 text-orange-500 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-orange-800 truncate">Shift Anda: <strong x-text="shiftInfo"></strong></p>
            </div>
        </div>

        {{-- Card 2: Waktu Lembur --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Waktu Lembur</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-3">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Waktu Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="start_time" id="start_time" required
                           x-model="startTime" @change="calculateHours()"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-sm sm:text-base @error('start_time') border-red-400 bg-red-50 @enderror">
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Waktu Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="end_time" id="end_time" required
                           x-model="endTime" @change="calculateHours()"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-sm sm:text-base @error('end_time') border-red-400 bg-red-50 @enderror">
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Live duration badge --}}
            <div x-show="totalHours > 0" x-cloak x-transition
                 class="flex items-center justify-between px-3 sm:px-4 py-3 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl border border-orange-200">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-600 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-orange-800">Total Lembur</span>
                </div>
                <span class="text-lg sm:text-2xl font-bold text-orange-700" x-text="totalHours + ' jam'"></span>
            </div>

            <p class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Lembur lintas tengah malam dihitung otomatis
            </p>
        </div>

        {{-- Card 3: Alasan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-rose-600" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Alasan Lembur</h2>
            </div>
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1.5">
                Alasan <span class="text-red-500">*</span>
            </label>
            <textarea name="reason" id="reason" rows="4" required
                      class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-transparent transition resize-none text-sm sm:text-base @error('reason') border-red-400 bg-red-50 @enderror"
                      placeholder="Jelaskan alasan lembur dan pekerjaan yang akan diselesaikan..."
                      oninput="document.getElementById('reasonCount').textContent = this.value.length + ' karakter'">{{ old('reason') }}</textarea>
            <div class="flex justify-between items-center mt-1">
                @error('reason')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @else
                    <span class="text-xs text-gray-400">Jelaskan pekerjaan yang diselesaikan</span>
                @enderror
                <span id="reasonCount" class="text-xs text-gray-400 ml-auto">0 karakter</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1">
            <a href="{{ route('employee.overtimes.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="w-full sm:w-auto sm:flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Ajukan Lembur
            </button>
        </div>
    </form>
</div>

<script>
function overtimeForm() {
    return {
        startTime:    '{{ old("start_time", "") }}',
        endTime:      '{{ old("end_time", "") }}',
        overtimeDate: '{{ old("date", "") }}',
        totalHours:   0,
        shiftInfo:    '',
        fetching:     false,
        workerId:     '{{ $worker->id ?? "" }}',

        async fetchShiftTime() {
            if (!this.workerId || !this.overtimeDate) { this.shiftInfo = ''; return; }
            this.fetching  = true;
            this.shiftInfo = '';
            try {
                const res = await fetch('/api/workers/' + this.workerId + '/shift-time?date=' + this.overtimeDate);
                if (res.ok) {
                    const data     = await res.json();
                    this.startTime = data.end_time;
                    this.shiftInfo = data.shift_name + ' (' + data.start_time + ' - ' + data.end_time + ')';
                    this.calculateHours();
                }
            } catch (e) { console.error('Shift fetch error:', e); }
            finally { this.fetching = false; }
        },

        calculateHours() {
            if (this.startTime && this.endTime) {
                const start = new Date('2000-01-01 ' + this.startTime);
                const end   = new Date('2000-01-01 ' + this.endTime);
                let diff = (end - start) / 3600000;
                if (diff < 0) diff += 24;
                this.totalHours = Math.round(diff * 2) / 2;
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const reason = document.getElementById('reason');
    if (reason && reason.value) {
        document.getElementById('reasonCount').textContent = reason.value.length + ' karakter';
    }
});
</script>
@endsection
