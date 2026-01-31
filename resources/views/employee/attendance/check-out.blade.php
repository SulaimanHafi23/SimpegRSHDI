@extends('layouts.employee')

@section('title', 'Check Out')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-sign-out-alt text-red-600"></i>
                    Konfirmasi Check Out
                </h1>
                <p class="text-sm text-gray-600 mt-1">Selesaikan sesi absensi Anda dengan mengonfirmasi check-out</p>
            </div>
            <a href="{{ route('employee.attendance.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="hidden sm:inline">Kembali</span>
            </a>
        </div>
    </div>

    <!-- Session Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 sm:p-6 border-b border-gray-200">
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
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600">Selamat bekerja,</div>
                    <div class="font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ auth()->user()->worker->employee_id ?? '' }}</div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-4 sm:p-6 border-b border-gray-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-info-circle text-amber-600"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Panduan Check Out:</h3>
                    <ol class="text-sm text-gray-700 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-amber-100 text-amber-800 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <span>Pilih lokasi checkout dari dropdown yang tersedia</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-amber-100 text-amber-800 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <span>Sistem akan otomatis mendapatkan koordinat GPS Anda</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-amber-100 text-amber-800 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <span>Pastikan Anda berada dalam radius lokasi yang dipilih</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-amber-100 text-amber-800 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            <span>Upload foto (opsional) dan tambahkan catatan jika diperlukan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="flex-shrink-0 w-5 h-5 bg-amber-100 text-amber-800 rounded-full flex items-center justify-center text-xs font-bold">5</span>
                            <span>Klik "Konfirmasi Check Out" untuk menyelesaikan</span>
                        </li>
                    </ol>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Lokasi Check-Out</label>
                        <select name="location_id" id="location_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location_id') border-red-500 @enderror" required>
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" 
                                        data-lat="{{ $location->latitude }}" 
                                        data-lng="{{ $location->longitude }}" 
                                        data-radius="{{ $location->radius }}">
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
                            <span class="text-sm font-normal text-blue-600">(Sangat Direkomendasikan)</span>
                        </label>
                        <div class="text-xs text-gray-600 mb-3">
                            📸 Ambil foto selfie atau lingkungan sekitar sebagai bukti check-out Anda
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
                    <div class="space-y-3">
                        <button type="submit" id="btn-submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-sign-out-alt"></i> 
                            Konfirmasi Check Out
                        </button>
                        
                        <a href="{{ route('employee.attendance.index') }}" 
                           class="block text-center text-sm text-gray-600 hover:text-gray-800 hover:underline transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Batal Check Out
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

    function initMap() {
        map = L.map('map').setView([-2.5489, 118.0149], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
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

        // Geolocation
        if ("geolocation" in navigator) {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                updateLocation(position);
            }, function(error) {
                handleLocationError(error);
            }, { 
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 
            });
        } else {
            document.getElementById('distanceInfo').innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Browser tidak mendukung Geolocation';
        }

        // Refresh location button
        document.getElementById('refreshLocation').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mencari...';
            
            navigator.geolocation.getCurrentPosition(function(position) {
                updateLocation(position);
                document.getElementById('refreshLocation').disabled = false;
                document.getElementById('refreshLocation').innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Refresh Lokasi';
            }, function(error) {
                handleLocationError(error);
                document.getElementById('refreshLocation').disabled = false;
                document.getElementById('refreshLocation').innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Refresh Lokasi';
            }, { 
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0 
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
            if (accuracy && accuracy > ACC_THRESHOLD) {
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
            let message = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Akses lokasi ditolak. Mohon izinkan akses lokasi pada browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    message = 'Request lokasi timeout. Coba refresh halaman.';
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
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
            
            const bgColor = type === 'success' ? 'bg-green-500' : 
                           type === 'warning' ? 'bg-yellow-500' : 'bg-red-500';
            
            notification.classList.add(bgColor, 'text-white');
            notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'}"></i>
                    <span class="text-sm">${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Hide notification
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 4000);
        }
        } else {
            document.getElementById('distanceInfo').innerHTML = '<i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>Browser tidak mendukung Geolocation';
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