@extends('layouts.employee')

@section('title', 'Buat Permintaan Tukar Shift')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
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

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
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
                            <li>Minimal 48 jam sebelum shift dimulai (72 jam untuk IGD/ICU/Satpam)</li>
                            <li>Minimal 12 jam istirahat antara shift</li>
                            <li>Tidak boleh double shift dalam satu hari</li>
                            <li><strong>Satu departemen:</strong> Tidak perlu persetujuan manager</li>
                            <li><strong>Beda departemen:</strong> Memerlukan persetujuan manager</li>
                        </ul>
                    </div>
                </div>
            </div>

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
                            {{ $ws->effective_from->format('d M Y') }} - 
                            {{ $ws->shift?->name ?? 'N/A' }} 
                            ({{ $ws->shift?->start_time ?? '' }} - {{ $ws->shift?->end_time ?? '' }})
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
                        <option value="{{ $w->id }}" {{ old('target_worker_id') == $w->id ? 'selected' : '' }}>
                            {{ $w->name }} - {{ $w->department->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Open request berarti siapa saja bisa menerima</p>
                @error('target_worker_id')
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
                    <option value="">-- Pilih Shift Target --</option>
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
        </form>
    </div>
</div>

<script>
// Load target shifts when target worker is selected
document.getElementById('target_worker_id').addEventListener('change', function() {
    const workerId = this.value;
    const targetShiftSelect = document.getElementById('target_shift_id');
    
    // Clear existing options
    targetShiftSelect.innerHTML = '<option value="">-- Pilih Shift Target --</option>';
    
    if (!workerId) return;
    
    // Fetch shifts for selected worker
    fetch(`/api/workers/${workerId}/future-shifts`)
        .then(response => response.json())
        .then(data => {
            data.forEach(shift => {
                const option = document.createElement('option');
                option.value = shift.id;
                option.textContent = `${shift.date} - ${shift.shift_name} (${shift.start_time} - ${shift.end_time})`;
                targetShiftSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading shifts:', error);
        });
});
</script>
@endsection
