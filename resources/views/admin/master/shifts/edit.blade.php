@extends('layouts.admin')

@section('title', 'Edit Shift')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.shifts.index') }}" class="hover:text-green-600">Shift</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-edit text-green-600 mr-2"></i>
            Edit Shift
        </h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.master.shifts.update', $shift->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Shift -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Shift <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $shift->name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jam Masuk -->
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Masuk <span class="text-red-500">*</span>
                    </label>
                    <input type="time"
                           name="start_time"
                           id="start_time"
                           value="{{ old('start_time', \Carbon\Carbon::parse($shift->start_time)->format('H:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('start_time') border-red-500 @enderror"
                           required>
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jam Keluar -->
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Keluar <span class="text-red-500">*</span>
                    </label>
                    <input type="time"
                           name="end_time"
                           id="end_time"
                           value="{{ old('end_time', \Carbon\Carbon::parse($shift->end_time)->format('H:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('end_time') border-red-500 @enderror"
                           required>
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Jam -->
                <div class="md:col-span-2">
                    <label for="total_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Total Jam <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="total_hours"
                           id="total_hours"
                           value="{{ old('total_hours', $shift->total_hours) }}"
                           step="0.01"
                           readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed @error('total_hours') border-red-500 @enderror"
                           placeholder="Akan dihitung otomatis dari jam masuk dan keluar">
                    @error('total_hours')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i> Total jam akan dihitung otomatis berdasarkan jam masuk dan keluar
                    </p>
                </div>

                <!-- Jadwal Per Hari -->
                <div class="md:col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                        <label class="block text-sm font-medium text-gray-700">Jadwal Per Hari</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Kosongkan jika mengikuti jam default</span>
                            <button type="button" id="fill-default-all" class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">
                                Isi Semua = Default
                            </button>
                        </div>
                    </div>
                    @php
                        $perDayEnabled = old('per_day_enabled', ($shift->dayTimes?->isNotEmpty() ?? false));
                    @endphp
                    <div class="flex items-center gap-2 mb-3">
                        <input type="hidden" name="per_day_enabled" value="0">
                        <input type="checkbox" id="per_day_enabled" name="per_day_enabled" value="1" {{ $perDayEnabled ? 'checked' : '' }} class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <label for="per_day_enabled" class="text-sm text-gray-700">Jadwal per hari berbeda</label>
                    </div>
                    @php
                        $days = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            0 => 'Minggu',
                        ];
                        $dayTimesByKey = $shift->dayTimes?->keyBy('day_of_week') ?? collect();
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="day-schedule-grid">
                        @foreach($days as $dayKey => $dayLabel)
                            @php
                                $dayTime = $dayTimesByKey->get($dayKey);
                                $defaultStart = $dayTime?->start_time ? \Carbon\Carbon::parse($dayTime->start_time)->format('H:i') : '';
                                $defaultEnd = $dayTime?->end_time ? \Carbon\Carbon::parse($dayTime->end_time)->format('H:i') : '';
                                $dayActive = old('day_active.' . $dayKey, $dayTime ? 1 : 0);
                            @endphp
                            <div class="rounded-lg border border-gray-200 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-semibold text-gray-700">{{ $dayLabel }}</div>
                                    <label class="flex items-center gap-2 text-xs text-gray-600">
                                        <input type="checkbox" name="day_active[{{ $dayKey }}]" value="1" {{ $dayActive ? 'checked' : '' }} data-day-active>
                                        Aktif
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Masuk</label>
                                             <input type="time"
                                               name="day_times[{{ $dayKey }}][start_time]"
                                               value="{{ old('day_times.' . $dayKey . '.start_time', $defaultStart) }}"
                                                 data-day-start
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Pulang</label>
                                             <input type="time"
                                               name="day_times[{{ $dayKey }}][end_time]"
                                               value="{{ old('day_times.' . $dayKey . '.end_time', $defaultEnd) }}"
                                                 data-day-end
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('day_times')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="description"
                              id="description"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $shift->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $shift->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="text-sm font-medium text-gray-700">Shift Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                <a href="{{ route('admin.master.shifts.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Function to calculate total hours
    function calculateTotalHours() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        if (startTime && endTime) {
            // Parse hours and minutes
            const [startHour, startMinute] = startTime.split(':').map(num => parseInt(num, 10));
            const [endHour, endMinute] = endTime.split(':').map(num => parseInt(num, 10));

            // Convert to minutes since midnight
            let startMinutes = (startHour * 60) + startMinute;
            let endMinutes = (endHour * 60) + endMinute;

            // Handle overnight shift (end time is before start time)
            if (endMinutes <= startMinutes) {
                endMinutes += (24 * 60); // Add 24 hours in minutes
            }

            // Calculate difference in minutes
            const diffMinutes = endMinutes - startMinutes;

            // Convert to hours (with 2 decimal places)
            const totalHours = parseFloat((diffMinutes / 60).toFixed(2));

            // Update the total_hours field
            document.getElementById('total_hours').value = totalHours;

            console.log('Calculation:', {
                startTime,
                endTime,
                startMinutes,
                endMinutes,
                diffMinutes,
                totalHours
            });
        }
    }

    // Add event listeners when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const fillDefaultButton = document.getElementById('fill-default-all');
        const perDayToggle = document.getElementById('per_day_enabled');
        const dayStartInputs = Array.from(document.querySelectorAll('[data-day-start]'));
        const dayEndInputs = Array.from(document.querySelectorAll('[data-day-end]'));
        const dayActiveInputs = Array.from(document.querySelectorAll('[data-day-active]'));

        // Calculate on change
        startTimeInput.addEventListener('change', calculateTotalHours);
        endTimeInput.addEventListener('change', calculateTotalHours);

        // Calculate on page load if both values exist (for old input)
        calculateTotalHours();

        function setDayInputsEnabled(enabled) {
            dayStartInputs.forEach(input => { input.disabled = !enabled; });
            dayEndInputs.forEach(input => { input.disabled = !enabled; });
            dayActiveInputs.forEach(input => { input.disabled = !enabled; });
        }

        function syncDayActiveState() {
            dayActiveInputs.forEach(function(activeInput, index) {
                const isActive = activeInput.checked;
                if (dayStartInputs[index]) {
                    dayStartInputs[index].disabled = !isActive || !perDayToggle.checked;
                }
                if (dayEndInputs[index]) {
                    dayEndInputs[index].disabled = !isActive || !perDayToggle.checked;
                }
            });
        }

        if (perDayToggle) {
            setDayInputsEnabled(perDayToggle.checked);
            perDayToggle.addEventListener('change', function() {
                setDayInputsEnabled(perDayToggle.checked);
                syncDayActiveState();
            });
        }

        dayActiveInputs.forEach(function(activeInput) {
            activeInput.addEventListener('change', syncDayActiveState);
        });

        if (fillDefaultButton) {
            fillDefaultButton.addEventListener('click', function() {
                const defaultStart = startTimeInput.value;
                const defaultEnd = endTimeInput.value;
                if (!defaultStart || !defaultEnd) {
                    alert('Isi Jam Masuk dan Jam Keluar default terlebih dahulu.');
                    return;
                }

                if (perDayToggle && !perDayToggle.checked) {
                    perDayToggle.checked = true;
                    setDayInputsEnabled(true);
                }

                dayStartInputs.forEach(function(input, index) {
                    const activeInput = dayActiveInputs[index];
                    if (activeInput && !activeInput.checked) {
                        return;
                    }
                    input.value = defaultStart;
                });

                dayEndInputs.forEach(function(input, index) {
                    const activeInput = dayActiveInputs[index];
                    if (activeInput && !activeInput.checked) {
                        return;
                    }
                    input.value = defaultEnd;
                });
            });
        }

        syncDayActiveState();
    });
</script>
@endpush
