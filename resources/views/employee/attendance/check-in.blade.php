@extends('layouts.employee')

@section('title', 'Check In')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

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
        <!-- Instructions -->
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Cara Check In:</h3>
                    <ol class="text-sm text-gray-700 space-y-1 list-decimal list-inside">
                        <li>Pilih lokasi absensi dari dropdown</li>
                        <li>Klik tombol "Dapatkan Lokasi" untuk mendapatkan koordinat GPS Anda</li>
                        <li>Pastikan Anda berada dalam radius lokasi yang dipilih</li>
                        <li>Upload foto (opsional) dan tambahkan catatan jika diperlukan</li>
                        <li>Klik "Check In Sekarang" untuk menyelesaikan</li>
                    </ol>
                </div>
            </div>
        </div>

        <form id="checkin-form" action="{{ route('employee.attendance.check-in') }}" method="POST" enctype="multipart/form-data">
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
                        <option value="{{ $location->id }}" 
                                data-lat="{{ $location->latitude }}" 
                                data-lng="{{ $location->longitude }}" 
                                data-radius="{{ $location->radius }}"
                                {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Map Container -->
            <div id="map" class="w-full h-64 rounded-lg border border-gray-300 mb-4 z-0"></div>

            <!-- Photo -->
            <div class="mb-4">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
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
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700">
                        <i class="fas fa-map-marked-alt text-blue-500 mr-1"></i>
                        Koordinat GPS <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                onclick="getLocation()" 
                                class="text-xs px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded transition shadow-sm">
                            <i class="fas fa-crosshairs mr-1"></i>Dapatkan Lokasi
                        </button>
                        <button type="button" 
                                onclick="openPickOnMap()" 
                                class="text-xs px-3 py-1.5 bg-white border border-gray-300 rounded hover:bg-gray-50 transition shadow-sm">
                            <i class="fas fa-map-pin mr-1"></i>Pilih pada Peta
                        </button>
                    </div>
                </div>
                <div id="locationStatus" class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>Klik tombol "Dapatkan Lokasi" setelah memilih lokasi absensi
                </div>
                <div id="accuracyInfo" class="text-xs text-gray-500 mt-1">Akurasi: —</div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                <input type="hidden" name="accuracy" id="accuracy" value="{{ old('accuracy') }}">
                @error('latitude')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit" id="btn-checkin"
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

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
// Update current time every second
setInterval(function() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
}, 1000);

// Config thresholds
const ACC_THRESHOLD = {{ config('attendance.max_accuracy', 300) }}; // meters

// Map Variables
let map, userMarker, officeCircle;

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
            document.getElementById('accuracy').value = accuracy;
            document.getElementById('accuracyInfo').textContent = `Akurasi: ±${Math.round(accuracy)} m`;
            
            statusDiv.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>Lokasi berhasil didapat: ${latitude.toFixed(6)}, ${longitude.toFixed(6)} (±${Math.round(accuracy)}m)`;
            
            updateUserMarker(latitude, longitude, accuracy);

            // Enforce accuracy threshold
            const submit = document.getElementById('btn-checkin');
            if (accuracy && accuracy > ACC_THRESHOLD) {
                submit.disabled = true;
                submit.classList.add('opacity-50','cursor-not-allowed');
                statusDiv.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mr-1"></i>Akurasi buruk: ±${Math.round(accuracy)} m. Silakan gunakan ponsel atau pilih lokasi manual.`;
            } else {
                submit.disabled = false;
                submit.classList.remove('opacity-50','cursor-not-allowed');
            }

            // Check distance from selected location (if any)
            (function(){
                const select = document.getElementById('location_id');
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const lat = parseFloat(selectedOption.dataset.lat);
                    const lng = parseFloat(selectedOption.dataset.lng);
                    const radius = parseFloat(selectedOption.dataset.radius);
                    
                    if (lat && lng && radius) {
                        const distance = computeDistance(parseFloat(latitude), parseFloat(longitude), lat, lng);
                        const info = document.createElement('div');
                        info.className = 'text-xs mt-1';
                        
                        if (distance <= radius) {
                            info.className += ' text-green-600';
                            info.innerHTML = `<i class="fas fa-check-circle mr-1"></i>Jarak dari lokasi: ${Math.round(distance)} m (dalam radius ${Math.round(radius)} m)`;
                        } else {
                            info.className += ' text-orange-600';
                            info.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i>Jarak dari lokasi: ${Math.round(distance)} m (radius ${Math.round(radius)} m)`;
                        }
                        
                        statusDiv.appendChild(info);
                    }
                }
            })();
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

// Initialize Map
function initMap() {
    // Default view (Indonesia)
    map = L.map('map').setView([-2.5489, 118.0149], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
}

// Compute distance between two lat/lng points (Haversine formula)
function computeDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // distance in meters
}

// Update User Marker on Map
function updateUserMarker(lat, lng, accuracy) {
    if (!map) initMap();

    if (userMarker) {
        map.removeLayer(userMarker);
    }

    userMarker = L.marker([lat, lng]).addTo(map)
        .bindPopup(`Lokasi Anda (Akurasi: ±${Math.round(accuracy)}m)`).openPopup();

    // Zoom to user
    map.setView([lat, lng], 16);
}

// Update Office Circle on Map
function updateOfficeCircle(lat, lng, radius) {
    if (!map) initMap();

    if (officeCircle) {
        map.removeLayer(officeCircle);
    }

    officeCircle = L.circle([lat, lng], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);

    // Fit bounds to include office
    if (userMarker) {
        const group = new L.featureGroup([userMarker, officeCircle]);
        map.fitBounds(group.getBounds().pad(0.1));
    } else {
        map.setView([lat, lng], 16);
    }
}

// Open map for manual location picking
function openPickOnMap() {
    if (!map) initMap();
    alert('Klik pada peta untuk memilih lokasi. Klik sekali untuk memilih.');
    map.once('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        // mark as manual pick with accuracy = 0 (admin can review if needed)
        document.getElementById('accuracy').value = 0;
        document.getElementById('accuracyInfo').textContent = 'Akurasi: manual';
        document.getElementById('locationStatus').innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>Lokasi dipilih pada peta: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        updateUserMarker(lat, lng, 0);
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initMap();

    // Listen to location dropdown change to show office circle
    const locationSelect = document.getElementById('location_id');
    if (locationSelect) {
        locationSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const lat = parseFloat(selectedOption.dataset.lat);
                const lng = parseFloat(selectedOption.dataset.lng);
                const radius = parseFloat(selectedOption.dataset.radius);
                
                if (lat && lng && radius) {
                    // Show office location on map
                    updateOfficeCircle(lat, lng, radius);
                    // Center map to office location
                    map.setView([lat, lng], 15);
                }
            }
        });
    }

    // Check-in form validation
    const checkinForm = document.getElementById('checkin-form');
    if (checkinForm) {
        checkinForm.addEventListener('submit', function(e) {
            const acc = parseFloat(document.getElementById('accuracy').value) || null;
            if (acc && acc > ACC_THRESHOLD) {
                e.preventDefault();
                alert('Lokasi tidak cukup akurat (±' + Math.round(acc) + ' m). Silakan gunakan ponsel atau pilih lokasi manual.');
                return false;
            }
        });
    }
});
</script>
@endsection
