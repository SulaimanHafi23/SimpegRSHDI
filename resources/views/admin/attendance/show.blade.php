@extends('layouts.admin')

@section('title', 'Detail Absensi')

@section('content')
<div class="space-y-6">
    {{-- Page Header with Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-check mr-3 text-blue-600"></i>
                    Detail Absensi
                </h1>
                <p class="text-sm text-gray-600 mt-1">Informasi lengkap data absensi pegawai</p>
            </div>
        <div class="flex space-x-2 w-full sm:w-auto">
            @if(!$attendance->check_out)
                <button onclick="document.getElementById('checkout-modal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition duration-200 shadow-md">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Check Out
                </button>
            @endif
            <a href="{{ route('admin.attendance.edit', $attendance->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
            @can('delete-attendance')
                <button onclick="if(confirm('Yakin ingin menghapus data absensi ini?')) document.getElementById('delete-form').submit()"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200 shadow-md">
                    <i class="fas fa-trash mr-2"></i>
                    Hapus
                </button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Worker Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Worker Profile --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col items-center">
                    @if($attendance->worker->photo_url && Storage::disk('public')->exists($attendance->worker->photo_url))
                        <img src="{{ asset('storage/' . $attendance->worker->photo_url) }}"
                             alt="{{ $attendance->worker->name }}"
                             class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-blue-500 object-cover mb-4">
                    @else
                        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-blue-500 overflow-hidden bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fas fa-user text-4xl sm:text-5xl text-gray-400"></i>
                        </div>
                    @endif
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 text-center mb-2">{{ $attendance->worker->name }}</h2>
                    <p class="text-sm text-gray-600">{{ $attendance->worker->nip }}</p>

                    @php
                        $statusConfig = [
                            'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir'],
                            'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat'],
                            'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir'],
                            'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit'],
                            'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin'],
                            'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti'],
                        ];
                        $status = $statusConfig[$attendance->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($attendance->status)];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }} mt-2">
                        {{ $status['label'] }}
                    </span>
                </div>
                <div class="mt-6 space-y-3 text-center border-t pt-4">
                    <div class="py-2">
                        <p class="text-sm text-gray-600">Departemen</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->worker->department->name ?? '-' }}</p>
                    </div>
                    <div class="py-2 border-t">
                        <p class="text-sm text-gray-600">Shift</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->shift->name ?? '-' }}</p>
                        @if($attendance->shift)
                            <p class="text-xs text-gray-500">{{ $attendance->shift->start_time }} - {{ $attendance->shift->end_time }}</p>
                        @endif
                    </div>
                    <div class="py-2 border-t">
                        <p class="text-sm text-gray-600">Lokasi</p>
                        <p class="font-semibold text-gray-900">{{ config('attendance.location.name', '-') }}</p>
                    </div>
                </div>
            </div>

            {{-- Status Info --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Absensi</h3>
                <div class="space-y-3">
                    @if($attendance->is_late)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-clock text-yellow-600"></i>
                                <span class="text-sm text-yellow-900">Terlambat</span>
                            </div>
                            <span class="font-semibold text-yellow-900">{{ $attendance->late_minutes }} menit</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                <span class="text-sm text-green-900">Tepat Waktu</span>
                            </div>
                        </div>
                    @endif

                    @if($attendance->is_early_leave)
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-door-open text-orange-600"></i>
                                <span class="text-sm text-orange-900">Pulang Cepat</span>
                            </div>
                            <span class="font-semibold text-orange-900">{{ $attendance->early_leave_minutes }} menit</span>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Right Column - Attendance Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Check In Info --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-sign-in-alt text-green-600 mr-2"></i>
                        Check In
                    </span>
                    @if($attendance->check_in_by_admin)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-user-shield mr-1"></i>
                            Oleh Admin
                        </span>
                    @endif
                </h3>

                {{-- Admin Info if by admin --}}
                @if($attendance->check_in_by_admin && $attendance->checkInAdmin)
                    <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                            <span class="text-sm text-purple-800">
                                Check-in dilakukan oleh <strong>{{ $attendance->checkInAdmin->name }}</strong>
                            </span>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Tanggal</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->attendance_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Waktu</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->check_in?->format('H:i:s') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Jarak dari Lokasi</p>
                        <p class="font-semibold text-gray-900">{{ $attendance->distance_check_in ?? 0 }} meter</p>
                    </div>
                </div>

                {{-- Check In Photos --}}
                @if($attendance->checkInPhoto->count() > 0)
                    <div class="mt-4">
                        <p class="text-sm text-gray-600 mb-2">Foto Check In</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($attendance->checkInPhoto as $photo)
                                @if($photo->photo_path && Storage::disk('public')->exists($photo->photo_path))
                                    <div class="relative">
                                        <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                             alt="Check In Photo"
                                             class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75"
                                             onclick="window.open('{{ asset('storage/' . $photo->photo_path) }}', '_blank')">
                                        @if($attendance->check_in_by_admin)
                                            <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-purple-600 text-white text-xs rounded">
                                                Admin
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Check Out Info --}}
            @if($attendance->check_out)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                            Check Out
                        </span>
                        @if($attendance->check_out_by_admin)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-user-shield mr-1"></i>
                                Oleh Admin
                            </span>
                        @endif
                    </h3>

                    {{-- Admin Info if by admin --}}
                    @if($attendance->check_out_by_admin && $attendance->checkOutAdmin)
                        <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                                <span class="text-sm text-purple-800">
                                    Check-out dilakukan oleh <strong>{{ $attendance->checkOutAdmin->name }}</strong>
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Tanggal</p>
                            <p class="font-semibold text-gray-900">{{ $attendance->check_out?->format('d F Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Waktu</p>
                            <p class="font-semibold text-gray-900">{{ $attendance->check_out?->format('H:i:s') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jarak dari Lokasi</p>
                            <p class="font-semibold text-gray-900">{{ $attendance->distance_check_out ?? 0 }} meter</p>
                        </div>
                    </div>

                    {{-- Check Out Photos --}}
                    @if($attendance->checkOutPhoto->count() > 0)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 mb-2">Foto Check Out</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($attendance->checkOutPhoto as $photo)
                                    @if($photo->photo_path && Storage::disk('public')->exists($photo->photo_path))
                                        <div class="relative">
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                                 alt="Check Out Photo"
                                                 class="w-full h-32 object-cover rounded-lg border cursor-pointer hover:opacity-75"
                                                 onclick="window.open('{{ asset('storage/' . $photo->photo_path) }}', '_blank')">
                                            @if($attendance->check_out_by_admin)
                                                <span class="absolute top-1 right-1 px-1.5 py-0.5 bg-purple-600 text-white text-xs rounded">
                                                    Admin
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center py-8">
                        <i class="fas fa-clock text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">Belum Check Out</p>
                        <p class="text-sm text-gray-500 mt-1">Pegawai belum melakukan check out</p>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            @if($attendance->notes)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h3>
                    <p class="text-gray-700">{{ $attendance->notes }}</p>
                </div>
            @endif

            {{-- Working Hours Summary --}}
            @if($attendance->check_in && $attendance->check_out)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Jam Kerja</h3>
                    @php
                        $workingHours = $attendance->check_in->diffInMinutes($attendance->check_out);
                        $hours = floor($workingHours / 60);
                        $minutes = $workingHours % 60;
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-600">Total Jam Kerja</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $hours }}j {{ $minutes }}m</p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <p class="text-sm text-gray-600">Terlambat</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $attendance->late_minutes ?? 0 }}m</p>
                        </div>
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <p class="text-sm text-gray-600">Pulang Cepat</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $attendance->early_leave_minutes ?? 0 }}m</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Delete Form --}}
@can('delete-attendance')
    <form id="delete-form" action="{{ route('admin.attendance.destroy', $attendance->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endcan

{{-- Check Out Modal --}}
@if(!$attendance->check_out)
<div id="checkout-modal" class="hidden fixed inset-0 backdrop-blur-sm bg-white/30 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) document.getElementById('checkout-modal').classList.add('hidden')">
    <div class="bg-white rounded-lg max-w-md w-full p-6" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Check Out</h3>
        <form action="{{ route('admin.attendance.check-out', $attendance->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <x-form.select
                name="location_id"
                label="Lokasi"
                disabled>
                @php
                    $singleLocation = $locations->count() === 1 ? $locations->first() : null;
                    $defaultLocationId = old('location_id', $singleLocation?->id);
                @endphp
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ $defaultLocationId == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </x-form.select>

            <div class="grid grid-cols-2 gap-4" id="checkout-coordinates">
                <x-form.input
                    name="latitude"
                    label="Latitude"
                    type="number"
                    step="any"
                    value="0"
                    required />

                <x-form.input
                    name="longitude"
                    label="Longitude"
                    type="number"
                    step="any"
                    value="0"
                    required />
            </div>

            <button type="button" id="get-checkout-location" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2 mb-4">
                <i class="fas fa-location-arrow"></i>
                <span>Dapatkan Lokasi Saya</span>
            </button>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto (Opsional)</label>
                <input type="file"
                       name="photo"
                       accept="image/jpeg,image/jpg,image/png"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       id="checkout-photo-input">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG (Max: 2MB)</p>

                <div id="checkout-photo-preview" class="hidden mt-2">
                    <img src="" alt="Preview" class="max-w-full h-32 rounded-md object-cover">
                </div>
            </div>

            <div id="checkout-location-validation" class="hidden"></div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <p class="text-sm text-red-800">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ $errors->first() }}
                </p>
            </div>
            @endif

            <div class="flex justify-end gap-3 mt-6">
                <x-button
                    variant="secondary"
                    type="button"
                    onclick="document.getElementById('checkout-modal').classList.add('hidden')">
                    Batal
                </x-button>
                <x-button
                    id="submit-checkout-btn"
                    variant="danger"
                    icon="fas fa-sign-out-alt"
                    type="submit">
                    Check Out
                </x-button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
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

    // Get GPS Location for Check Out
    document.addEventListener('DOMContentLoaded', function() {
        // Location validation for checkout
        const checkoutLocations = {!! json_encode($locationsData) !!};

        function calculateCheckoutDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Earth radius in meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function validateCheckoutLocation() {
            const locationSelect = document.querySelector('#checkout-modal select[name="location_id"]');
            const latInput = document.querySelector('#checkout-coordinates input[name="latitude"]');
            const lngInput = document.querySelector('#checkout-coordinates input[name="longitude"]');
            const messageDiv = document.getElementById('checkout-location-validation');
            const submitBtn = document.getElementById('submit-checkout-btn');

            if (!locationSelect || !latInput || !lngInput || !messageDiv || !submitBtn) {
                return true;
            }

            const locationId = locationSelect.value;
            const latitude = parseFloat(latInput.value);
            const longitude = parseFloat(lngInput.value);

            // Hide message if no location selected or invalid coordinates
            if (!locationId || isNaN(latitude) || isNaN(longitude) || latitude === 0 || longitude === 0) {
                messageDiv.classList.add('hidden');
                submitBtn.disabled = false;
                return true;
            }

            const location = checkoutLocations[locationId];
            if (!location) {
                messageDiv.classList.add('hidden');
                submitBtn.disabled = false;
                return true;
            }

            const distance = calculateCheckoutDistance(
                location.latitude,
                location.longitude,
                latitude,
                longitude
            );

            const distanceFormatted = distance >= 1000
                ? (distance / 1000).toFixed(2) + ' km'
                : Math.round(distance) + ' m';
            const radiusFormatted = location.radius >= 1000
                ? (location.radius / 1000).toFixed(2) + ' km'
                : location.radius + ' m';

            if (distance > location.radius) {
                if (location.enforce_geofence) {
                    // Block submission - outside radius with geofence enforced
                    messageDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-md p-3 mt-3">
                            <p class="text-sm text-red-800">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <strong>Lokasi Tidak Valid!</strong> Anda berada ${distanceFormatted} dari lokasi <strong>${location.name}</strong>.
                                Radius maksimal: ${radiusFormatted}. Check out tidak dapat dilakukan.
                            </p>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');
                    submitBtn.disabled = true;
                    return false;
                } else {
                    // Warning only - outside radius but geofence not enforced
                    messageDiv.innerHTML = `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mt-3">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Peringatan:</strong> Anda berada ${distanceFormatted} dari lokasi <strong>${location.name}</strong>.
                                Radius maksimal: ${radiusFormatted}. Check out dapat dilakukan tetapi akan dicatat sebagai di luar radius.
                            </p>
                        </div>
                    `;
                    messageDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    return true;
                }
            } else {
                // Within radius - success
                messageDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded-md p-3 mt-3">
                        <p class="text-sm text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Lokasi Valid! Anda berada ${distanceFormatted} dari lokasi <strong>${location.name}</strong>
                            (dalam radius ${radiusFormatted}).
                        </p>
                    </div>
                `;
                messageDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                return true;
            }
        }

        // Add event listeners for checkout location validation
        const checkoutLocationSelect = document.querySelector('#checkout-modal select[name="location_id"]');
        const checkoutLatInput = document.querySelector('#checkout-coordinates input[name="latitude"]');
        const checkoutLngInput = document.querySelector('#checkout-coordinates input[name="longitude"]');
        const checkoutForm = document.querySelector('#checkout-modal form');

        if (checkoutLocationSelect) {
            checkoutLocationSelect.addEventListener('change', validateCheckoutLocation);
        }
        if (checkoutLatInput) {
            checkoutLatInput.addEventListener('change', validateCheckoutLocation);
        }
        if (checkoutLngInput) {
            checkoutLngInput.addEventListener('change', validateCheckoutLocation);
        }

        // Prevent form submission if location is invalid
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                if (!validateCheckoutLocation()) {
                    e.preventDefault();
                    alert('Anda harus berada dalam radius lokasi yang dipilih untuk melakukan check out.');
                }
            });
        }

        // Handle photo compression in checkout modal
        const checkoutPhotoInput = document.getElementById('checkout-photo-input');
        const checkoutPhotoPreview = document.getElementById('checkout-photo-preview');

        if (checkoutPhotoInput) {
            checkoutPhotoInput.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) {
                    if (checkoutPhotoPreview) {
                        checkoutPhotoPreview.classList.add('hidden');
                    }
                    return;
                }

                const modal = document.getElementById('checkout-modal');
                const submitBtn = modal.querySelector('button[type="submit"]');
                const originalBtnHTML = submitBtn ? submitBtn.innerHTML : '';

                try {
                    // Validate file type
                    if (!file.type.match(/^image\/(jpeg|jpg|png)$/i)) {
                        alert('Format file tidak valid. Gunakan JPG, JPEG, atau PNG.');
                        checkoutPhotoInput.value = '';
                        return;
                    }

                    // Check if file is too large (more than 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengompres foto...';
                        }

                        const compressedFile = await compressImage(file, 0.5);

                        // Validate compressed file
                        if (!compressedFile || compressedFile.size === 0) {
                            throw new Error('File terkompresi tidak valid');
                        }

                        // Create new FileList with compressed file
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        checkoutPhotoInput.files = dataTransfer.files;

                        console.log('Original size:', (file.size / 1024 / 1024).toFixed(2), 'MB');
                        console.log('Compressed size:', (compressedFile.size / 1024 / 1024).toFixed(2), 'MB');

                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHTML;
                        }
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (checkoutPhotoPreview) {
                            const img = checkoutPhotoPreview.querySelector('img');
                            if (img) {
                                img.src = e.target.result;
                                checkoutPhotoPreview.classList.remove('hidden');
                            }
                        }
                    };
                    reader.readAsDataURL(checkoutPhotoInput.files[0]);

                } catch (error) {
                    console.error('Error compressing image:', error);
                    alert('Gagal mengompres foto. Silakan coba lagi dengan foto yang lebih kecil.');
                    checkoutPhotoInput.value = '';
                    if (checkoutPhotoPreview) {
                        checkoutPhotoPreview.classList.add('hidden');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHTML;
                    }
                }
            });
        }

        const getLocationBtn = document.getElementById('get-checkout-location');

        if (getLocationBtn && navigator.geolocation) {
            getLocationBtn.addEventListener('click', function() {
                const latInput = document.querySelector('#checkout-coordinates input[name="latitude"]');
                const lngInput = document.querySelector('#checkout-coordinates input[name="longitude"]');

                // Disable button and show loading
                getLocationBtn.disabled = true;
                getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span class="ml-2">Mencari lokasi...</span>';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Success - set coordinates
                        latInput.value = position.coords.latitude;
                        lngInput.value = position.coords.longitude;

                        // Validate location after getting GPS coordinates
                        validateCheckoutLocation();

                        // Update button to success state
                        getLocationBtn.disabled = false;
                        getLocationBtn.className = 'w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors flex items-center justify-center space-x-2 mb-4';
                        getLocationBtn.innerHTML = '<i class="fas fa-check"></i><span class="ml-2">Lokasi Berhasil Didapat!</span>';

                        // Reset button after 2 seconds
                        setTimeout(() => {
                            getLocationBtn.className = 'w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2 mb-4';
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
                        getLocationBtn.className = 'w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2 mb-4';
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
