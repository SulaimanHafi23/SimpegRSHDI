@extends('layouts.employee')

@section('title', 'Ajukan Lembur')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl" x-data="overtimeForm()">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Ajukan Lembur</h1>
        <p class="text-gray-600 mt-1">Buat permohonan lembur baru</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.overtimes.store') }}" method="POST">
            @csrf

            <!-- Date -->
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       name="date"
                       id="date"
                       required
                       x-model="overtimeDate"
                       @change="fetchShiftTime()"
                       min="{{ now()->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('date') border-red-500 @enderror">
                @error('date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500" x-show="shiftInfo" x-cloak>
                    <i class="fas fa-info-circle mr-1 text-green-500"></i>
                    Shift Anda: <span x-text="shiftInfo"></span>
                </p>
            </div>

            <!-- Start Time -->
            <div class="mb-4">
                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Waktu Mulai <span class="text-red-500">*</span>
                </label>
                <input type="time"
                       name="start_time"
                       id="start_time"
                       required
                       x-model="startTime"
                       @change="calculateHours()"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('start_time') border-red-500 @enderror">
                @error('start_time')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- End Time -->
            <div class="mb-4">
                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Waktu Selesai <span class="text-red-500">*</span>
                </label>
                <input type="time"
                       name="end_time"
                       id="end_time"
                       required
                       x-model="endTime"
                       @change="calculateHours()"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('end_time') border-red-500 @enderror">
                @error('end_time')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Total Hours (calculated) -->
            <div class="mb-4" x-show="totalHours > 0" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Jam Lembur</label>
                <p class="text-lg font-semibold text-green-600"><span x-text="totalHours"></span> jam</p>
            </div>

            <!-- Reason -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan <span class="text-red-500">*</span>
                </label>
                <textarea name="reason"
                          id="reason"
                          rows="4"
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('reason') border-red-500 @enderror"
                          placeholder="Jelaskan alasan pengajuan lembur Anda">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Ajukan Lembur
                </button>
                <a href="{{ route('employee.overtimes.index') }}"
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function overtimeForm() {
    return {
        startTime: '{{ old('start_time', '') }}',
        endTime: '{{ old('end_time', '') }}',
        overtimeDate: '{{ old('date', '') }}',
        totalHours: 0,
        shiftInfo: '',
        workerId: '{{ $worker->id ?? '' }}',

        async fetchShiftTime() {
            if (!this.workerId || !this.overtimeDate) {
                this.shiftInfo = '';
                return;
            }

            try {
                const response = await fetch(`/api/workers/${this.workerId}/shift-time?date=${this.overtimeDate}`);
                if (response.ok) {
                    const data = await response.json();
                    // Auto-fill start_time with shift end_time
                    this.startTime = data.end_time;
                    this.shiftInfo = `${data.shift_name} (${data.start_time} - ${data.end_time})`;
                    this.calculateHours();
                } else {
                    this.shiftInfo = '';
                }
            } catch (e) {
                console.error('Error fetching shift time:', e);
                this.shiftInfo = '';
            }
        },

        calculateHours() {
            if (this.startTime && this.endTime) {
                const start = new Date('2000-01-01 ' + this.startTime);
                const end = new Date('2000-01-01 ' + this.endTime);

                let diff = (end - start) / 1000 / 60 / 60; // difference in hours

                // Handle overnight
                if (diff < 0) {
                    diff += 24;
                }

                this.totalHours = Math.round(diff * 2) / 2; // Round to nearest 0.5
            }
        }
    }
}
</script>
@endsection
