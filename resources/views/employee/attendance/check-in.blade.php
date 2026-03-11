@extends('layouts.employee')

@section('title', 'Check In')

@section('content')
<div class="space-y-6">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Header -->
    <div class="mb-4 sm:mb-6">
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Check In Absensi</h1>
        <p class="text-sm sm:text-base text-gray-600 mt-1">Catat kehadiran Anda hari ini</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span class="block sm:inline text-sm">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 sm:px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span class="block sm:inline text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Check In Form -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
        <!-- Instructions -->
        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-lg sm:text-xl mt-0.5"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-800 mb-2">Cara Check In:</h3>
                    <ol class="text-xs sm:text-sm text-gray-700 space-y-1 list-decimal list-inside">
                        <li>Pilih lokasi absensi dari dropdown</li>
                        <li>Pilih status kehadiran Anda</li>
                        <li>Klik "Dapatkan Lokasi" untuk GPS</li>
                        <li>Upload foto (opsional)</li>
                        <li>Klik "Check In Sekarang"</li>
                    </ol>
                </div>
            </div>
        </div>

        <form id="checkin-form" action="{{ route('employee.attendance.check-in') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Current Time Display -->
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg text-center">
                <div class="text-xs sm:text-sm text-gray-600 mb-1">Waktu Saat Ini</div>
                <div class="text-2xl sm:text-3xl font-bold text-blue-600" id="current-time">{{ now()->format('H:i:s') }}</div>
                <div class="text-xs sm:text-sm text-gray-600 mt-1">{{ now()->format('l, d F Y') }}</div>
            </div>

            @php
                $effectiveShift = $todayShiftInfo['shift'] ?? null;
                $effectiveSchedule = $todayShiftInfo['schedule'] ?? null;
                $shiftSource = $todayShiftInfo['source'] ?? 'none';
            @endphp
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg border {{ $effectiveShift ? 'bg-indigo-50 border-indigo-200' : 'bg-yellow-50 border-yellow-200' }}">
                @if(is_object($effectiveShift) && is_array($effectiveSchedule))
                    <div class="flex items-start gap-3">
                        <i class="fas fa-clock text-indigo-600 mt-1"></i>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-indigo-900">Shift Efektif Hari Ini: {{ $effectiveShift->name }}</p>
                                @if($shiftSource === 'shift_swap')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-800">Tukar Shift</span>
                                @elseif($shiftSource === 'override')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800">Override</span>
                                @endif
                            </div>
                            <p class="text-sm text-indigo-800 mt-1">
                                {{ \Carbon\Carbon::parse($effectiveSchedule['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($effectiveSchedule['end_time'])->format('H:i') }}
                            </p>
                            @if($shiftSource === 'shift_swap' && !empty($todayShiftInfo['swap_with_name']))
                                <p class="text-xs text-purple-700 mt-1">
                                    <i class="fas fa-exchange-alt mr-1"></i>
                                    Jam shift ini berasal dari tukar shift dengan {{ $todayShiftInfo['swap_with_name'] }}.
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">Jadwal shift belum tersedia</p>
                            <p class="text-xs text-yellow-700 mt-1">Silakan hubungi HR/Admin untuk memastikan jadwal shift Anda.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Location -->
            <div class="mb-4">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Lokasi Absensi <span class="text-red-500">*</span>
                </label>
                @php
                    $singleLocation = $locations->count() === 1 ? $locations->first() : null;
                    $defaultLocationId = old('location_id', $singleLocation?->id);
                @endphp
                <select name="location_id"
                        id="location_id"
                        required
                        class="w-full px-3 py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location_id') border-red-500 @enderror">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}"
                                data-lat="{{ $location->latitude }}"
                                data-lng="{{ $location->longitude }}"
                                data-radius="{{ $location->radius }}"
                                {{ $defaultLocationId == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-xs sm:text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Kehadiran -->
            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Status Kehadiran <span class="text-red-500">*</span>
                </label>
                <select name="status"
                        id="status"
                        required
                        class="w-full px-3 py-2.5 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                    <option value="">-- Pilih Status --</option>
                    <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>
                        ✅ Hadir
                    </option>
                    <option value="sick" {{ old('status') == 'sick' ? 'selected' : '' }}>
                        🏥 Sakit
                    </option>
                    <option value="permission" {{ old('status') == 'permission' ? 'selected' : '' }}>
                        📝 Izin
                    </option>
                    <option value="leave" {{ old('status') == 'leave' ? 'selected' : '' }}>
                        🏖️ Cuti
                    </option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs sm:text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    <strong>Hadir:</strong> Datang ke kantor | <strong>Sakit:</strong> Tidak hadir karena sakit<br class="sm:hidden">
                    <strong>Izin:</strong> Keperluan pribadi | <strong>Cuti:</strong> Cuti yang disetujui
                </p>
            </div>

            <!-- Map Container -->
            <div class="mb-4 relative">
                <div id="map" class="w-full h-48 sm:h-64 rounded-lg border-2 border-gray-300 overflow-hidden relative z-0"></div>
                <div class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span>Klik peta untuk mengaktifkan zoom dengan scroll</span>
                </div>
            </div>


            <!-- Photo Camera -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Foto (Opsional)
                </label>

                <!-- Camera Preview -->
                <div class="relative mb-3">
                    <video id="camera-preview" class="w-full rounded-lg border border-gray-300 bg-gray-900" style="display: none; max-height: 300px;" autoplay playsinline></video>
                    <canvas id="photo-canvas" class="w-full rounded-lg border border-gray-300" style="display: none; max-height: 300px;"></canvas>
                    <div id="camera-placeholder" class="w-full h-40 sm:h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                        <i class="fas fa-camera text-gray-400 text-3xl sm:text-4xl mb-2"></i>
                        <p class="text-xs sm:text-sm text-gray-500 text-center px-2">Klik tombol untuk mengambil foto</p>
                    </div>
                    <!-- Switch Camera Button -->
                    <button type="button" id="btn-switch-camera" onclick="switchCamera()" style="display: none;"
                            class="absolute top-2 right-2 p-2 bg-white/90 hover:bg-white text-gray-700 rounded-full shadow-lg transition">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>

                <!-- Camera Controls -->
                <div class="grid grid-cols-2 sm:flex gap-2">
                    <button type="button" id="btn-start-camera" onclick="startCamera()"
                            class="col-span-2 sm:flex-1 px-3 sm:px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-camera mr-2"></i>Buka Kamera
                    </button>
                    <button type="button" id="btn-capture" onclick="capturePhoto()" style="display: none;"
                            class="col-span-2 sm:flex-1 px-3 sm:px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-camera-retro mr-2"></i>Ambil Foto
                    </button>
                    <button type="button" id="btn-retake" onclick="retakePhoto()" style="display: none;"
                            class="sm:flex-1 px-3 sm:px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i>Ambil Ulang
                    </button>
                    <button type="button" id="btn-remove" onclick="removePhoto()" style="display: none;"
                            class="px-3 sm:px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-trash mr-1 sm:mr-0"></i><span class="sm:hidden">Hapus</span>
                    </button>
                </div>

                <input type="hidden" name="photo" id="photo-data">
                @error('photo')
                    <p class="mt-1 text-xs sm:text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">Foto akan diambil menggunakan kamera perangkat</p>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="notes"
                          id="notes"
                          rows="3"
                          class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                          placeholder="Tambahkan catatan jika diperlukan">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs sm:text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Geolocation Info -->
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-2">
                    <label class="text-sm font-medium text-gray-700">
                        <i class="fas fa-map-marked-alt text-blue-500 mr-1"></i>
                        Koordinat GPS <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                onclick="getLocation()"
                                class="flex-1 sm:flex-none text-xs px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition shadow-sm">
                            <i class="fas fa-crosshairs mr-1"></i>Dapatkan Lokasi
                        </button>
                    </div>
                </div>
                <div id="locationStatus" class="text-xs sm:text-sm text-gray-600 mb-1">
                    <i class="fas fa-info-circle mr-1"></i>Klik "Dapatkan Lokasi" setelah memilih lokasi
                </div>
                <div id="accuracyInfo" class="text-xs text-gray-500">Akurasi: —</div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                <input type="hidden" name="accuracy" id="accuracy" value="{{ old('accuracy') }}">
                @error('latitude')
                    <p class="mt-1 text-xs sm:text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <button type="submit" id="btn-checkin"
                        class="w-full sm:flex-1 px-4 sm:px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm sm:text-base font-semibold rounded-lg shadow-md transition duration-150">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Check In Sekarang
                </button>
                <a href="{{ route('employee.attendance.index') }}"
                   class="w-full sm:w-auto px-4 sm:px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm sm:text-base font-semibold rounded-lg transition duration-150 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
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

        const hours = String(currentTime.getHours()).padStart(2, '0');
        const minutes = String(currentTime.getMinutes()).padStart(2, '0');
        const seconds = String(currentTime.getSeconds()).padStart(2, '0');

        document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds} WITA`;
    } else {
        document.getElementById('current-time').textContent = 'Memuat...';
    }
}

// Initialize time
fetchWorldTime(); // Initial fetch
setInterval(updateCurrentTime, 1000); // Update display every second
setInterval(fetchWorldTime, 30000); // Re-sync every 30 seconds
updateCurrentTime();

// Config thresholds
const ACC_THRESHOLD = {{ config('attendance.max_accuracy', 300) }}; // meters

// Map Variables
let map, userMarker, officeCircle;

// Reject extremely coarse fixes (usually IP/cell fallback) and request better samples.
const HARD_REJECT_ACCURACY = 2000; // meters

function getBestAvailablePosition(maxWaitMs = 18000, targetAccuracy = ACC_THRESHOLD) {
    return new Promise((resolve, reject) => {
        let bestPosition = null;
        let watchId = null;
        let finished = false;

        const finish = (position, error = null) => {
            if (finished) return;
            finished = true;
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
            }

            if (position) {
                resolve(position);
            } else {
                reject(error || { code: 3, message: 'TIMEOUT' });
            }
        };

        const onSuccess = (position) => {
            const accuracy = Number(position?.coords?.accuracy ?? 0);

            // Ignore clearly unusable coordinates and keep waiting.
            if (!Number.isFinite(accuracy) || accuracy <= 0 || accuracy > HARD_REJECT_ACCURACY) {
                return;
            }

            if (!bestPosition || accuracy < bestPosition.coords.accuracy) {
                bestPosition = position;
            }

            if (accuracy <= targetAccuracy) {
                finish(position);
            }
        };

        const onError = (error) => {
            if (error && error.code === error.PERMISSION_DENIED) {
                finish(null, error);
            }
        };

        watchId = navigator.geolocation.watchPosition(onSuccess, onError, {
            enableHighAccuracy: true,
            timeout: 12000,
            maximumAge: 0,
        });

        navigator.geolocation.getCurrentPosition(onSuccess, onError, {
            enableHighAccuracy: true,
            timeout: 12000,
            maximumAge: 0,
        });

        setTimeout(() => {
            if (bestPosition) {
                finish(bestPosition);
            } else {
                finish(null, { code: 3, message: 'TIMEOUT' });
            }
        }, maxWaitMs);
    });
}

// Get geolocation
async function getLocation(retryCount = 0) {
    const statusDiv = document.getElementById('locationStatus');

    if (!navigator.geolocation) {
        statusDiv.innerHTML = '<i class="fas fa-times-circle text-red-500 mr-1"></i>Browser Anda tidak mendukung geolocation';
        return;
    }

    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengambil sampel GPS terbaik...';

    try {
        const position = await getBestAvailablePosition(18000, ACC_THRESHOLD);
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
            document.getElementById('accuracy').value = accuracy;
            document.getElementById('accuracyInfo').textContent = `Akurasi: ±${Math.round(accuracy)} m`;

            statusDiv.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>Lokasi berhasil didapat: ${latitude.toFixed(6)}, ${longitude.toFixed(6)} (±${Math.round(accuracy)}m)`;

            updateUserMarker(latitude, longitude, accuracy);

            // Enforce accuracy threshold only for present status
            const submit = document.getElementById('btn-checkin');
            const statusValue = document.getElementById('status')?.value || 'present';
            if (statusValue === 'present' && accuracy && accuracy > ACC_THRESHOLD) {
                submit.disabled = true;
                submit.classList.add('opacity-50','cursor-not-allowed');
                statusDiv.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mr-1"></i>Akurasi masih kurang baik: ±${Math.round(accuracy)} m. Tekan "Dapatkan Lokasi" lagi untuk ambil sampel baru.`;
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

                    if (Number.isFinite(lat) && Number.isFinite(lng) && Number.isFinite(radius)) {
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
    } catch (error) {
            let errorMsg = 'Gagal mendapatkan lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Izin akses lokasi ditolak. Mohon aktifkan GPS dan izinkan akses lokasi di browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                    break;
                case error.TIMEOUT:
                    if (retryCount < 1) {
                        errorMsg = 'Timeout. Mengulang pengambilan lokasi...';
                        statusDiv.innerHTML = `<i class="fas fa-spinner fa-spin text-yellow-500 mr-1"></i>${errorMsg}`;
                        setTimeout(() => getLocation(retryCount + 1), 1000);
                        return;
                    }
                    errorMsg = 'Timeout mendapatkan lokasi. Coba lagi dalam beberapa detik.';
                    break;
            }
            statusDiv.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mr-1"></i>${errorMsg}`;

            // Add retry button
            if (error.code === error.TIMEOUT || error.code === error.POSITION_UNAVAILABLE) {
                statusDiv.innerHTML += ` <button type="button" onclick="getLocation()" class="text-xs underline text-blue-600 hover:text-blue-700">Coba Lagi</button>`;
            }
    }
}

// Initialize Map
function initMap() {
    // Default view (Indonesia)
    map = L.map('map', {
        scrollWheelZoom: false,  // Disable scroll zoom by default
        tap: false,              // Disable tap on mobile
        touchZoom: true,         // Keep touch zoom enabled
        dragging: true           // Keep dragging enabled
    }).setView([-2.5489, 118.0149], 5);

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

    // Add message to enable scroll zoom
    map.on('click', function() {
        if (!map.scrollWheelZoom.enabled()) {
            map.scrollWheelZoom.enable();
        }
    });

    // Disable scroll zoom when mouse leaves map
    const mapElement = document.getElementById('map');
    if (mapElement) {
        mapElement.addEventListener('mouseleave', function() {
            if (map.scrollWheelZoom.enabled()) {
                map.scrollWheelZoom.disable();
            }
        });
    }
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

                if (Number.isFinite(lat) && Number.isFinite(lng) && Number.isFinite(radius)) {
                    // Show office location on map
                    updateOfficeCircle(lat, lng, radius);
                    // Center map to office location
                    map.setView([lat, lng], 15);
                } else if (officeCircle) {
                    map.removeLayer(officeCircle);
                }
            }
        });

        // Ensure single-location setup is reflected immediately on map and geofence UI.
        if (locationSelect.value) {
            locationSelect.dispatchEvent(new window.Event('change'));
        }
    }

    // Check-in form validation
    const checkinForm = document.getElementById('checkin-form');
    if (checkinForm) {
        checkinForm.addEventListener('submit', function(e) {
            const acc = parseFloat(document.getElementById('accuracy').value) || null;
            const statusValue = document.getElementById('status')?.value || 'present';
            if (statusValue === 'present' && acc && acc > ACC_THRESHOLD) {
                e.preventDefault();
                alert('Lokasi tidak cukup akurat (±' + Math.round(acc) + ' m). Silakan gunakan ponsel atau pilih lokasi manual.');
                return false;
            }
        });
    }
});

// ============================================
// CAMERA FUNCTIONALITY
// ============================================
let cameraStream = null;
let capturedPhoto = null;
let currentFacingMode = 'environment'; // 'environment' for rear, 'user' for front

// Start Camera
async function startCamera() {
    try {
        const video = document.getElementById('camera-preview');
        const placeholder = document.getElementById('camera-placeholder');
        const canvas = document.getElementById('photo-canvas');

        // Request camera access with current facing mode
        const constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = cameraStream;

        // Show video, hide others
        video.style.display = 'block';
        placeholder.style.display = 'none';
        canvas.style.display = 'none';

        // Update buttons
        document.getElementById('btn-start-camera').style.display = 'none';
        document.getElementById('btn-capture').style.display = 'block';
        document.getElementById('btn-retake').style.display = 'none';
        document.getElementById('btn-remove').style.display = 'none';
        document.getElementById('btn-switch-camera').style.display = 'block';

    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
    }
}

// Switch Camera (Front/Rear)
async function switchCamera() {
    // Toggle facing mode
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';

    // Stop current stream
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    // Restart camera with new facing mode
    await startCamera();
}

// Capture Photo
function capturePhoto() {
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('photo-canvas');
    const context = canvas.getContext('2d');

    // Set canvas size to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Convert canvas to base64
    capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('photo-data').value = capturedPhoto;

    // Stop camera stream
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    // Show canvas, hide video
    video.style.display = 'none';
    canvas.style.display = 'block';

    // Update buttons
    document.getElementById('btn-start-camera').style.display = 'none';
    document.getElementById('btn-capture').style.display = 'none';
    document.getElementById('btn-retake').style.display = 'block';
    document.getElementById('btn-remove').style.display = 'block';
    document.getElementById('btn-switch-camera').style.display = 'none';
}

// Retake Photo
function retakePhoto() {
    const canvas = document.getElementById('photo-canvas');
    const placeholder = document.getElementById('camera-placeholder');

    // Clear captured photo
    capturedPhoto = null;
    document.getElementById('photo-data').value = '';

    // Hide canvas, show placeholder
    canvas.style.display = 'none';
    placeholder.style.display = 'flex';

    // Update buttons
    document.getElementById('btn-start-camera').style.display = 'block';
    document.getElementById('btn-capture').style.display = 'none';
    document.getElementById('btn-retake').style.display = 'none';
    document.getElementById('btn-remove').style.display = 'none';
    document.getElementById('btn-switch-camera').style.display = 'none';

    // Reset to rear camera
    currentFacingMode = 'environment';
}

// Remove Photo
function removePhoto() {
    retakePhoto(); // Same as retake
}

// Clean up camera stream when leaving page
window.addEventListener('beforeunload', function() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endsection
