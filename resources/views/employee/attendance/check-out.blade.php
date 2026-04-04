@extends('layouts.employee')

@section('title', 'Check Out')

@section('content')
<div class="space-y-6">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Header -->
    <div class="mb-4 sm:mb-6">
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Check Out Absensi</h1>
        <p class="text-sm sm:text-base text-gray-600 mt-1">Selesaikan sesi kehadiran Anda hari ini</p>
    </div>

    <!-- Session Info -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6">
        @php
            $effectiveShift = $attendanceShiftInfo['shift'] ?? null;
            $effectiveSchedule = $attendanceShiftInfo['schedule'] ?? null;
            $shiftSource = $attendanceShiftInfo['source'] ?? 'none';
            $isCheckoutExpired = (bool) ($checkoutWindowInfo['is_past_checkout_window'] ?? false);
        @endphp
        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg text-center">
            <div class="text-xs sm:text-sm text-gray-600 mb-1">Sesi Absensi</div>
            <div class="text-lg sm:text-xl font-bold text-blue-700">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l, d M Y') }}</div>
            <div class="text-xs sm:text-sm text-gray-600 mt-1">Check-in: {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</div>
        </div>

        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-lg sm:text-xl mt-0.5"></i>
                </div>
                <div class="ml-2 sm:ml-3">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-800 mb-2">Cara Check Out:</h3>
                    <ol class="text-xs sm:text-sm text-gray-700 space-y-1 list-decimal list-inside">
                        <li>Lokasi check-out ditentukan otomatis oleh sistem</li>
                        <li>Pastikan GPS berhasil didapatkan</li>
                        <li>Pastikan posisi berada dalam radius lokasi</li>
                        <li>Ambil foto bukti (wajib)</li>
                        <li>Klik "Konfirmasi Check Out"</li>
                    </ol>
                </div>
            </div>
        </div>

        @if($checkoutWindowInfo)
            @php
                $isPastShiftEnd = (bool) ($checkoutWindowInfo['is_past_shift_end'] ?? false);
                $isPastCheckoutWindow = (bool) ($checkoutWindowInfo['is_past_checkout_window'] ?? false);
                $shiftEndTimeText = \Carbon\Carbon::parse($checkoutWindowInfo['shift_end_time'])->format('H:i');
                $maxCheckoutTimeText = \Carbon\Carbon::parse($checkoutWindowInfo['max_checkout_time'])->format('H:i');
            @endphp
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg border-l-4 {{ $isPastCheckoutWindow ? 'bg-red-50 border-red-500' : ($isPastShiftEnd ? 'bg-amber-50 border-amber-500' : 'bg-emerald-50 border-emerald-500') }}">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="fas {{ $isPastCheckoutWindow ? 'fa-exclamation-triangle text-red-500' : ($isPastShiftEnd ? 'fa-clock text-amber-500' : 'fa-check-circle text-emerald-500') }} text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold {{ $isPastCheckoutWindow ? 'text-red-800' : ($isPastShiftEnd ? 'text-amber-800' : 'text-emerald-800') }}">
                            Status Waktu Check-out
                        </h3>
                        @if($isPastCheckoutWindow)
                            <p class="text-sm text-red-700 mt-1">
                                Anda sudah melewati batas check-out. Jam kerja berakhir pukul <strong>{{ $shiftEndTimeText }}</strong>,
                                dan batas check-out adalah <strong>{{ $maxCheckoutTimeText }}</strong>.
                            </p>
                            <p class="text-xs text-red-700 mt-1">
                                Silakan hubungi admin untuk koreksi absensi.
                            </p>
                        @elseif($isPastShiftEnd)
                            <p class="text-sm text-amber-700 mt-1">
                                Jam kerja sudah berakhir pukul <strong>{{ $shiftEndTimeText }}</strong>.
                                Anda masih dapat check-out sampai <strong>{{ $maxCheckoutTimeText }}</strong>.
                            </p>
                        @else
                            <p class="text-sm text-emerald-700 mt-1">
                                Jam kerja berakhir pukul <strong>{{ $shiftEndTimeText }}</strong>.
                                Anda dapat check-out normal hingga batas <strong>{{ $maxCheckoutTimeText }}</strong>.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="p-0 sm:p-0">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Sesi Absensi</div>
                    <div class="text-lg font-semibold text-gray-800">
                        {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('l, d M Y') }}
                    </div>
                    <div class="text-sm text-green-700 mt-1 flex items-center gap-1">
                        <i class="fas fa-sign-in-alt text-xs"></i>
                        Check-in: {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                    </div>
                    @if(is_object($effectiveShift) && is_array($effectiveSchedule))
                        <div class="text-sm text-indigo-700 mt-1 flex items-center gap-1">
                            <i class="fas fa-clock text-xs"></i>
                            Shift: {{ $effectiveShift->name }} ({{ \Carbon\Carbon::parse($effectiveSchedule['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($effectiveSchedule['end_time'])->format('H:i') }})
                        </div>
                        @if($shiftSource === 'shift_swap' && !empty($attendanceShiftInfo['swap_with_name']))
                            <div class="text-xs text-purple-700 mt-1">
                                <i class="fas fa-exchange-alt mr-1"></i>
                                Jam ini berasal dari tukar shift dengan {{ $attendanceShiftInfo['swap_with_name'] }}.
                            </div>
                        @endif
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">Selamat bekerja,</div>
                    <div class="font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ auth()->user()->worker->employee_id ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Out Form -->
    <form action="{{ route('employee.attendance.check-out', $attendance->id) }}" method="POST" enctype="multipart/form-data" id="checkout-form">
        @csrf
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <input type="hidden" name="accuracy" id="accuracy">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Location & Map Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-blue-600"></i>
                        Lokasi & Peta
                    </h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Check-Out (Otomatis)</label>
                        @php
                            $singleLocation = $locations->first();
                            $defaultLocationId = old('location_id', $singleLocation?->id);
                        @endphp
                        <select name="location_id" id="location_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location_id') border-red-500 @enderror" disabled>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}"
                                        data-lat="{{ $location->latitude }}"
                                        data-lng="{{ $location->longitude }}"
                                        data-radius="{{ $location->radius }}"
                                        {{ $defaultLocationId == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }} (Radius: {{ $location->radius }}m)
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Map -->
                    <div id="map" class="w-full h-80 rounded-lg border border-gray-300 mb-4"></div>

                    <!-- GPS Status -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-satellite-dish text-blue-600"></i>
                            Status GPS
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <div id="distanceInfo" class="text-sm text-gray-600 mb-1">
                                    <i class="fas fa-search-location mr-1"></i>
                                    Mencari lokasi Anda...
                                </div>
                                <div id="accuracyInfo" class="text-xs text-gray-500">
                                    <i class="fas fa-crosshairs mr-1"></i>
                                    Akurasi: —
                                </div>
                                <div id="gpsNotice" class="hidden text-xs mt-2 rounded-md px-2 py-1"></div>
                            </div>
                            <div>
                                <div id="insideBadge" class="hidden inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium"></div>
                                <button type="button" id="refreshLocation" class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-100 text-blue-700 text-sm rounded-lg hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-sync-alt"></i>
                                    Refresh Lokasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo & Notes Section -->
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-camera text-green-600"></i>
                        Foto & Catatan
                    </h3>
                    <!-- Photo Section -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Foto Bukti
                            <span class="text-sm font-normal text-red-600">(Wajib)</span>
                        </label>
                        <div class="text-xs text-gray-600 mb-3">
                            📸 Ambil foto selfie atau lingkungan sekitar sebagai bukti check-out Anda (wajib)
                        </div>

                        <!-- Camera Preview -->
                        <div class="relative mb-4">
                            <video id="camera-preview" class="w-full rounded-lg border border-gray-300 bg-gray-900" style="display: none; max-height: 250px;" autoplay playsinline></video>
                            <canvas id="photo-canvas" class="w-full rounded-lg border border-gray-300" style="display: none; max-height: 250px;"></canvas>
                            <div id="camera-placeholder" class="w-full h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-camera text-gray-400 text-4xl mb-2"></i>
                                    <p class="text-sm text-gray-500 font-medium">Klik tombol untuk ambil foto</p>
                                    <p class="text-xs text-gray-400 mt-1">Foto akan digunakan sebagai bukti check-out</p>
                                </div>
                            </div>
                            <!-- Switch Camera Button -->
                            <button type="button" id="btn-switch-camera" onclick="switchCamera()" style="display: none;"
                                    class="absolute top-3 right-3 p-2 bg-white/90 hover:bg-white text-gray-700 rounded-full shadow-lg transition-all duration-200 text-sm">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>

                        <!-- Camera Controls -->
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <button type="button" id="btn-start-camera" onclick="startCamera()"
                                    class="flex items-center justify-center gap-2 px-3 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <i class="fas fa-camera"></i>
                                Buka Kamera
                            </button>
                            <button type="button" id="btn-capture" onclick="capturePhoto()" style="display: none;"
                                    class="flex items-center justify-center gap-2 px-3 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <i class="fas fa-camera-retro"></i>
                                Ambil Foto
                            </button>
                            <button type="button" id="btn-retake" onclick="retakePhoto()" style="display: none;"
                                    class="flex items-center justify-center gap-2 px-3 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <i class="fas fa-redo"></i>
                                Foto Ulang
                            </button>
                            <button type="button" id="btn-remove" onclick="removePhoto()" style="display: none;"
                                    class="flex items-center justify-center gap-2 px-3 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <i class="fas fa-trash"></i>
                                Hapus
                            </button>
                        </div>

                        <input type="hidden" name="photo" id="photo-data">
                        @error('photo')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                  placeholder="Contoh: Pulang lebih awal karena ada keperluan keluarga..."></textarea>
                        <div class="text-xs text-gray-500 mt-1">
                            Maksimal 500 karakter
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <button type="submit" id="btn-submit"
                                class="w-full sm:flex-1 px-4 sm:px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm sm:text-base font-semibold rounded-lg shadow-md transition duration-150 {{ $isCheckoutExpired ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $isCheckoutExpired ? 'disabled' : '' }}>
                            <i class="fas fa-sign-out-alt"></i>
                            Konfirmasi Check Out
                        </button>
                        <a href="{{ route('employee.attendance.index') }}"
                           class="w-full sm:w-auto px-4 sm:px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm sm:text-base font-semibold rounded-lg transition duration-150 text-center">
                            Batal
                        </a>
                    </div>

                    <!-- Quick Info -->
                    <div class="mt-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="text-xs text-blue-800">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-info-circle"></i>
                                <span class="font-medium">Info:</span>
                            </div>
                            <ul class="space-y-1 ml-4">
                                <li>• Waktu check-out akan tercatat secara otomatis</li>
                                <li>• Foto akan disimpan sebagai bukti kehadiran</li>
                                <li>• Pastikan lokasi GPS akurat untuk validasi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let map, userMarker, officeCircle;

    const HARD_REJECT_ACCURACY = 5000; // meters

    function getBestAvailablePosition(maxWaitMs = 18000, targetAccuracy = {{ config('attendance.max_accuracy', 300) }}) {
        return new Promise((resolve, reject) => {
            let bestPosition = null;
            let coarseFallbackPosition = null;
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

                if (!Number.isFinite(accuracy) || accuracy <= 0) {
                    return;
                }

                // Keep the best coarse result as last-resort fallback.
                if (!coarseFallbackPosition || accuracy < coarseFallbackPosition.coords.accuracy) {
                    coarseFallbackPosition = position;
                }

                // Ignore extremely coarse results for primary acceptance.
                if (accuracy > HARD_REJECT_ACCURACY) {
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
                timeout: 15000,
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
                } else if (coarseFallbackPosition) {
                    finish(coarseFallbackPosition);
                } else {
                    finish(null, { code: 3, message: 'TIMEOUT' });
                }
            }, maxWaitMs);
        });
    }

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
    }

    function updateUserMarker(lat, lng) {
        if (!map) initMap();
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.marker([lat, lng]).addTo(map).bindPopup("Lokasi Anda");
    }

    function updateOfficeCircle(lat, lng, radius) {
        if (!map) initMap();
        if (officeCircle) map.removeLayer(officeCircle);

        officeCircle = L.circle([lat, lng], {
            color: '#9f1239',
            fillColor: '#fecdd3',
            fillOpacity: 0.2,
            radius: radius
        }).addTo(map);

        if (userMarker) {
            const group = new L.featureGroup([userMarker, officeCircle]);
            map.fitBounds(group.getBounds().pad(0.1));
        } else {
            map.setView([lat, lng], 16);
        }
    }

    function computeDistance(lat1, lon1, lat2, lon2) {
        // Haversine formula in meters
        function toRad(deg) { return deg * Math.PI / 180; }
        const R = 6371000;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        const ACC_THRESHOLD = {{ config('attendance.max_accuracy', 300) }}; // meters
        const IS_CHECKOUT_EXPIRED = @json($isCheckoutExpired);

        function resetLocationInputs() {
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            document.getElementById('accuracy').value = '';
            document.getElementById('accuracyInfo').innerHTML = '<i class="fas fa-crosshairs mr-1"></i>Akurasi: -';
            const submit = document.getElementById('btn-submit');
            submit.disabled = true;
            submit.classList.add('opacity-50', 'cursor-not-allowed');
        }

        async function refreshBestLocation() {
            if (!('geolocation' in navigator)) {
                document.getElementById('distanceInfo').innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Browser tidak mendukung Geolocation';
                return;
            }

            resetLocationInputs();
            document.getElementById('insideBadge').classList.add('hidden');

            document.getElementById('distanceInfo').innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengambil beberapa sampel GPS...';

            try {
                const position = await getBestAvailablePosition(22000, ACC_THRESHOLD);
                updateLocation(position);
            } catch (error) {
                handleLocationError(error);
            }
        }

        // Geolocation
        refreshBestLocation();

        // Refresh location button
        document.getElementById('refreshLocation').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mencari...';

            refreshBestLocation().finally(() => {
                document.getElementById('refreshLocation').disabled = false;
                document.getElementById('refreshLocation').innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Refresh Lokasi';
            });
        });

        function updateLocation(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('accuracy').value = accuracy;

            updateUserMarker(lat, lng);
            document.getElementById('accuracyInfo').innerHTML = `<i class="fas fa-crosshairs mr-1"></i>Akurasi: ±${Math.round(accuracy)} m`;

            // Accuracy validation
            const submit = document.getElementById('btn-submit');
            if (IS_CHECKOUT_EXPIRED) {
                submit.disabled = true;
                submit.classList.add('opacity-50', 'cursor-not-allowed');
                document.getElementById('distanceInfo').innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Batas waktu check-out sudah terlewati';
            } else if (accuracy && accuracy > ACC_THRESHOLD) {
                submit.disabled = true;
                submit.classList.add('opacity-50', 'cursor-not-allowed');
                document.getElementById('distanceInfo').innerHTML = `<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Akurasi kurang baik: ±${Math.round(accuracy)} m`;
                showNotification('GPS tidak cukup akurat. Coba refresh atau pindah ke area terbuka.', 'warning');
            } else {
                submit.disabled = false;
                submit.classList.remove('opacity-50', 'cursor-not-allowed');
                document.getElementById('distanceInfo').innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i>Lokasi ditemukan dengan akurat`;
            }

            // Check distance if location selected
            checkLocationDistance(lat, lng);
        }

        function handleLocationError(error) {
            resetLocationInputs();
            let message = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Akses lokasi ditolak. Mohon izinkan akses lokasi pada browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    message = 'Request lokasi timeout. Tekan refresh lokasi untuk ambil sampel baru.';
                    break;
                default:
                    message = 'Error tidak diketahui saat mendapatkan lokasi.';
                    break;
            }
            document.getElementById('distanceInfo').innerHTML = `<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>${message}`;
            showNotification(message, 'error');
        }

        function checkLocationDistance(userLat, userLng) {
            const select = document.getElementById('location_id');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption && selectedOption.value) {
                const latLoc = parseFloat(selectedOption.getAttribute('data-lat'));
                const lngLoc = parseFloat(selectedOption.getAttribute('data-lng'));
                const radius = parseFloat(selectedOption.getAttribute('data-radius'));

                if (latLoc && lngLoc && radius) {
                    const distance = computeDistance(userLat, userLng, latLoc, lngLoc);
                    const badge = document.getElementById('insideBadge');

                    document.getElementById('distanceInfo').innerHTML =
                        `<i class="fas fa-map-marker-alt mr-1"></i>${Math.round(distance)} m dari lokasi terpilih`;

                    if (distance <= radius) {
                        badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Di dalam area kerja';
                        badge.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-green-100 text-green-800 border border-green-200';
                        badge.classList.remove('hidden');
                        showNotification('Anda berada dalam area kerja yang valid', 'success');
                    } else {
                        badge.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Di luar area kerja';
                        badge.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium bg-red-100 text-red-800 border border-red-200';
                        badge.classList.remove('hidden');
                        showNotification(`Anda berada ${Math.round(distance - radius)} meter di luar area kerja`, 'warning');
                    }
                }
            }
        }

        function showNotification(message, type) {
            const notice = document.getElementById('gpsNotice');
            if (!notice) {
                return;
            }

            const classes = {
                success: 'text-green-700 bg-green-100 border border-green-200',
                warning: 'text-amber-700 bg-amber-100 border border-amber-200',
                error: 'text-red-700 bg-red-100 border border-red-200',
            };

            notice.className = `text-xs mt-2 rounded-md px-2 py-1 ${classes[type] || classes.error}`;
            notice.textContent = message;
            notice.classList.remove('hidden');
        }

        // Handle Location Select Change
        document.getElementById('location_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const lat = selectedOption.getAttribute('data-lat');
            const lng = selectedOption.getAttribute('data-lng');
            const radius = selectedOption.getAttribute('data-radius');

            if (lat && lng) {
                updateOfficeCircle(parseFloat(lat), parseFloat(lng), parseFloat(radius));

                // Update distance if user location exists
                const ulat = document.getElementById('latitude').value;
                const ulng = document.getElementById('longitude').value;
                if (ulat && ulng) {
                    checkLocationDistance(parseFloat(ulat), parseFloat(ulng));
                }
            } else {
                // Clear badge if no location selected
                document.getElementById('insideBadge').classList.add('hidden');
                document.getElementById('distanceInfo').innerHTML = '<i class="fas fa-map-marker-alt mr-1"></i>Pilih lokasi terlebih dahulu';
            }
        });

        const locationSelect = document.getElementById('location_id');
        if (locationSelect && locationSelect.value) {
            locationSelect.dispatchEvent(new window.Event('change'));
        }

        // Form submit validation for location and accuracy
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            const acc = parseFloat(document.getElementById('accuracy').value) || null;
            const locationId = document.getElementById('location_id').value;

            if (!locationId) {
                e.preventDefault();
                showNotification('Pilih lokasi check-out terlebih dahulu', 'error');
                return;
            }

            if (!lat || !lng) {
                e.preventDefault();
                showNotification('Lokasi GPS belum terdeteksi. Mohon tunggu atau refresh lokasi.', 'error');
                return;
            }

            if (acc && acc > ACC_THRESHOLD) {
                e.preventDefault();
                showNotification('Akurasi GPS tidak cukup baik. Coba pindah ke area terbuka.', 'error');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        });
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
