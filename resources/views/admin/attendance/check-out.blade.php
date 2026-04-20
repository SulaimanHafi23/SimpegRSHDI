    @extends('layouts.admin')

@section('title', 'Check Out')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Check Out</h1>
        <p class="text-sm text-gray-600 mt-1">Catat waktu pulang pegawai</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Informasi Pegawai --}}
        <x-card title="Informasi Absensi">
            @php
                $effectiveShift = $shiftInfo['shift'] ?? null;
                $effectiveSchedule = $shiftInfo['schedule'] ?? null;
                $shiftSource = $shiftInfo['source'] ?? 'none';
            @endphp
            <div class="space-y-4">
                {{-- Profile Section --}}
                <div class="flex items-center space-x-4">
                    @if($attendance->worker->photo)
                        <img src="{{ Storage::url($attendance->worker->photo) }}"
                             alt="{{ $attendance->worker->name }}"
                             class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">
                                {{ strtoupper(substr($attendance->worker->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $attendance->worker->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $attendance->worker->nip }}</p>
                        @if($attendance->worker->department)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                {{ $attendance->worker->department->name }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Departemen</label>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            {{ $attendance->worker->department->name ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Status</label>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $attendance->worker->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $attendance->worker->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </p>
                    </div>
                </div>

                @if(is_object($effectiveShift) && is_array($effectiveSchedule))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-clock text-blue-600 mt-0.5 mr-3"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-800">Shift Efektif Hari Ini</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-lg font-bold text-blue-900">{{ $effectiveShift->name }}</p>
                                    @if($shiftSource === 'shift_swap')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Tukar Shift</span>
                                    @elseif($shiftSource === 'override')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">Override</span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-4 mt-2 text-sm text-blue-700">
                                    <span>
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        Masuk: {{ \Carbon\Carbon::parse($effectiveSchedule['start_time'])->format('H:i') }}
                                    </span>
                                    <span>
                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                        Pulang: {{ \Carbon\Carbon::parse($effectiveSchedule['end_time'])->format('H:i') }}
                                    </span>
                                </div>
                                @if($shiftSource === 'shift_swap' && !empty($shiftInfo['swap_with_name']))
                                    <p class="text-xs text-purple-700 mt-2">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        Jam ini berasal dari tukar shift dengan {{ $shiftInfo['swap_with_name'] }}.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($attendance->is_late)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Terlambat</p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    {{ floor($attendance->late_minutes / 60) > 0 ? floor($attendance->late_minutes / 60) . ' jam ' : '' }}
                                    {{ $attendance->late_minutes % 60 }} menit
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($attendance->photo)
                    <div>
                        <label class="text-sm font-medium text-gray-700">Foto Check In</label>
                        <img src="{{ Storage::url($attendance->photo) }}"
                             alt="Check In Photo"
                             class="mt-2 rounded-lg max-h-48 object-cover">
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Check Out Form --}}
        <x-card title="Form Check Out">
            <form action="{{ route('admin.attendance.check-out', $attendance->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                    <p class="font-semibold mb-1">Info Checkout Admin</p>
                    <p>Checkout dari halaman ini akan ditandai sebagai checkout oleh admin.</p>
                </div>

                <div class="rounded-lg border border-purple-200 bg-purple-50 p-3 text-sm text-purple-900">
                    <p class="font-semibold mb-1">Admin Pelaksana</p>
                    <p>
                        Check-out ini akan dilakukan oleh
                        <strong>{{ auth()->user()->name }}</strong>.
                    </p>
                </div>

                <div>
                    <label for="admin_checkout_note" class="block text-sm font-medium text-gray-700 mb-1">
                        Keterangan Admin <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="admin_checkout_note"
                        name="admin_checkout_note"
                        rows="3"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('admin_checkout_note') border-red-500 @enderror"
                        placeholder="Contoh: Pegawai lupa checkout dan sudah dikonfirmasi atasan.">{{ old('admin_checkout_note') }}</textarea>
                    @error('admin_checkout_note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-form.file
                    name="photo"
                    label="Foto (Opsional)"
                    accept="image/*"
                    :error="$errors->first('photo')">
                    <x-slot name="help">
                        Format: JPG, JPEG, PNG. Maksimal 2MB
                    </x-slot>
                </x-form.file>

                {{-- Tombol Submit --}}
                <div class="flex gap-3 pt-4">
                    <x-button
                        type="button"
                        variant="secondary"
                        onclick="window.location.href='{{ route('admin.attendance.index') }}'"
                        class="flex-1">
                        Batal
                    </x-button>
                    <x-button
                        type="submit"
                        variant="primary"
                        class="flex-1">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Check Out
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
    // Tidak ada script lokasi yang digunakan pada form ini
</script>
@endpush
@endsection
