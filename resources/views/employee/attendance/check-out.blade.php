@extends('layouts.employee')

@section('title', 'Check Out')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Konfirmasi Check Out</h1>
                <p class="text-sm text-gray-600 mt-1">Selesaikan sesi absensi Anda dengan mengonfirmasi check-out.</p>
            </div>
            <div class="text-right">
                <a href="{{ route('employee.attendance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded shadow-sm hover:shadow">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                    <span class="text-sm text-gray-700">Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-5 border-b">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Sesi tanggal</div>
                    <div class="text-lg font-semibold text-gray-800">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</div>
                    <div class="text-sm text-gray-600">Check-in: <span class="font-medium text-green-700">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</span></div>
                </div>
                <div class="text-sm text-gray-500 text-right">
                    <div>Selamat bekerja,</div>
                    <div class="font-semibold">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="p-5 border-b bg-gradient-to-r from-red-50 to-pink-50">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-red-500 text-xl mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Cara Check Out:</h3>
                    <ol class="text-sm text-gray-700 space-y-1 list-decimal list-inside">
                        <li>Pilih lokasi checkout dari dropdown (lokasi checkout Anda)</li>
                        <li>Sistem akan otomatis mendapatkan koordinat GPS Anda</li>
                        <li>Pastikan Anda berada dalam radius lokasi yang dipilih</li>
                        <li>Upload foto (opsional) dan tambahkan catatan jika diperlukan</li>
                        <li>Klik "Konfirmasi Check Out" untuk menyelesaikan</li>
                    </ol>
                </div>
            </div>
        </div>

        <form action="{{ route('employee.attendance.check-out', $attendance->id) }}" method="POST" enctype="multipart/form-data" id="checkout-form">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="accuracy" id="accuracy">

            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Lokasi</label>
                    <select name="location_id" id="location_id" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-200 @error('location_id') border-red-500 @enderror" required>
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" data-lat="{{ $location->latitude }}" data-lng="{{ $location->longitude }}" data-radius="{{ $location->radius }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div id="map" class="mt-4 w-full h-72 rounded border border-gray-200"></div>

                    <div class="mt-3 flex items-center gap-4 text-sm">
                        <div>
                            <div id="distanceInfo" class="text-gray-600">Menunggu lokasi Anda...</div>
                            <div id="accuracyInfo" class="text-gray-500 text-xs mt-1">Akurasi: —</div>
                        </div>
                        <div id="insideBadge" class="hidden px-2 py-0.5 rounded-full text-xs font-semibold"></div>
                    </div>
                </div>

                <div>
                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Bukti (Opsional)</label>
                        
                        <!-- Camera Preview -->
                        <div class="relative mb-3">
                            <video id="camera-preview" class="w-full rounded-lg border border-gray-300 bg-gray-900" style="display: none; max-height: 200px;" autoplay playsinline></video>
                            <canvas id="photo-canvas" class="w-full rounded-lg border border-gray-300" style="display: none; max-height: 200px;"></canvas>
                            <div id="camera-placeholder" class="w-full h-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center">
                                <i class="fas fa-camera text-gray-400 text-3xl mb-1"></i>
                                <p class="text-xs text-gray-500">Klik tombol untuk ambil foto</p>
                            </div>
                            <!-- Switch Camera Button (shown when camera is active) -->
                            <button type="button" id="btn-switch-camera" onclick="switchCamera()" style="display: none;"
                                    class="absolute top-2 right-2 p-1.5 bg-white/90 hover:bg-white text-gray-700 rounded-full shadow-lg transition text-sm">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>

                        <!-- Camera Controls -->
                        <div class="flex gap-1 mb-3">
                            <button type="button" id="btn-start-camera" onclick="startCamera()" 
                                    class="flex-1 px-2 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded transition">
                                <i class="fas fa-camera mr-1"></i>Buka Kamera
                            </button>
                            <button type="button" id="btn-capture" onclick="capturePhoto()" style="display: none;"
                                    class="flex-1 px-2 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs rounded transition">
                                <i class="fas fa-camera-retro mr-1"></i>Ambil
                            </button>
                            <button type="button" id="btn-retake" onclick="retakePhoto()" style="display: none;"
                                    class="flex-1 px-2 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded transition">
                                <i class="fas fa-redo mr-1"></i>Ulang
                            </button>
                            <button type="button" id="btn-remove" onclick="removePhoto()" style="display: none;"
                                    class="px-2 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <input type="hidden" name="photo" id="photo-data">

                        <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Catatan (Opsional)</label>
                        <textarea name="notes" id="notes" rows="4" class="w-full px-3 py-2 border border-gray-200 rounded text-sm" placeholder="Contoh: Pulang cepat karena sakit..."></textarea>

                        <div class="mt-4">
                            <button type="submit" id="btn-submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded shadow">
                                <i class="fas fa-sign-out-alt"></i> Konfirmasi Check Out
                            </button>
                            <a href="{{ route('employee.attendance.index') }}" class="mt-2 block text-center text-sm text-gray-600 hover:underline">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById('accuracy').value = accuracy;

                updateUserMarker(lat, lng);
                document.getElementById('accuracyInfo').textContent = `Akurasi: ±${Math.round(accuracy)} m`;

                // Enforce accuracy threshold
                const submit = document.getElementById('btn-submit');
                if (accuracy && accuracy > ACC_THRESHOLD) {
                    submit.disabled = true;
                    submit.classList.add('opacity-50', 'cursor-not-allowed');
                    document.getElementById('distanceInfo').textContent = `Akurasi buruk: ±${Math.round(accuracy)} m. Silakan gunakan ponsel atau pilih lokasi manual.`;
                } else {
                    submit.disabled = false;
                    submit.classList.remove('opacity-50', 'cursor-not-allowed');
                }

                // Check distance from selected location (if any)
                const select = document.getElementById('location_id');
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const latLoc = parseFloat(selectedOption.getAttribute('data-lat'));
                    const lngLoc = parseFloat(selectedOption.getAttribute('data-lng'));
                    const radius = parseFloat(selectedOption.getAttribute('data-radius'));
                    
                    if (latLoc && lngLoc && radius) {
                        const distance = computeDistance(lat, lng, latLoc, lngLoc);
                        const distInfo = document.getElementById('distanceInfo');
                        const badge = document.getElementById('insideBadge');
                        
                        distInfo.textContent = Math.round(distance) + ' m dari lokasi terpilih';
                        
                        if (distance <= radius) {
                            badge.textContent = 'Di dalam area';
                            badge.className = 'px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800';
                            badge.classList.remove('hidden');
                        } else {
                            badge.textContent = 'Di luar area';
                            badge.className = 'px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700';
                            badge.classList.remove('hidden');
                        }
                    }
                }

            }, function(error) {
                document.getElementById('distanceInfo').textContent = 'Gagal mendapatkan lokasi.';
            }, { enableHighAccuracy: true });
        } else {
            document.getElementById('distanceInfo').textContent = 'Browser Anda tidak mendukung Geolocation.';
        }

        // Handle Location Select Change
        document.getElementById('location_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const lat = selectedOption.getAttribute('data-lat');
            const lng = selectedOption.getAttribute('data-lng');
            const radius = selectedOption.getAttribute('data-radius');

            if (lat && lng) {
                updateOfficeCircle(parseFloat(lat), parseFloat(lng), parseFloat(radius));
            }

            // update distance immediately if user location exists
            const ulat = document.getElementById('latitude').value;
            const ulng = document.getElementById('longitude').value;
            if (ulat && ulng && lat && lng) {
                const dist = computeDistance(parseFloat(ulat), parseFloat(ulng), parseFloat(lat), parseFloat(lng));
                document.getElementById('distanceInfo').textContent = Math.round(dist) + ' m dari lokasi terpilih';
                const badge = document.getElementById('insideBadge');
                if (radius && dist <= parseFloat(radius)) {
                    badge.textContent = 'Di dalam area';
                    badge.className = 'px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800';
                    badge.classList.remove('hidden');
                } else {
                    badge.textContent = 'Di luar area';
                    badge.className = 'px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700';
                    badge.classList.remove('hidden');
                }

                // Re-evaluate accuracy and enable/disable submit accordingly
                const accVal = parseFloat(document.getElementById('accuracy').value) || null;
                const submit = document.getElementById('btn-submit');
                if (accVal && accVal > ACC_THRESHOLD) {
                    submit.disabled = true;
                    submit.classList.add('opacity-50','cursor-not-allowed');
                    document.getElementById('accuracyInfo').textContent = `Akurasi: ±${Math.round(accVal)} m (tidak cukup akurat)`;
                } else {
                    submit.disabled = false;
                    submit.classList.remove('opacity-50','cursor-not-allowed');
                    if (accVal) document.getElementById('accuracyInfo').textContent = `Akurasi: ±${Math.round(accVal)} m`;
                }
            }
        });

        // Form submit validation for location and accuracy
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            const acc = parseFloat(document.getElementById('accuracy').value) || null;

            if (!lat || !lng) {
                e.preventDefault();
                alert('Lokasi belum terdeteksi. Mohon tunggu sebentar atau refresh halaman, dan pastikan GPS aktif.');
                return;
            }

            if (acc && acc > ACC_THRESHOLD) {
                e.preventDefault();
                alert('Lokasi tidak cukup akurat (±' + Math.round(acc) + ' m). Silakan gunakan ponsel atau pilih lokasi manual.');
                return;
            }
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