@extends('layouts.admin')

@section('title', 'Input Lembur')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Form Input Lembur" 
        description="Lengkapi form di bawah untuk input data lembur"
        icon="fas fa-clock">
        <x-slot:actions>
            <x-button 
                variant="secondary" 
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.overtime.index') }}'">
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
    <form action="{{ route('admin.overtime.store') }}" method="POST" enctype="multipart/form-data" x-data="overtimeForm()">
        @csrf

        <div class="max-w-3xl mx-auto">
            <x-card title="Informasi Lembur">
                <div class="space-y-5">
                    {{-- Pegawai --}}
                    <x-form.select 
                        name="worker_id" 
                        label="Pegawai"
                        required
                        placeholder="Pilih Pegawai"
                        :selected="old('worker_id')">
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}">
                                {{ $worker->name }} - {{ $worker->position->name ?? '' }}
                            </option>
                        @endforeach
                    </x-form.select>

                    {{-- Tanggal Lembur --}}
                    <x-form.input 
                        name="overtime_date" 
                        label="Tanggal Lembur" 
                        type="date"
                        :value="old('overtime_date')"
                        required />

                    {{-- Waktu --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input 
                            name="start_time" 
                            label="Waktu Mulai" 
                            type="time"
                            x-model="startTime"
                            @change="calculateHours"
                            :value="old('start_time')"
                            required />

                        <x-form.input 
                            name="end_time" 
                            label="Waktu Selesai" 
                            type="time"
                            x-model="endTime"
                            @change="calculateHours"
                            :value="old('end_time')"
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
                        :value="old('total_hours')"
                        help="Akan dihitung otomatis berdasarkan waktu mulai dan selesai" />

                    {{-- Keterangan --}}
                    <x-form.textarea 
                        name="reason" 
                        label="Keterangan/Alasan Lembur" 
                        rows="4"
                        placeholder="Jelaskan alasan/kegiatan lembur..."
                        :value="old('reason')"
                        required />

                    {{-- Lampiran --}}
                    <x-form.file 
                        name="attachment" 
                        label="Lampiran (Opsional)" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        help="PDF, JPG, PNG hingga 2MB"
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
                        onclick="window.location.href='{{ route('admin.overtime.index') }}'">
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
                            variant="success" 
                            icon="fas fa-save">
                            Simpan Lembur
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
        startTime: '',
        endTime: '',
        totalHours: 0,
        
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
