@extends('layouts.admin')

@section('title', 'Detail Lokasi')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center space-x-3">
            <x-button 
                variant="secondary" 
                size="sm"
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.master.locations.index') }}'">
            </x-button>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detail Lokasi</h1>
                <p class="text-sm text-gray-600 mt-1">Informasi lengkap lokasi</p>
            </div>
        </div>
        <div class="flex gap-2">
            <x-button 
                variant="warning"
                icon="fas fa-edit"
                size="sm"
                onclick="window.location.href='{{ route('admin.master.locations.edit', $location->id) }}'">
                Edit
            </x-button>
            <form action="{{ route('admin.master.locations.destroy', $location->id) }}" method="POST" 
                  onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
                @csrf
                @method('DELETE')
                <x-button 
                    variant="danger"
                    icon="fas fa-trash"
                    size="sm"
                    type="submit">
                    Hapus
                </x-button>
            </form>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stats-card 
            title="Status" 
            :value="$location->is_active ? 'Aktif' : 'Nonaktif'"
            :color="$location->is_active ? 'green' : 'red'"
            icon="fas fa-power-off" />
        
        <x-stats-card 
            title="Geofence" 
            :value="$location->enforce_geofence ? 'Aktif' : 'Nonaktif'"
            :color="$location->enforce_geofence ? 'blue' : 'gray'"
            icon="fas fa-map-marked-alt" />
        
        <x-stats-card 
            title="Radius" 
            :value="$location->radius . ' meter'"
            color="purple"
            icon="fas fa-bullseye" />
    </div>

    {{-- Location Information --}}
    <x-card title="Informasi Lokasi">
        <div class="space-y-4">
            <div class="flex items-start space-x-3 pb-4 border-b">
                <div class="flex-shrink-0 h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-green-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900">{{ $location->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">ID: {{ $location->id }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">
                        <i class="fas fa-map-pin mr-2 text-gray-400"></i>Alamat
                    </p>
                    <p class="text-base text-gray-900">{{ $location->address ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">
                        <i class="fas fa-toggle-on mr-2 text-gray-400"></i>Status Aktif
                    </p>
                    <x-badge :color="$location->is_active ? 'success' : 'danger'">
                        {{ $location->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </div>
            </div>
        </div>
    </x-card>

    {{-- GPS Coordinates --}}
    <x-card title="Koordinat GPS">
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">
                        <i class="fas fa-compass mr-2 text-gray-400"></i>Latitude
                    </p>
                    <p class="text-base font-mono text-gray-900">{{ $location->latitude }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">
                        <i class="fas fa-compass mr-2 text-gray-400"></i>Longitude
                    </p>
                    <p class="text-base font-mono text-gray-900">{{ $location->longitude }}</p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Koordinat: {{ $location->latitude }}, {{ $location->longitude }}
                </p>
                <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" 
                   target="_blank"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium mt-2 inline-block">
                    <i class="fas fa-external-link-alt mr-1"></i>Lihat di Google Maps
                </a>
            </div>
        </div>
    </x-card>

    {{-- Geofence Settings --}}
    <x-card title="Pengaturan Geofence">
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">
                        <i class="fas fa-bullseye mr-2 text-gray-400"></i>Radius
                    </p>
                    <p class="text-base text-gray-900">{{ $location->radius }} meter</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">
                        <i class="fas fa-shield-alt mr-2 text-gray-400"></i>Paksa Geofence
                    </p>
                    <x-badge :color="$location->enforce_geofence ? 'success' : 'secondary'">
                        {{ $location->enforce_geofence ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </div>
            </div>

            @if($location->enforce_geofence)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Geofence diaktifkan. Pegawai hanya dapat melakukan absensi dalam radius {{ $location->radius }} meter dari koordinat lokasi.
                </p>
            </div>
            @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Geofence tidak diaktifkan. Pegawai dapat melakukan absensi dari mana saja.
                </p>
            </div>
            @endif
        </div>
    </x-card>

    {{-- Metadata --}}
    <x-card title="Informasi Sistem">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">
                    <i class="fas fa-calendar-plus mr-2 text-gray-400"></i>Dibuat Pada
                </p>
                <p class="text-base text-gray-900">{{ $location->created_at->format('d F Y, H:i') }}</p>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">
                    <i class="fas fa-calendar-edit mr-2 text-gray-400"></i>Terakhir Diubah
                </p>
                <p class="text-base text-gray-900">{{ $location->updated_at->format('d F Y, H:i') }}</p>
            </div>
        </div>
    </x-card>
</div>
@endsection
