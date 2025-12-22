@extends('layouts.employee')

@section('title', 'Check In')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Check In</h1>
        <p class="text-gray-600 mt-1">Catat kehadiran Anda hari ini</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Check In Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.attendance.check-in') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Current Time Display -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg text-center">
                <div class="text-sm text-gray-600 mb-2">Waktu Saat Ini</div>
                <div class="text-3xl font-bold text-blue-600" id="current-time">{{ now()->format('H:i:s') }}</div>
                <div class="text-sm text-gray-600 mt-2">{{ now()->format('l, d F Y') }}</div>
            </div>

            <!-- Location -->
            <div class="mb-4">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Lokasi <span class="text-red-500">*</span>
                </label>
                <select name="location_id" 
                        id="location_id" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('location_id') border-red-500 @enderror">
                    <option value="">Pilih Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Photo -->
            <div class="mb-4">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                    Foto (Opsional)
                </label>
                <input type="file" 
                       name="photo" 
                       id="photo" 
                       accept="image/jpeg,image/jpg,image/png"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('photo') border-red-500 @enderror">
                @error('photo')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Format: JPG, JPEG, PNG. Maksimal 2MB</p>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                          placeholder="Tambahkan catatan jika diperlukan">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Geolocation Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Lokasi GPS</label>
                    <button type="button" 
                            onclick="getLocation()" 
                            class="text-xs px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded transition">
                        <i class="fas fa-map-marker-alt mr-1"></i>Dapatkan Lokasi
                    </button>
                </div>
                <div id="locationStatus" class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>Klik tombol untuk mendapatkan lokasi Anda
                </div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                @error('latitude')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Check In Sekarang
                </button>
                <a href="{{ route('employee.attendance.index') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Update current time every second
setInterval(function() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
}, 1000);

// Get geolocation
function getLocation(useFallback = false) {
    const statusDiv = document.getElementById('locationStatus');
    
    if (!navigator.geolocation) {
        statusDiv.innerHTML = '<i class="fas fa-times-circle text-red-500 mr-1"></i>Browser Anda tidak mendukung geolocation';
        return;
    }
    
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mendapatkan lokasi' + (useFallback ? ' (mode cepat)...' : '...');
    
    const options = useFallback ? {
        enableHighAccuracy: false,
        timeout: 15000,
        maximumAge: 60000
    } : {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 0
    };
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            const accuracy = position.coords.accuracy;
            
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
            
            statusDiv.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>Lokasi berhasil didapat: ${latitude.toFixed(6)}, ${longitude.toFixed(6)} (±${Math.round(accuracy)}m)`;
        },
        function(error) {
            let errorMsg = 'Gagal mendapatkan lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Izin akses lokasi ditolak. Mohon aktifkan GPS dan izinkan akses lokasi di browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                    break;
                case error.TIMEOUT:
                    if (!useFallback) {
                        errorMsg = 'Timeout. Mencoba dengan mode cepat...';
                        statusDiv.innerHTML = `<i class="fas fa-spinner fa-spin text-yellow-500 mr-1"></i>${errorMsg}`;
                        // Retry with fallback (low accuracy, faster)
                        setTimeout(() => getLocation(true), 1000);
                        return;
                    } else {
                        errorMsg = 'Timeout mendapatkan lokasi. Coba lagi atau pindah ke area dengan sinyal lebih baik.';
                    }
                    break;
            }
            statusDiv.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mr-1"></i>${errorMsg}`;
            
            // Add retry button
            if (error.code === error.TIMEOUT || error.code === error.POSITION_UNAVAILABLE) {
                statusDiv.innerHTML += ` <button type="button" onclick="getLocation()" class="text-xs underline text-blue-600 hover:text-blue-700">Coba Lagi</button>`;
            }
        },
        options
    );
}

// Auto get location on page load
document.addEventListener('DOMContentLoaded', function() {
    getLocation();
});
</script>
@endsection
