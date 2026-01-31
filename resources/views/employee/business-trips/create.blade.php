@extends('layouts.employee')

@section('title', 'Ajukan Perjalanan Dinas')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Ajukan Perjalanan Dinas</h1>
                <p class="text-gray-600 mt-1">Isi formulir untuk mengajukan perjalanan dinas baru</p>
            </div>
            <a href="{{ route('employee.business-trips.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
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

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="font-bold">Terjadi kesalahan:</div>
            <ul class="list-disc list-inside mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Info Banner -->
    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Informasi Penting:</h3>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                    <li>Perjalanan dinas harus diajukan minimal 3 hari sebelum keberangkatan</li>
                    <li>Estimasi biaya harus realistis dan sesuai dengan standar</li>
                    <li>Semua perjalanan dinas memerlukan persetujuan</li>
                    <!-- <li>Dokumen pendukung dapat diupload setelah pengajuan disetujui</li> -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.business-trips.store') }}" method="POST">
            @csrf
            <!-- Basic Information Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                    Informasi Dasar
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Destination -->
                    <div class="md:col-span-2">
                        <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-pin text-green-600 mr-1"></i>
                            Tujuan Perjalanan
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="destination" 
                               id="destination"
                               value="{{ old('destination') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('destination') border-red-500 @enderror"
                               placeholder="Contoh: Jakarta, Surabaya, Bandung"
                               required>
                        @error('destination')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day text-green-600 mr-1"></i>
                            Tanggal Keberangkatan
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date"
                               value="{{ old('start_date') }}"
                               min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_date') border-red-500 @enderror"
                               required>
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Minimal 3 hari dari hari ini</p>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-check text-green-600 mr-1"></i>
                            Tanggal Kembali
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date"
                               value="{{ old('end_date') }}"
                               min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_date') border-red-500 @enderror"
                               required>
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Transportation & Accommodation Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-plane text-blue-600 mr-2"></i>
                    Transportasi & Akomodasi
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Transportation -->
                    <div>
                        <label for="transportation" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-car text-green-600 mr-1"></i>
                            Jenis Transportasi
                            <span class="text-red-500">*</span>
                        </label>
                        <select name="transportation" 
                                id="transportation"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('transportation') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Transportasi</option>
                            <option value="Pesawat" {{ old('transportation') == 'Pesawat' ? 'selected' : '' }}>✈️ Pesawat</option>
                            <option value="Kereta Api" {{ old('transportation') == 'Kereta Api' ? 'selected' : '' }}>🚄 Kereta Api</option>
                            <option value="Bus" {{ old('transportation') == 'Bus' ? 'selected' : '' }}>🚌 Bus</option>
                            <option value="Mobil Dinas" {{ old('transportation') == 'Mobil Dinas' ? 'selected' : '' }}>🚗 Mobil Dinas</option>
                            <option value="Mobil Pribadi" {{ old('transportation') == 'Mobil Pribadi' ? 'selected' : '' }}>🚙 Mobil Pribadi</option>
                            <option value="Lainnya" {{ old('transportation') == 'Lainnya' ? 'selected' : '' }}>🚚 Lainnya</option>
                        </select>
                        @error('transportation')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Accommodation -->
                    <div>
                        <label for="accommodation" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-hotel text-green-600 mr-1"></i>
                            Akomodasi
                        </label>
                        <select name="accommodation" 
                                id="accommodation"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('accommodation') border-red-500 @enderror">
                            <option value="">Pilih Akomodasi (Opsional)</option>
                            <option value="Hotel" {{ old('accommodation') == 'Hotel' ? 'selected' : '' }}>🏨 Hotel</option>
                            <option value="Guest House" {{ old('accommodation') == 'Guest House' ? 'selected' : '' }}>🏠 Guest House</option>
                            <option value="Wisma" {{ old('accommodation') == 'Wisma' ? 'selected' : '' }}>🏡 Wisma</option>
                            <option value="Tidak Menginap" {{ old('accommodation') == 'Tidak Menginap' ? 'selected' : '' }}>🏃 Tidak Menginap</option>
                            <option value="Lainnya" {{ old('accommodation') == 'Lainnya' ? 'selected' : '' }}>🏢 Lainnya</option>
                        </select>
                        @error('accommodation')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimated Cost -->
                    <div class="md:col-span-2">
                        <label for="estimated_cost" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calculator text-green-600 mr-1"></i>
                            Estimasi Total Biaya
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-lg">Rp</span>
                            </div>
                            <input type="number" 
                                   name="estimated_cost" 
                                   id="estimated_cost"
                                   value="{{ old('estimated_cost') }}"
                                   class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('estimated_cost') border-red-500 @enderror"
                                   placeholder="0"
                                   min="0"
                                   step="1000"
                                   required>
                        </div>
                        @error('estimated_cost')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Termasuk transportasi, akomodasi, dan konsumsi</p>
                    </div>
                </div>
            </div>

            <!-- Trip Details Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                    Detail Perjalanan
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Purpose -->
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-bullseye text-green-600 mr-1"></i>
                            Tujuan/Keperluan Perjalanan
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea name="purpose" 
                                  id="purpose"
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('purpose') border-red-500 @enderror"
                                  placeholder="Jelaskan secara detail tujuan perjalanan dinas, agenda yang akan dilaksanakan, atau keperluan lainnya..."
                                  required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 flex items-center text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Minimal 50 karakter, jelaskan dengan jelas dan detail
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-green-600 mr-1"></i>
                            Catatan Tambahan
                            <span class="text-gray-500 text-sm">(Opsional)</span>
                        </label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                                  placeholder="Catatan khusus, permintaan akomodasi, atau informasi tambahan lainnya...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-end sm:space-x-4 space-y-3 sm:space-y-0 pt-6 border-t border-gray-200">
                <a href="{{ route('employee.business-trips.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
                
                <button type="submit" 
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Ajukan Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const costInput = document.getElementById('estimated_cost');
    
    // Set minimum date validation
    const today = new Date();
    const minDate = new Date(today.setDate(today.getDate() + 3));
    const minDateString = minDate.toISOString().split('T')[0];
    
    startDateInput.min = minDateString;
    endDateInput.min = minDateString;
    
    // Start date change handler
    startDateInput.addEventListener('change', function() {
        const startDate = new Date(this.value);
        if (startDate) {
            // Set end date minimum to start date
            endDateInput.min = this.value;
            
            // Clear end date if it's before start date
            if (endDateInput.value && new Date(endDateInput.value) < startDate) {
                endDateInput.value = '';
            }
        }
    });
    
    // End date change handler
    endDateInput.addEventListener('change', function() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(this.value);
        
        if (startDate && endDate && endDate < startDate) {
            alert('Tanggal kembali tidak boleh sebelum tanggal keberangkatan');
            this.value = '';
        }
    });
    
    // Cost input formatting
    costInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value) {
            this.value = parseInt(value);
        }
    });
    
    // Form submission validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const purpose = document.getElementById('purpose').value.trim();
        
        if (purpose.length < 50) {
            e.preventDefault();
            alert('Tujuan perjalanan minimal 50 karakter. Saat ini: ' + purpose.length + ' karakter');
            document.getElementById('purpose').focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = document.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        submitBtn.disabled = true;
        
        // Re-enable button after 3 seconds (in case of validation errors)
        setTimeout(function() {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });
});
</script>
@endsection
