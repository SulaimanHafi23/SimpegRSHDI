@extends('layouts.admin')

@section('title', 'Edit Absensi')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center space-x-3">
        <x-button 
            variant="secondary" 
            size="sm"
            icon="fas fa-arrow-left"
            onclick="window.location.href='{{ route('admin.attendance.show', $attendance->id) }}'">
        </x-button>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Edit Absensi</h1>
            <p class="text-sm text-gray-600 mt-1">Perbarui data absensi pegawai</p>
        </div>
    </div>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Left Column --}}
            <div class="space-y-6">
                {{-- Worker & Date Info --}}
                <x-card title="Informasi Dasar">
                    <div class="space-y-4">
                        <x-form.select 
                            name="worker_id" 
                            label="Pegawai"
                            required 
                            :error="$errors->first('worker_id')">
                            <option value="">Pilih Pegawai</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ old('worker_id', $attendance->worker_id) == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->nip }} - {{ $worker->name }}
                                </option>
                            @endforeach
                        </x-form.select>

                        <x-form.input 
                            name="date" 
                            label="Tanggal" 
                            type="date"
                            :value="old('date', $attendance->attendance_date?->format('Y-m-d'))"
                            required 
                            :error="$errors->first('date')" />

                        <x-form.select 
                            name="status" 
                            label="Status"
                            required 
                            :error="$errors->first('status')">
                            <option value="">Pilih Status</option>
                            <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="leave" {{ old('status', $attendance->status) == 'leave' ? 'selected' : '' }}>Cuti</option>
                            <option value="sick" {{ old('status', $attendance->status) == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="permission" {{ old('status', $attendance->status) == 'permission' ? 'selected' : '' }}>Izin</option>
                        </x-form.select>

                        <x-form.textarea 
                            name="notes" 
                            label="Catatan (Opsional)" 
                            rows="3" 
                            :value="old('notes', $attendance->notes)"
                            :error="$errors->first('notes')"
                            placeholder="Tambahkan catatan jika diperlukan" />
                    </div>
                </x-card>

                {{-- Check In Info --}}
                <x-card title="Data Check In">
                    <div class="space-y-4">
                        <x-form.input 
                            name="check_in" 
                            label="Waktu Check In" 
                            type="datetime-local"
                            :value="old('check_in', $attendance->check_in?->format('Y-m-d\TH:i'))"
                            required 
                            :error="$errors->first('check_in')" />

                        <div class="grid grid-cols-2 gap-4">
                            <x-form.input 
                                name="latitude_in" 
                                label="Latitude" 
                                type="number"
                                step="0.00000001"
                                :value="old('latitude_in', $attendance->check_in_latitude)"
                                :error="$errors->first('latitude_in')"
                                placeholder="Contoh: -6.200000" />

                            <x-form.input 
                                name="longitude_in" 
                                label="Longitude" 
                                type="number"
                                step="0.00000001"
                                :value="old('longitude_in', $attendance->check_in_longitude)"
                                :error="$errors->first('longitude_in')"
                                placeholder="Contoh: 106.816666" />
                        </div>

                        <x-form.file 
                            name="photo_in" 
                            label="Foto Check In (Opsional)"
                            accept="image/*"
                            preview
                            help="Format: JPG, PNG (Max: 2MB) - Kosongkan jika tidak ingin mengubah" />

                        @if($attendance->checkInPhoto->count() > 0)
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 mb-2">Foto Saat Ini:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach($attendance->checkInPhoto as $photo)
                                        @if($photo->photo_path && Storage::disk('public')->exists($photo->photo_path))
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}" 
                                                 alt="Check In" 
                                                 class="w-full h-20 object-cover rounded border">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Check Out Info --}}
                <x-card title="Data Check Out">
                    <div class="space-y-4">
                        <x-form.input 
                            name="check_out" 
                            label="Waktu Check Out (Opsional)" 
                            type="datetime-local"
                            :value="old('check_out', $attendance->check_out?->format('Y-m-d\TH:i'))"
                            :error="$errors->first('check_out')"
                            help="Kosongkan jika belum check out" />

                        <div class="grid grid-cols-2 gap-4">
                            <x-form.input 
                                name="latitude_out" 
                                label="Latitude" 
                                type="number"
                                step="0.00000001"
                                :value="old('latitude_out', $attendance->check_out_latitude)"
                                :error="$errors->first('latitude_out')"
                                placeholder="Contoh: -6.200000" />

                            <x-form.input 
                                name="longitude_out" 
                                label="Longitude" 
                                type="number"
                                step="0.00000001"
                                :value="old('longitude_out', $attendance->check_out_longitude)"
                                :error="$errors->first('longitude_out')"
                                placeholder="Contoh: 106.816666" />
                        </div>

                        <x-form.file 
                            name="photo_out" 
                            label="Foto Check Out (Opsional)"
                            accept="image/*"
                            preview
                            help="Format: JPG, PNG (Max: 2MB) - Kosongkan jika tidak ingin mengubah" />

                        @if($attendance->checkOutPhoto->count() > 0)
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 mb-2">Foto Saat Ini:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach($attendance->checkOutPhoto as $photo)
                                        @if($photo->photo_path && Storage::disk('public')->exists($photo->photo_path))
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}" 
                                                 alt="Check Out" 
                                                 class="w-full h-20 object-cover rounded border">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>

                {{-- Info Card --}}
                <x-card title="Informasi">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <p class="text-gray-600">Pastikan waktu check in lebih awal dari check out.</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-camera text-purple-500 mt-0.5"></i>
                            <p class="text-gray-600">Foto bersifat opsional. Kosongkan jika tidak ingin mengubah foto yang ada.</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-map-marker-alt text-green-500 mt-0.5"></i>
                            <p class="text-gray-600">Koordinat GPS digunakan untuk validasi lokasi absensi.</p>
                        </div>
                    </div>
                </x-card>

                {{-- Current Info Display --}}
                <x-card title="Data Saat Ini">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Pegawai:</span>
                            <span class="font-semibold">{{ $attendance->worker->name }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Tanggal:</span>
                            <span class="font-semibold">{{ $attendance->attendance_date?->format('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Check In:</span>
                            <span class="font-semibold">{{ $attendance->check_in?->format('H:i:s') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Check Out:</span>
                            <span class="font-semibold">{{ $attendance->check_out?->format('H:i:s') ?? 'Belum' }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Status:</span>
                            @php
                                $statusLabels = [
                                    'present' => 'Hadir',
                                    'late' => 'Terlambat',
                                    'absent' => 'Tidak Hadir',
                                    'leave' => 'Cuti',
                                    'sick' => 'Sakit',
                                    'permission' => 'Izin',
                                ];
                            @endphp
                            <span class="font-semibold">{{ $statusLabels[$attendance->status] ?? $attendance->status }}</span>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                <x-button 
                    variant="secondary"
                    onclick="window.location.href='{{ route('admin.attendance.show', $attendance->id) }}'">
                    Batal
                </x-button>
                <x-button 
                    variant="success" 
                    icon="fas fa-save"
                    type="submit">
                    Update
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
