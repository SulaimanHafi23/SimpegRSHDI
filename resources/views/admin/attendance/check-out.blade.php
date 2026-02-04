@extends('layouts.admin')

@section('title', 'Check Out')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center space-x-3">
        <x-button 
            variant="secondary" 
            size="sm"
            icon="fas fa-arrow-left"
            onclick="window.location.href='{{ route('admin.attendance.index') }}'">
        </x-button>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Check Out</h1>
            <p class="text-sm text-gray-600 mt-1">Catat waktu pulang pegawai</p>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="font-bold">Terdapat kesalahan pada form!</strong>
                    <ul class="mt-2 ml-4 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informasi Pegawai --}}
        <x-card title="Informasi Absensi">
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Pegawai</label>
                    <p class="text-base font-semibold text-gray-900 mt-1">
                        {{ $attendance->worker->nip }} - {{ $attendance->worker->name }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Waktu Check In</label>
                    <p class="text-base font-semibold text-gray-900 mt-1">
                        {{ $attendance->check_in->format('H:i:s') }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Lokasi Check In</label>
                    <p class="text-base font-semibold text-gray-900 mt-1">
                        {{ $attendance->location ? $attendance->location->name : 'Tidak ada lokasi' }}
                    </p>
                </div>

                @if($attendance->is_late)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Terlambat</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    {{ floor($attendance->late_minutes / 60) > 0 ? floor($attendance->late_minutes / 60) . ' jam ' : '' }}
                                    {{ $attendance->late_minutes % 60 }} menit
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($attendance->photo)
                    <div>
                        <label class="text-sm font-medium text-gray-700">Foto Check In</label>
                        <img src="{{ Storage::url($attendance->photo) }}" 
                             alt="Check In Photo" 
                             class="mt-2 rounded-lg max-h-48 object-cover">
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Check Out Form --}}
        <x-card title="Form Check Out">
            <form action="{{ route('admin.attendance.check-out', $attendance->id) }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="space-y-4">
                @csrf
                
                <x-form.select 
                    name="location_id" 
                    label="Lokasi"
                    required 
                    :error="$errors->first('location_id')">
                    <option value="">Pilih Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" 
                                {{ old('location_id', $attendance->location_id) == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.input 
                    name="latitude" 
                    label="Latitude" 
                    type="number"
                    step="any"
                    :value="old('latitude', '0')"
                    required 
                    :error="$errors->first('latitude')"
                    placeholder="Contoh: -6.200000" />

                <x-form.input 
                    name="longitude" 
                    label="Longitude" 
                    type="number"
                    step="any"
                    :value="old('longitude', '0')"
                    required 
                    :error="$errors->first('longitude')"
                    placeholder="Contoh: 106.816666" />

                <x-form.file 
                    name="photo" 
                    label="Foto (Opsional)"
                    accept="image/*"
                    :error="$errors->first('photo')">
                    <x-slot name="help">
                        Format: JPG, JPEG, PNG. Maksimal 2MB
                    </x-slot>
                </x-form.file>

                {{-- Get Location Button --}}
                <div>
                    <x-button 
                        type="button" 
                        variant="secondary" 
                        onclick="getCurrentLocation()"
                        class="w-full">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Ambil Lokasi Saat Ini
                    </x-button>
                </div>

                {{-- Tombol Submit --}}
                <div class="flex gap-3 pt-4">
                    <x-button 
                        type="button" 
                        variant="secondary"
                        onclick="window.location.href='{{ route('admin.attendance.index') }}'"
                        class="flex-1">
                        Batal
                    </x-button>
                    <x-button 
                        type="submit" 
                        variant="primary"
                        class="flex-1">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Check Out
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    {{-- Location Map (Optional) --}}
    <x-card title="Peta Lokasi">
        <div id="map" class="w-full h-96 rounded-lg"></div>
    </x-card>
</div>

@push('scripts')
<script>
    // Location data from controller
    const locationsData = @json($locationsData);
    let map;
    let marker;
    let circles = [];

    // Initialize map
    function initMap() {
        // Default center (Indonesia)
        map = L.map('map').setView([-2.5489, 118.0149], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add location circles
        updateLocationCircles();

        // Try to get current location on load
        getCurrentLocation();
    }

    // Update location circles on map
    function updateLocationCircles() {
        // Clear existing circles
        circles.forEach(circle => circle.remove());
        circles = [];

        // Add circles for all locations
        Object.values(locationsData).forEach(location => {
            const circle = L.circle([location.latitude, location.longitude], {
                color: '#3B82F6',
                fillColor: '#93C5FD',
                fillOpacity: 0.2,
                radius: location.radius
            }).addTo(map);

            circle.bindPopup(`
                <div class="text-center">
                    <strong>${location.name}</strong><br>
                    <small>Radius: ${location.radius}m</small>
                </div>
            `);

            circles.push(circle);
        });
    }

    // Get current location
    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation tidak didukung oleh browser Anda');
            return;
        }

        const button = event ? event.target : null;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengambil lokasi...';
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Update form inputs
                document.querySelector('input[name="latitude"]').value = lat;
                document.querySelector('input[name="longitude"]').value = lng;

                // Update map
                if (marker) {
                    marker.remove();
                }

                marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup('Lokasi Anda').openPopup();
                map.setView([lat, lng], 16);

                // Validate location
                validateLocation(lat, lng);

                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Ambil Lokasi Saat Ini';
                }
            },
            function(error) {
                let errorMessage = 'Gagal mendapatkan lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Izin lokasi ditolak';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Informasi lokasi tidak tersedia';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Waktu permintaan habis';
                        break;
                    default:
                        errorMessage += 'Terjadi kesalahan';
                }
                alert(errorMessage);

                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Ambil Lokasi Saat Ini';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Validate location against selected location's geofence
    function validateLocation(lat, lng) {
        const locationId = document.querySelector('select[name="location_id"]').value;
        if (!locationId) return;

        const location = locationsData[locationId];
        if (!location || !location.enforce_geofence) return;

        const distance = calculateDistance(
            lat, lng,
            location.latitude, location.longitude
        );

        if (distance > location.radius) {
            alert(`Peringatan: Anda berada di luar radius lokasi ${location.name} (${Math.round(distance)}m dari lokasi)`);
        }
    }

    // Calculate distance between two coordinates (Haversine formula)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    // Update marker when coordinates change
    document.querySelector('input[name="latitude"]').addEventListener('change', updateMarkerFromInputs);
    document.querySelector('input[name="longitude"]').addEventListener('change', updateMarkerFromInputs);

    function updateMarkerFromInputs() {
        const lat = parseFloat(document.querySelector('input[name="latitude"]').value);
        const lng = parseFloat(document.querySelector('input[name="longitude"]').value);

        if (isNaN(lat) || isNaN(lng)) return;

        if (marker) {
            marker.remove();
        }

        marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup('Lokasi Anda').openPopup();
        map.setView([lat, lng], 16);

        validateLocation(lat, lng);
    }

    // Initialize map when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });
</script>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush
@endsection
