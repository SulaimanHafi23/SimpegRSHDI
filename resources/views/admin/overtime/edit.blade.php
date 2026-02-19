@extends('layouts.admin')

@section('title', 'Edit Lembur')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Edit Data Lembur"
        description="Perbarui informasi data lembur"
        icon="fas fa-edit">
        <x-slot:actions>
            <x-button
                variant="secondary"
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.overtime.show', $overtime->id) }}'">
                Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Alert Messages --}}
    @if($errors->any())
        <x-alert type="danger" dismissible>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mt-2 ml-4 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.overtime.update', $overtime->id) }}" method="POST" enctype="multipart/form-data" x-data="overtimeForm()">
        @csrf
        @method('PUT')

        <div class="max-w-3xl mx-auto">
            <x-card title="Informasi Lembur">
                <div class="space-y-5">
                    {{-- Current Status Badge --}}
                    <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Status Saat Ini:</span>
                        </div>
                        <x-badge :variant="$overtime->status == 'Approved' ? 'success' : ($overtime->status == 'Rejected' ? 'danger' : 'warning')" size="lg">
                            {{ $overtime->status }}
                        </x-badge>
                    </div>

                    {{-- Pegawai --}}
                    <div>
                        <label for="worker_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Pegawai <span class="text-red-500">*</span>
                        </label>
                        <select name="worker_id" id="worker_id" required @change="fetchShiftTime()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Pegawai</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('worker_id', $overtime->worker_id) == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }} - {{ $worker->department->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('worker_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500" x-show="shiftInfo" x-cloak>
                            <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                            Shift: <span x-text="shiftInfo"></span>
                        </p>
                    </div>

                    {{-- Tanggal Lembur --}}
                    <x-form.input
                        name="overtime_date"
                        label="Tanggal Lembur"
                        type="date"
                        x-model="overtimeDate"
                        @change="fetchShiftTime()"
                        :value="old('overtime_date', $overtime->overtime_date->format('Y-m-d'))"
                        required />

                    {{-- Waktu --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input
                            name="start_time"
                            label="Waktu Mulai"
                            type="time"
                            x-model="startTime"
                            @change="calculateHours"
                            :value="old('start_time', $overtime->start_time)"
                            required />

                        <x-form.input
                            name="end_time"
                            label="Waktu Selesai"
                            type="time"
                            x-model="endTime"
                            @change="calculateHours"
                            :value="old('end_time', $overtime->end_time)"
                            required />
                    </div>

                    {{-- Total Jam (Auto Calculate) --}}
                    <x-form.input
                        name="total_hours"
                        label="Total Jam Lembur"
                        type="number"
                        step="0.5"
                        x-model="totalHours"
                        readonly
                        :value="old('total_hours', $overtime->total_hours)"
                        help="Akan dihitung otomatis berdasarkan waktu mulai dan selesai" />

                    {{-- Keterangan --}}
                    <x-form.textarea
                        name="reason"
                        label="Keterangan/Alasan Lembur"
                        rows="4"
                        placeholder="Jelaskan alasan/kegiatan lembur..."
                        :value="old('reason', $overtime->reason)"
                        required />

                    {{-- Lampiran --}}
                    <x-form.file
                        name="attachment"
                        label="Lampiran (Opsional)"
                        accept=".pdf,.jpg,.jpeg,.png"
                        help="PDF, JPG, PNG hingga 2MB. Kosongkan jika tidak ingin mengubah"
                        :currentFile="$overtime->attachment ? asset('storage/' . $overtime->attachment) : null"
                        preview />
                </div>
            </x-card>

            {{-- Action Buttons --}}
            <x-card class="mt-6">
                <div class="flex items-center justify-between">
                    <x-button
                        type="button"
                        variant="outline"
                        icon="fas fa-times"
                        onclick="window.location.href='{{ route('admin.overtime.show', $overtime->id) }}'">
                        Batal
                    </x-button>

                    <div class="flex gap-3">
                        <x-button
                            type="reset"
                            variant="secondary"
                            icon="fas fa-redo">
                            Reset
                        </x-button>

                        <x-button
                            type="submit"
                            variant="primary"
                            icon="fas fa-save">
                            Update Lembur
                        </x-button>
                    </div>
                </div>
            </x-card>
        </div>
    </form>
</div>

@push('scripts')
<script>
function overtimeForm() {
    return {
        startTime: '{{ old('start_time', $overtime->start_time) }}',
        endTime: '{{ old('end_time', $overtime->end_time) }}',
        totalHours: {{ old('total_hours', $overtime->total_hours) }},
        overtimeDate: '{{ old('overtime_date', $overtime->overtime_date->format('Y-m-d')) }}',
        shiftInfo: '',

        async fetchShiftTime() {
            const workerId = document.getElementById('worker_id').value;
            if (!workerId || !this.overtimeDate) {
                this.shiftInfo = '';
                return;
            }

            try {
                const response = await fetch(`/api/workers/${workerId}/shift-time?date=${this.overtimeDate}`);
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

                // Handle overnight shifts
                if (diff < 0) {
                    diff += 24;
                }

                this.totalHours = Math.round(diff * 2) / 2; // Round to nearest 0.5
            }
        }
    }
}
</script>
@endpush
@endsection
