@extends('layouts.admin')

@section('title', 'Absensi Manual')

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
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Absensi Manual</h1>
            <p class="text-sm text-gray-600 mt-1">Input absensi pegawai secara manual</p>
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
        {{-- Check In Form --}}
        <x-card title="Check In">
            <form action="{{ route('admin.attendance.check-in') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <x-form.select 
                    name="worker_id" 
                    label="Pegawai"
                    required 
                    :error="$errors->first('worker_id')">
                    <option value="">Pilih Pegawai</option>
                    @foreach($workers as $worker)
                        <option value="{{ $worker->id }}" {{ old('worker_id') == $worker->id ? 'selected' : '' }}>
                            {{ $worker->nip }} - {{ $worker->name }}
                        </option>
                    @endforeach
                </x-form.select>

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
                    preview
                    help="Format: JPG, PNG (Max: 2MB)" />

                <x-form.textarea 
                    name="notes" 
                    label="Catatan (Opsional)" 
                    rows="3" 
                    :value="old('notes')"
                    :error="$errors->first('notes')"
                    placeholder="Tambahkan catatan jika diperlukan" />

                <div id="location-validation-message" class="hidden"></div>

                <div class="flex justify-end gap-3">
                    <x-button 
                        variant="secondary"
                        onclick="window.location.href='{{ route('admin.attendance.index') }}'">
                        Batal
                    </x-button>
                    <x-button 
                        variant="success" 
                        icon="fas fa-sign-in-alt"
                        type="submit"
                        id="submit-checkin-btn">
                        Check In
                    </x-button>
                </div>
            </form>
        </x-card>

        {{-- Info Card --}}
        <div class="space-y-6">
            <x-card title="Informasi">
                <div class="space-y-4 text-sm">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Check In Manual</p>
                            <p class="text-gray-600">Gunakan form ini untuk mencatat absensi pegawai secara manual. Pastikan data yang diinput sudah benar.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <i class="fas fa-map-marker-alt text-green-500 mt-1"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Koordinat GPS</p>
                            <p class="text-gray-600">Masukkan koordinat GPS lokasi absensi. Jika tidak ada, gunakan koordinat kantor (0, 0).</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <i class="fas fa-camera text-purple-500 mt-1"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Foto Absensi</p>
                            <p class="text-gray-600">Upload foto pegawai saat absen (opsional). Format JPG/PNG maksimal 2MB.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <i class="fas fa-clock text-yellow-500 mt-1"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Waktu Otomatis</p>
                            <p class="text-gray-600">Waktu check-in akan dicatat secara otomatis saat data disimpan.</p>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card title="Lokasi Tersedia">
                <div class="space-y-2">
                    @forelse($locations as $location)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="font-semibold text-gray-900">{{ $location->name }}</p>
                            <p class="text-sm text-gray-600">{{ $location->address ?? '-' }}</p>
                            @if($location->radius)
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-bullseye"></i> Radius: {{ $location->radius }}m
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada lokasi tersedia</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Location data for validation
    const locations = {!! json_encode($locationsData) !!};
    console.log('Locations data loaded:', locations);

    // Haversine formula to calculate distance
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth radius in meters
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c; // Distance in meters
    }

    // Validate location
    function validateLocation() {
        const locationId = document.querySelector('select[name="location_id"]').value;
        const latitude = parseFloat(document.querySelector('input[name="latitude"]').value);
        const longitude = parseFloat(document.querySelector('input[name="longitude"]').value);
        const messageDiv = document.getElementById('location-validation-message');
        const submitBtn = document.getElementById('submit-checkin-btn');

        console.log('Validating location:', {locationId, latitude, longitude});

        if (!locationId || isNaN(latitude) || isNaN(longitude) || latitude === 0 || longitude === 0) {
            console.log('Validation skipped: Missing or invalid data');
            messageDiv.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return true;
        }

        const location = locations[locationId];
        if (!location) {
            console.log('Location not found:', locationId);
            messageDiv.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return true;
        }

        const distance = calculateDistance(
            location.latitude,
            location.longitude,
            latitude,
            longitude
        );
        
        console.log('Distance calculated:', {
            distance: Math.round(distance),
            radius: location.radius,
            enforce_geofence: location.enforce_geofence
        });

        messageDiv.classList.remove('hidden');

        if (distance > location.radius) {
            if (location.enforce_geofence) {
                // Block submission
                messageDiv.className = 'p-4 mb-4 bg-red-50 border border-red-200 rounded-lg';
                messageDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-600 mt-1 mr-3"></i>
                        <div>
                            <p class="font-semibold text-red-800">Anda Berada di Luar Radius!</p>
                            <p class="text-sm text-red-700 mt-1">
                                Jarak Anda: <strong>${Math.round(distance)} meter</strong> dari lokasi <strong>${location.name}</strong>
                            </p>
                            <p class="text-sm text-red-700">
                                Radius maksimal: <strong>${location.radius} meter</strong>
                            </p>
                            <p class="text-xs text-red-600 mt-2">
                                <i class="fas fa-info-circle"></i> Anda harus berada dalam radius lokasi untuk melakukan check-in.
                            </p>
                        </div>
                    </div>
                `;
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                return false;
            } else {
                // Just warning
                messageDiv.className = 'p-4 mb-4 bg-yellow-50 border border-yellow-200 rounded-lg';
                messageDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div>
                            <p class="font-semibold text-yellow-800">Peringatan: Anda di Luar Radius</p>
                            <p class="text-sm text-yellow-700 mt-1">
                                Jarak Anda: <strong>${Math.round(distance)} meter</strong> dari lokasi <strong>${location.name}</strong>
                            </p>
                            <p class="text-sm text-yellow-700">
                                Radius normal: <strong>${location.radius} meter</strong>
                            </p>
                            <p class="text-xs text-yellow-600 mt-2">
                                <i class="fas fa-info-circle"></i> Check-in tetap dapat dilakukan, tapi akan ditandai berada di luar radius.
                            </p>
                        </div>
                    </div>
                `;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                return true;
            }
        } else {
            // Within radius - success
            messageDiv.className = 'p-4 mb-4 bg-green-50 border border-green-200 rounded-lg';
            messageDiv.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                    <div>
                        <p class="font-semibold text-green-800">Lokasi Valid</p>
                        <p class="text-sm text-green-700 mt-1">
                            Jarak Anda: <strong>${Math.round(distance)} meter</strong> dari lokasi <strong>${location.name}</strong>
                        </p>
                        <p class="text-xs text-green-600 mt-2">
                            <i class="fas fa-check"></i> Anda berada dalam radius yang diizinkan.
                        </p>
                    </div>
                </div>
            `;
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return true;
        }
    }

    // Compress image before upload
    function compressImage(file, maxSizeMB = 0.5) {
        return new Promise((resolve, reject) => {
            const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
            
            // If file is already small enough, return as is
            if (file.size <= maxSize) {
                resolve(file);
                return;
            }
            
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    
                    // Calculate new dimensions (max 1200px)
                    const maxDimension = 1200;
                    if (width > height && width > maxDimension) {
                        height = (height * maxDimension) / width;
                        width = maxDimension;
                    } else if (height > maxDimension) {
                        width = (width * maxDimension) / height;
                        height = maxDimension;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Start with quality 0.8 and reduce if needed
                    let quality = 0.8;
                    const tryCompress = () => {
                        canvas.toBlob((blob) => {
                            if (blob.size <= maxSize || quality <= 0.1) {
                                const compressedFile = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            } else {
                                quality -= 0.1;
                                tryCompress();
                            }
                        }, 'image/jpeg', quality);
                    };
                    tryCompress();
                };
                img.onerror = reject;
            };
            reader.onerror = reject;
        });
    }

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing validation...');
        
        // Add event listeners for location validation
        const locationSelect = document.querySelector('select[name="location_id"]');
        const latInput = document.querySelector('input[name="latitude"]');
        const lngInput = document.querySelector('input[name="longitude"]');
        
        if (locationSelect) {
            console.log('Location select found, adding listener');
            locationSelect.addEventListener('change', function() {
                console.log('Location changed to:', this.value);
                validateLocation();
            });
        }
        
        if (latInput) {
            console.log('Latitude input found, adding listener');
            latInput.addEventListener('input', validateLocation);
            latInput.addEventListener('change', validateLocation);
        }
        
        if (lngInput) {
            console.log('Longitude input found, adding listener');
            lngInput.addEventListener('input', validateLocation);
            lngInput.addEventListener('change', validateLocation);
        }
        
        // Prevent form submission if invalid
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateLocation()) {
                    e.preventDefault();
                    alert('Anda harus berada dalam radius lokasi yang dipilih untuk melakukan check-in.');
                    return false;
                }
            });
        }
    });

    // Handle photo input
    const photoInput = document.querySelector('input[name="photo"]');
    if (photoInput) {
        photoInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Show loading
            const form = this.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHTML = submitBtn.innerHTML;
            
            try {
                // Check if file is too large (more than 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengompres foto...';
                    
                    const compressedFile = await compressImage(file, 0.5);
                    
                    // Create new FileList with compressed file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    photoInput.files = dataTransfer.files;
                    
                    console.log('Original size:', (file.size / 1024 / 1024).toFixed(2), 'MB');
                    console.log('Compressed size:', (compressedFile.size / 1024 / 1024).toFixed(2), 'MB');
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHTML;
                }
            } catch (error) {
                console.error('Error compressing image:', error);
                alert('Gagal mengompres foto. Silakan coba lagi dengan foto yang lebih kecil.');
                photoInput.value = '';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHTML;
            }
        });
    }

    // Get current location if browser supports geolocation
    if (navigator.geolocation) {
        const latInput = document.querySelector('input[name="latitude"]');
        const lngInput = document.querySelector('input[name="longitude"]');
        
        // Button to get current location
        const container = latInput.closest('.grid');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'col-span-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2';
        button.innerHTML = '<i class="fas fa-location-arrow"></i><span>Dapatkan Lokasi Saya</span>';
        
        button.addEventListener('click', function() {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Mencari lokasi...</span>';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    latInput.value = position.coords.latitude;
                    lngInput.value = position.coords.longitude;
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i><span>Lokasi Berhasil Didapat!</span>';
                    button.className = 'col-span-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors flex items-center justify-center space-x-2';
                    
                    // Validate location after getting GPS
                    validateLocation();
                    
                    setTimeout(() => {
                        button.className = 'col-span-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2';
                        button.innerHTML = '<i class="fas fa-location-arrow"></i><span>Dapatkan Lokasi Saya</span>';
                    }, 2000);
                },
                function(error) {
                    alert('Tidak dapat mengakses lokasi: ' + error.message);
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-location-arrow"></i><span>Dapatkan Lokasi Saya</span>';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
        
        container.appendChild(button);
    }
</script>
@endpush
