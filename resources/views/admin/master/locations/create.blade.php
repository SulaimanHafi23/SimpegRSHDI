@extends('layouts.admin')

@section('title', 'Tambah Lokasi')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center space-x-3">
        <x-button 
            variant="secondary" 
            size="sm"
            icon="fas fa-arrow-left"
            onclick="window.location.href='{{ route('admin.master.locations.index') }}'">
        </x-button>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Tambah Lokasi</h1>
            <p class="text-sm text-gray-600 mt-1">Tambah lokasi kerja baru</p>
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

    <form action="{{ route('admin.master.locations.store') }}" method="POST" class="space-y-6">
        @csrf
        
        {{-- Basic Information --}}
        <x-card title="Informasi Dasar">
            <div class="space-y-4">
                <x-form.input 
                    name="name" 
                    label="Nama Lokasi" 
                    :value="old('name')"
                    required 
                    :error="$errors->first('name')"
                    placeholder="Contoh: Kantor Pusat, Cabang Jakarta" />

                <x-form.textarea 
                    name="address" 
                    label="Alamat Lengkap" 
                    rows="3" 
                    :value="old('address')"
                    :error="$errors->first('address')"
                    placeholder="Masukkan alamat lengkap lokasi" />
            </div>
        </x-card>

        {{-- GPS Coordinates --}}
        <x-card title="Koordinat GPS">
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="location-coordinates">
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
                </div>

                <div>
                    <div id="location-map" class="w-full h-96 rounded-lg border border-gray-200"></div>
                    <p class="text-xs text-gray-500 mt-2" id="map-location-status">
                        Klik pada peta untuk menentukan titik lokasi.
                    </p>
                </div>

                <button type="button" id="get-location-btn" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2">
                    <i class="fas fa-location-arrow"></i>
                    <span>Dapatkan Lokasi Saya</span>
                </button>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Klik tombol di atas untuk otomatis mendapatkan koordinat GPS dari lokasi Anda saat ini.
                    </p>
                </div>
            </div>
        </x-card>

        {{-- Geofence Settings --}}
        <x-card title="Pengaturan Geofence">
            <div class="space-y-4">
                <x-form.input 
                    name="radius" 
                    label="Radius (meter)" 
                    type="number"
                    :value="old('radius', '100')"
                    required 
                    :error="$errors->first('radius')"
                    help="Jarak maksimal yang diperbolehkan untuk absensi dari titik koordinat (dalam meter)" />

                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                           name="enforce_geofence" 
                           id="enforce_geofence" 
                           value="1"
                           {{ old('enforce_geofence', true) ? 'checked' : '' }}
                           class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <div class="flex-1">
                        <label for="enforce_geofence" class="text-sm font-medium text-gray-700">
                            Paksa Geofence
                        </label>
                        <p class="text-xs text-gray-500 mt-1">
                            Jika diaktifkan, pegawai hanya bisa absen jika berada dalam radius yang ditentukan.
                        </p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <div class="flex-1">
                        <label for="is_active" class="text-sm font-medium text-gray-700">
                            Status Aktif
                        </label>
                        <p class="text-xs text-gray-500 mt-1">
                            Lokasi aktif dapat digunakan untuk absensi pegawai.
                        </p>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                <x-button 
                    variant="secondary"
                    onclick="window.location.href='{{ route('admin.master.locations.index') }}'">
                    Batal
                </x-button>
                <x-button 
                    variant="success" 
                    icon="fas fa-save"
                    type="submit">
                    Simpan
                </x-button>
            </div>
        </x-card>
    </form>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.querySelector('#location-coordinates input[name="latitude"]');
        const lngInput = document.querySelector('#location-coordinates input[name="longitude"]');
        const radiusInput = document.querySelector('input[name="radius"]');
        const getLocationBtn = document.getElementById('get-location-btn');
        const mapStatus = document.getElementById('map-location-status');
        let map;
        let marker;
        let radiusCircle;

        function getCoordinateValue(inputElement) {
            if (!inputElement) return null;
            const parsed = parseFloat(inputElement.value);
            return Number.isFinite(parsed) ? parsed : null;
        }

        function getRadiusValue() {
            if (!radiusInput) return 0;
            const parsed = parseFloat(radiusInput.value);
            if (!Number.isFinite(parsed) || parsed <= 0) {
                return 0;
            }
            return parsed;
        }

        function updateStatus(message, isSuccess = true) {
            if (!mapStatus) return;
            mapStatus.textContent = message;
            mapStatus.className = isSuccess
                ? 'text-xs text-green-600 mt-2'
                : 'text-xs text-gray-500 mt-2';
        }

        function setMarkerAndInputs(latitude, longitude, shouldPan = true) {
            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                return;
            }

            latInput.value = latitude;
            lngInput.value = longitude;

            if (!marker) {
                marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
                marker.on('dragend', function(event) {
                    const newPosition = event.target.getLatLng();
                    setMarkerAndInputs(newPosition.lat, newPosition.lng, false);
                    updateStatus('Titik diperbarui dari marker (drag).');
                });
            } else {
                marker.setLatLng([latitude, longitude]);
            }

            if (shouldPan) {
                map.setView([latitude, longitude], 16);
            }

            updateRadiusPreview(latitude, longitude);
        }

        function updateRadiusPreview(latitude, longitude) {
            if (!map || !Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                return;
            }

            const radius = getRadiusValue();

            if (radiusCircle) {
                radiusCircle.remove();
                radiusCircle = null;
            }

            if (radius > 0) {
                radiusCircle = L.circle([latitude, longitude], {
                    radius: radius,
                    color: '#3B82F6',
                    fillColor: '#93C5FD',
                    fillOpacity: 0.25,
                    weight: 2
                }).addTo(map);
            }
        }

        function initializeMap() {
            const defaultCenter = [-2.5489, 118.0149];
            map = L.map('location-map').setView(defaultCenter, 5);

            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community',
                maxZoom: 19
            });

            const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            });

            satelliteLayer.addTo(map);
            L.control.layers(
                {
                    'Satelit': satelliteLayer,
                    'Peta Jalan': streetLayer,
                },
                {},
                { collapsed: false }
            ).addTo(map);

            const initialLat = getCoordinateValue(latInput);
            const initialLng = getCoordinateValue(lngInput);

            if (initialLat !== null && initialLng !== null && !(initialLat === 0 && initialLng === 0)) {
                setMarkerAndInputs(initialLat, initialLng, true);
                updateStatus('Titik awal diambil dari nilai koordinat form.');
            }

            map.on('click', function(event) {
                setMarkerAndInputs(event.latlng.lat, event.latlng.lng, false);
                updateStatus('Titik lokasi dipilih dari peta.');
            });
        }

        if (latInput && lngInput) {
            latInput.addEventListener('change', function() {
                const latitude = getCoordinateValue(latInput);
                const longitude = getCoordinateValue(lngInput);

                if (latitude !== null && longitude !== null) {
                    setMarkerAndInputs(latitude, longitude, true);
                    updateStatus('Titik diperbarui dari input koordinat.');
                }
            });

            lngInput.addEventListener('change', function() {
                const latitude = getCoordinateValue(latInput);
                const longitude = getCoordinateValue(lngInput);

                if (latitude !== null && longitude !== null) {
                    setMarkerAndInputs(latitude, longitude, true);
                    updateStatus('Titik diperbarui dari input koordinat.');
                }
            });
        }

        if (radiusInput) {
            const onRadiusChange = function() {
                const latitude = getCoordinateValue(latInput);
                const longitude = getCoordinateValue(lngInput);

                if (latitude !== null && longitude !== null) {
                    updateRadiusPreview(latitude, longitude);
                    updateStatus('Preview radius geofence diperbarui.');
                }
            };

            radiusInput.addEventListener('input', onRadiusChange);
            radiusInput.addEventListener('change', onRadiusChange);
        }

        initializeMap();
        
        if (getLocationBtn && navigator.geolocation) {
            getLocationBtn.addEventListener('click', function() {
                // Disable button and show loading
                getLocationBtn.disabled = true;
                getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span class="ml-2">Mencari lokasi...</span>';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Success - set coordinates
                        setMarkerAndInputs(position.coords.latitude, position.coords.longitude, true);
                        updateStatus('Lokasi saat ini berhasil didapatkan.');
                        
                        // Update button to success state
                        getLocationBtn.disabled = false;
                        getLocationBtn.className = 'w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors flex items-center justify-center space-x-2';
                        getLocationBtn.innerHTML = '<i class="fas fa-check"></i><span class="ml-2">Lokasi Berhasil Didapat!</span>';
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            getLocationBtn.className = 'w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2';
                            getLocationBtn.innerHTML = '<i class="fas fa-location-arrow"></i><span class="ml-2">Dapatkan Lokasi Saya</span>';
                        }, 2000);
                    },
                    function(error) {
                        // Error handling
                        let errorMessage = 'Tidak dapat mengakses lokasi';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Akses lokasi ditolak. Mohon izinkan akses lokasi di browser Anda.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Informasi lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Permintaan lokasi timeout.';
                                break;
                        }
                        
                        alert(errorMessage);
                        
                        // Reset button
                        getLocationBtn.disabled = false;
                        getLocationBtn.className = 'w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2';
                        getLocationBtn.innerHTML = '<i class="fas fa-location-arrow"></i><span class="ml-2">Dapatkan Lokasi Saya</span>';
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }
    });
</script>
@endpush
@endsection
