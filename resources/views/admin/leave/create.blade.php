@extends('layouts.admin')

@section('title', 'Ajukan Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Form Pengajuan Cuti" 
        description="Lengkapi form di bawah untuk mengajukan cuti"
        icon="fas fa-calendar-plus">
        <x-slot:actions>
            <x-button 
                variant="secondary" 
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.leave.index') }}'">
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
    <form action="{{ route('admin.leave.store') }}" method="POST" enctype="multipart/form-data" x-data="leaveForm()">
        @csrf

        <div class="max-w-3xl mx-auto">
            <x-card title="Informasi Pengajuan Cuti">
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

                    {{-- Jenis Cuti --}}
                    <x-form.select 
                        name="leave_type" 
                        label="Jenis Cuti"
                        required
                        placeholder="Pilih Jenis Cuti"
                        :selected="old('leave_type')">
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </x-form.select>

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input 
                            name="start_date" 
                            label="Tanggal Mulai" 
                            type="date"
                            x-model="startDate"
                            @change="calculateDays"
                            :value="old('start_date')"
                            required />

                        <x-form.input 
                            name="end_date" 
                            label="Tanggal Selesai" 
                            type="date"
                            x-model="endDate"
                            @change="calculateDays"
                            :value="old('end_date')"
                            required />
                    </div>

                    {{-- Total Hari (Auto Calculate) --}}
                    <x-form.input 
                        name="total_days" 
                        label="Total Hari" 
                        type="number"
                        x-model="totalDays"
                        readonly
                        :value="old('total_days')"
                        help="Akan dihitung otomatis berdasarkan tanggal mulai dan selesai" />

                    {{-- Alasan --}}
                    <x-form.textarea 
                        name="reason" 
                        label="Alasan Cuti" 
                        rows="4"
                        placeholder="Jelaskan alasan pengajuan cuti..."
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
                        onclick="window.location.href='{{ route('admin.leave.index') }}'">
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
                            icon="fas fa-paper-plane">
                            Ajukan Cuti
                        </x-button>
                    </div>
                </div>
            </x-card>
        </div>
    </form>
</div>

@push('scripts')
<script>
function leaveForm() {
    return {
        startDate: '',
        endDate: '',
        totalDays: 0,
        
        calculateDays() {
            if (this.startDate && this.endDate) {
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                this.totalDays = diffDays > 0 ? diffDays : 0;
            }
        }
    }
}
</script>
@endpush
@endsection
