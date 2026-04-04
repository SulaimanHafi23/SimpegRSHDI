@extends('layouts.admin')

@section('title', 'Edit Absensi')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Edit Absensi</h1>
        <p class="text-sm text-gray-600 mt-1">Perbarui data absensi pegawai</p>
    </div>


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
                            <p class="text-gray-600">Koordinat GPS hanya digunakan saat validasi. Sistem menyimpan jarak absensi (meter).</p>
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
