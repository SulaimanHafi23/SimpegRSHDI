@extends('layouts.admin')

@section('title', 'Check In - ' . $worker->name)

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
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Check In</h1>
            <p class="text-sm text-gray-600 mt-1">Catat waktu masuk pegawai</p>
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
        <x-card title="Informasi Pegawai">
            <div class="space-y-4">
                {{-- Profile Section --}}
                <div class="flex items-center space-x-4">
                    @if($worker->photo)
                        <img src="{{ Storage::url($worker->photo) }}"
                             alt="{{ $worker->name }}"
                             class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">
                                {{ strtoupper(substr($worker->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $worker->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $worker->nip }}</p>
                        @if($worker->department)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                {{ $worker->department->name }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Departemen</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            {{ $worker->department->name ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Status</label>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $worker->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $worker->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Shift Info --}}
                @php
                    $effectiveShift = $shiftInfo['shift'] ?? null;
                    $effectiveSchedule = $shiftInfo['schedule'] ?? null;
                    $shiftSource = $shiftInfo['source'] ?? 'none';
                @endphp
                @if(is_object($effectiveShift) && is_array($effectiveSchedule))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-clock text-blue-600 mt-0.5 mr-3"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-800">Shift Efektif Hari Ini</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-lg font-bold text-blue-900">{{ $effectiveShift->name }}</p>
                                    @if($shiftSource === 'shift_swap')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Tukar Shift</span>
                                    @elseif($shiftSource === 'override')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">Override</span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-4 mt-2 text-sm text-blue-700">
                                    <span>
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        Masuk: {{ \Carbon\Carbon::parse($effectiveSchedule['start_time'])->format('H:i') }}
                                    </span>
                                    <span>
                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                        Pulang: {{ \Carbon\Carbon::parse($effectiveSchedule['end_time'])->format('H:i') }}
                                    </span>
                                </div>
                                @if($shiftSource === 'shift_swap' && !empty($shiftInfo['swap_with_name']))
                                    <p class="text-xs text-purple-700 mt-2">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        Jam ini berasal dari tukar shift dengan {{ $shiftInfo['swap_with_name'] }}.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Tidak ada shift</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Pegawai ini belum memiliki jadwal shift untuk hari ini
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Current Time --}}
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Waktu Saat Ini</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1" id="current-time">--:--:--</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium text-gray-500 uppercase">Tanggal</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ now()->format('l, d F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Check In Form --}}
        <x-card title="Form Check In">
            <form action="{{ route('admin.attendance.check-in', $worker->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf
                <input type="hidden" name="worker_id" value="{{ $worker->id }}">

                <x-form.select
                    name="location_id"
                    label="Lokasi"
                    required
                    :error="$errors->first('location_id')">
                    <option value="">Pilih Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
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
                        class="w-full"
                        id="get-location-btn">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Ambil Lokasi Saat Ini
                    </x-button>
                    <p class="text-xs text-gray-500 mt-2 text-center" id="location-status"></p>
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
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Check In
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    {{-- Location Map --}}
    <x-card title="Peta Lokasi">
        <div id="map" class="w-full h-96 rounded-lg"></div>
    </x-card>
</div>

@push('scripts')
{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Location data from controller
    const locationsData = @json($locationsData);
    let map;
    let marker;
    let circles = [];

    // World Time tracking
    let serverTime = null;
    let lastSyncTime = null;

    // Fetch waktu dari World Time API (online time)
    async function fetchWorldTime() {
        try {
            const response = await fetch('{{ route('api.world-time') }}');
            const data = await response.json();

            if (data.success) {
                serverTime = new Date(data.datetime);
                lastSyncTime = Date.now();
                console.log('Time synced from:', data.source || 'unknown');
                console.log('Server datetime:', data.datetime);
                console.log('Parsed time:', serverTime.toLocaleString('id-ID', { timeZone: 'Asia/Makassar' }));

                if (data.fallback) {
                    console.warn('Using fallback server time');
                }
            }
        } catch (error) {
            console.error('Failed to fetch world time:', error);
            // Fallback to local time if API fails
            serverTime = new Date();
            lastSyncTime = Date.now();
        }
    }

    // Update display waktu (setiap detik)
    function updateCurrentTime() {
        if (serverTime && lastSyncTime) {
            // Calculate time passed since last sync
            const timePassed = Date.now() - lastSyncTime;
            const currentTime = new Date(serverTime.getTime() + timePassed);

            const timeStr = currentTime.toLocaleTimeString('id-ID', {
                timeZone: 'Asia/Makassar',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            document.getElementById('current-time').textContent = timeStr + ' WITA';
        } else {
            document.getElementById('current-time').textContent = 'Memuat...';
        }
    }

    // Initialize time
    fetchWorldTime(); // Initial fetch
    setInterval(updateCurrentTime, 1000); // Update display every second
    setInterval(fetchWorldTime, 30000); // Re-sync every 30 seconds
    updateCurrentTime();

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

        const button = document.getElementById('get-location-btn');
        const statusEl = document.getElementById('location-status');

        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengambil lokasi...';
        }
        if (statusEl) {
            statusEl.textContent = 'Mengambil lokasi GPS...';
            statusEl.className = 'text-xs text-blue-600 mt-2 text-center';
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Update form inputs
                document.querySelector('input[name="latitude"]').value = lat;
                document.querySelector('input[name="longitude"]').value = lng;

                // Update map
                if (marker) {
                    marker.remove();
                }

                marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`Lokasi Anda<br><small>Akurasi: ±${Math.round(accuracy)}m</small>`).openPopup();
                map.setView([lat, lng], 16);

                // Validate location
                validateLocation(lat, lng);

                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Ambil Lokasi Saat Ini';
                }
                if (statusEl) {
                    statusEl.textContent = `Lokasi ditemukan (Akurasi: ±${Math.round(accuracy)}m)`;
                    statusEl.className = 'text-xs text-green-600 mt-2 text-center';
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

                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Ambil Lokasi Saat Ini';
                }
                if (statusEl) {
                    statusEl.textContent = errorMessage;
                    statusEl.className = 'text-xs text-red-600 mt-2 text-center';
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

        const statusEl = document.getElementById('location-status');
        if (distance > location.radius) {
            if (statusEl) {
                statusEl.textContent = `⚠️ Anda berada di luar radius lokasi ${location.name} (${Math.round(distance)}m dari lokasi)`;
                statusEl.className = 'text-xs text-yellow-600 mt-2 text-center';
            }
        } else {
            if (statusEl) {
                statusEl.textContent = `✓ Anda berada dalam radius lokasi ${location.name}`;
                statusEl.className = 'text-xs text-green-600 mt-2 text-center';
            }
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

    // Validate on location select change
    document.querySelector('select[name="location_id"]').addEventListener('change', function() {
        const lat = parseFloat(document.querySelector('input[name="latitude"]').value);
        const lng = parseFloat(document.querySelector('input[name="longitude"]').value);
        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            validateLocation(lat, lng);
        }
    });

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
@endpush
@endsection
