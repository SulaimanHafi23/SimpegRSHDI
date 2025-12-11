@extends('layouts.workers')

@section('title', 'Ajukan Cuti')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Ajukan Cuti</h1>
        <p class="mt-2 text-sm text-gray-600">Isi formulir di bawah ini untuk mengajukan cuti</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('workers.leaves.store') }}" method="POST" enctype="multipart/form-data" id="leaveForm">
            @csrf

            <div class="space-y-6">
                <!-- Leave Type -->
                <div>
                    <label for="leave_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Cuti <span class="text-red-500">*</span>
                    </label>
                    <select name="leave_type" id="leave_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Pilih Jenis Cuti</option>
                        <option value="Cuti Tahunan" {{ old('leave_type') == 'Cuti Tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                        <option value="Cuti Sakit" {{ old('leave_type') == 'Cuti Sakit' ? 'selected' : '' }}>Cuti Sakit</option>
                        <option value="Cuti Melahirkan" {{ old('leave_type') == 'Cuti Melahirkan' ? 'selected' : '' }}>Cuti Melahirkan</option>
                        <option value="Cuti Menikah" {{ old('leave_type') == 'Cuti Menikah' ? 'selected' : '' }}>Cuti Menikah</option>
                        <option value="Cuti Penting" {{ old('leave_type') == 'Cuti Penting' ? 'selected' : '' }}>Cuti Penting</option>
                    </select>
                    @error('leave_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date" id="start_date" 
                               value="{{ old('start_date') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               onchange="calculateDays()">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="end_date" id="end_date" 
                               value="{{ old('end_date') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               onchange="calculateDays()">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Total Days (Auto Calculate) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Total Hari Cuti
                    </label>
                    <div class="flex items-center">
                        <input type="number" name="total_days" id="total_days" 
                               value="{{ old('total_days', 0) }}" readonly
                               class="w-32 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        <span class="ml-3 text-sm text-gray-600">hari</span>
                    </div>
                    @error('total_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Alasan Cuti <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              placeholder="Jelaskan alasan pengajuan cuti Anda...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attachment -->
                <div>
                    <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">
                        Lampiran (Opsional)
                    </label>
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Format: PDF, JPG, PNG (Max: 2MB). Lampirkan surat dokter untuk cuti sakit.</p>
                    @error('attachment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Emergency Contact -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-1">
                            Kontak Darurat
                        </label>
                        <input type="text" name="emergency_contact" id="emergency_contact" 
                               value="{{ old('emergency_contact') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Nama kontak darurat">
                        @error('emergency_contact')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="emergency_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            No. HP Darurat
                        </label>
                        <input type="tel" name="emergency_phone" id="emergency_phone" 
                               value="{{ old('emergency_phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="08xxxxxxxxxx">
                        @error('emergency_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Informasi Penting:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Pengajuan cuti harus diajukan minimal 3 hari sebelum tanggal cuti</li>
                                <li>Cuti tahunan maksimal 12 hari per tahun</li>
                                <li>Untuk cuti sakit, wajib melampirkan surat dokter</li>
                                <li>Pengajuan akan diproses maksimal 2 hari kerja</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('workers.leaves.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <div class="flex space-x-3">
                    <button type="button" onclick="this.form.reset(); document.getElementById('total_days').value = 0"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Ajukan Cuti
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function calculateDays() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (end >= start) {
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('total_days').value = diffDays;
        } else {
            document.getElementById('total_days').value = 0;
            alert('Tanggal selesai harus lebih besar atau sama dengan tanggal mulai');
        }
    }
}
</script>
@endsection
