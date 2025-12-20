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
<script>
    // Get GPS Location
    document.addEventListener('DOMContentLoaded', function() {
        const getLocationBtn = document.getElementById('get-location-btn');
        
        if (getLocationBtn && navigator.geolocation) {
            getLocationBtn.addEventListener('click', function() {
                const latInput = document.querySelector('#location-coordinates input[name="latitude"]');
                const lngInput = document.querySelector('#location-coordinates input[name="longitude"]');
                
                // Disable button and show loading
                getLocationBtn.disabled = true;
                getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span class="ml-2">Mencari lokasi...</span>';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Success - set coordinates
                        latInput.value = position.coords.latitude;
                        lngInput.value = position.coords.longitude;
                        
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
