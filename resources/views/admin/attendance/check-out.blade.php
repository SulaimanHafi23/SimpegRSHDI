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
            @php
                $effectiveShift = $shiftInfo['shift'] ?? null;
                $effectiveSchedule = $shiftInfo['schedule'] ?? null;
                $shiftSource = $shiftInfo['source'] ?? 'none';
            @endphp
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

                @if(is_object($effectiveShift) && is_array($effectiveSchedule))
                    <div>
                        <label class="text-sm font-medium text-gray-700">Shift Efektif</label>
                        <div class="mt-1">
                            <p class="text-base font-semibold text-gray-900">
                                {{ $effectiveShift->name }}
                                @if($shiftSource === 'shift_swap')
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Tukar Shift</span>
                                @elseif($shiftSource === 'override')
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">Override</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                {{ \Carbon\Carbon::parse($effectiveSchedule['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($effectiveSchedule['end_time'])->format('H:i') }}
                            </p>
                            @if($shiftSource === 'shift_swap' && !empty($shiftInfo['swap_with_name']))
                                <p class="text-xs text-purple-700 mt-1">
                                    <i class="fas fa-exchange-alt mr-1"></i>
                                    Shift hasil tukar dengan {{ $shiftInfo['swap_with_name'] }}.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

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

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                    <p class="font-semibold mb-1">Info Checkout Admin</p>
                    <p>Checkout dari halaman ini akan ditandai sebagai checkout oleh admin. Koordinat akan otomatis menggunakan lokasi yang dipilih.</p>
                </div>
                
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

                <div>
                    <label for="admin_checkout_note" class="block text-sm font-medium text-gray-700 mb-1">
                        Keterangan Admin (Opsional)
                    </label>
                    <textarea
                        id="admin_checkout_note"
                        name="admin_checkout_note"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('admin_checkout_note') border-red-500 @enderror"
                        placeholder="Contoh: Pegawai lupa checkout dan sudah dikonfirmasi atasan.">{{ old('admin_checkout_note') }}</textarea>
                    @error('admin_checkout_note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-form.file 
                    name="photo" 
                    label="Foto (Opsional)"
                    accept="image/*"
                    :error="$errors->first('photo')">
                    <x-slot name="help">
                        Format: JPG, JPEG, PNG. Maksimal 2MB
                    </x-slot>
                </x-form.file>

                <div>
                    <x-button
                        type="button"
                        variant="secondary"
                        onclick="getCurrentLocation()"
                        class="w-full"
                        id="get-location-btn">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Dapatkan Lokasi
                    </x-button>
                    <p class="text-xs text-gray-500 mt-2 text-center" id="location-status"></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Latitude/Longitude Lokasi Terpilih</p>
                        <p id="selected-coordinates" class="mt-1 text-sm font-semibold text-gray-900">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Latitude/Longitude Perangkat</p>
                        <p id="current-coordinates" class="mt-1 text-sm font-semibold text-gray-900">-</p>
                    </div>
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

    {{-- Location Map --}}
    <x-card title="Peta Lokasi Terpilih">
        <p class="text-sm text-gray-600 mb-3">Peta hanya menampilkan area lokasi yang dipilih pada form check-out.</p>
        <div id="map" class="w-full h-96 rounded-lg"></div>
    </x-card>
</div>

@push('scripts')
<script>
    // Location data from controller
    const locationsData = @json($locationsData);
    let map;
    let selectedMarker;
    let selectedCircle;
    let currentLocationMarker;

    function formatCoordinates(lat, lng) {
        return `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
    }

    // Initialize map
    function initMap() {
        map = L.map('map').setView([-2.5489, 118.0149], 5);

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

        renderSelectedLocation();
    }

    function renderSelectedLocation() {
        const locationId = document.querySelector('select[name="location_id"]').value;
        const selectedCoordinatesEl = document.getElementById('selected-coordinates');

        if (selectedMarker) {
            selectedMarker.remove();
            selectedMarker = null;
        }
        if (selectedCircle) {
            selectedCircle.remove();
            selectedCircle = null;
        }

        if (!locationId || !locationsData[locationId]) {
            if (selectedCoordinatesEl) {
                selectedCoordinatesEl.textContent = '-';
            }
            return;
        }

        const location = locationsData[locationId];
        const latLng = [location.latitude, location.longitude];

        selectedMarker = L.marker(latLng).addTo(map);
        selectedMarker.bindPopup(`<strong>${location.name}</strong><br><small>Titik lokasi terpilih</small>`);

        selectedCircle = L.circle(latLng, {
            color: '#2563EB',
            fillColor: '#60A5FA',
            fillOpacity: 0.2,
            radius: location.radius
        }).addTo(map);

        selectedCircle.bindPopup(`<strong>${location.name}</strong><br><small>Radius: ${location.radius}m</small>`);
        map.fitBounds(selectedCircle.getBounds(), { padding: [24, 24] });

        if (selectedCoordinatesEl) {
            selectedCoordinatesEl.textContent = formatCoordinates(location.latitude, location.longitude);
        }
    }

    function getCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation tidak didukung oleh browser Anda');
            return;
        }

        const button = document.getElementById('get-location-btn');
        const statusEl = document.getElementById('location-status');
        const currentCoordinatesEl = document.getElementById('current-coordinates');

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

                if (currentLocationMarker) {
                    currentLocationMarker.remove();
                }

                currentLocationMarker = L.marker([lat, lng]).addTo(map);
                currentLocationMarker.bindPopup(`Lokasi perangkat admin<br><small>Akurasi: ±${Math.round(accuracy)}m</small>`).openPopup();
                map.setView([lat, lng], 16);

                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Dapatkan Lokasi';
                }
                if (statusEl) {
                    statusEl.textContent = `Lokasi ditemukan (Akurasi: ±${Math.round(accuracy)}m)`;
                    statusEl.className = 'text-xs text-green-600 mt-2 text-center';
                }
                if (currentCoordinatesEl) {
                    currentCoordinatesEl.textContent = formatCoordinates(lat, lng);
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
                    button.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Dapatkan Lokasi';
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

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        const locationSelect = document.querySelector('select[name="location_id"]');
        if (locationSelect) {
            locationSelect.addEventListener('change', renderSelectedLocation);
        }
    });
</script>

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush
@endsection
