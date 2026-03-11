@extends('layouts.admin')

@section('title', 'Edit Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Edit Data Pegawai</h1>
        <p class="text-sm text-gray-600 mt-1">Perbarui informasi pegawai dan pastikan data profil tetap lengkap.</p>
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

    <form action="{{ route('admin.workers.update', $worker->id ?? 1) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Photo Upload --}}
        <x-card title="Foto Pegawai">
            @php
                $workerPhotoUrl = ($worker->photo_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo_url))
                    ? \Illuminate\Support\Facades\Storage::url($worker->photo_url)
                    : null;
            @endphp
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
                <div class="shrink-0">
                    @if($workerPhotoUrl)
                        <img id="worker-photo-preview" src="{{ $workerPhotoUrl }}" alt="{{ $worker->name }}" class="h-24 w-24 rounded-full border-4 border-green-100 object-cover shadow-sm sm:h-28 sm:w-28">
                    @else
                        <div id="worker-photo-placeholder" class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-dashed border-green-200 bg-green-50 text-2xl font-semibold text-green-700 shadow-sm sm:h-28 sm:w-28">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($worker->name ?? 'P', 0, 1)) }}
                        </div>
                        <img id="worker-photo-preview" src="" alt="Preview Foto" class="hidden h-24 w-24 rounded-full border-4 border-green-100 object-cover shadow-sm sm:h-28 sm:w-28">
                    @endif
                </div>
                <div class="flex-1">
                    <label for="photo" class="mb-2 block text-sm font-medium text-gray-700">Ganti Foto Profil</label>
                    <input type="file" name="photo" id="photo" accept="image/*"
                           class="block w-full cursor-pointer rounded-xl border border-gray-300 bg-gray-50 text-sm text-gray-900 file:mr-4 file:rounded-l-xl file:border-0 file:bg-green-600 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-white hover:file:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                           onchange="previewWorkerPhoto(this)">
                    <p class="mt-2 text-sm text-gray-500">Format JPG atau PNG, maksimal 2MB. Kosongkan jika tidak ingin mengubah foto saat ini.</p>
                    @error('photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-card>

        {{-- Personal Information --}}
        <x-card title="Data Pribadi">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.input
                    name="nip"
                    label="NIP"
                    :value="old('nip', $worker->nip ?? '')"
                    required
                    :error="$errors->first('nip')"
                    placeholder="Masukkan NIP" />

                <x-form.input
                    name="name"
                    label="Nama Lengkap"
                    :value="old('name', $worker->name ?? '')"
                    required
                    :error="$errors->first('name')"
                    placeholder="Masukkan nama lengkap" />

                <x-form.input
                    name="birth_place"
                    label="Tempat Lahir"
                    :value="old('birth_place', $worker->birth_place ?? '')"
                    required
                    :error="$errors->first('birth_place')"
                    placeholder="Masukkan tempat lahir" />

                <x-form.input
                    name="birth_date"
                    label="Tanggal Lahir"
                    type="date"
                    :value="old('birth_date', $worker->birth_date?->format('Y-m-d') ?? '')"
                    required
                    max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                    :error="$errors->first('birth_date')" />

                <x-form.select
                    name="gender_id"
                    label="Jenis Kelamin"
                    required
                    :error="$errors->first('gender_id')">
                    <option value="">Pilih Jenis Kelamin</option>
                    @foreach($genders as $gender)
                        <option value="{{ $gender->id }}" {{ old('gender_id', $worker->gender_id ?? '') == $gender->id ? 'selected' : '' }}>
                            {{ $gender->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    name="religion_id"
                    label="Agama"
                    required
                    :error="$errors->first('religion_id')">
                    <option value="">Pilih Agama</option>
                    @foreach($religions as $religion)
                        <option value="{{ $religion->id }}" {{ old('religion_id', $worker->religion_id ?? '') == $religion->id ? 'selected' : '' }}>
                            {{ $religion->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <div class="md:col-span-2">
                    <x-form.textarea
                        name="address"
                        label="Alamat"
                        rows="3"
                        :value="old('address', $worker->address ?? '')"
                        :error="$errors->first('address')"
                        placeholder="Masukkan alamat lengkap (opsional)" />
                </div>
            </div>
        </x-card>

        {{-- Contact Information --}}
        <x-card title="Kontak">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.input
                    name="phone_number"
                    label="No. Telepon"
                    type="tel"
                    :value="old('phone_number', $worker->phone_number ?? '')"
                    required
                    :error="$errors->first('phone_number')"
                    placeholder="Contoh: 081234567890" />

                <x-form.input
                    name="email"
                    label="Email"
                    type="email"
                    :value="old('email', $worker->email ?? '')"
                    required
                    :error="$errors->first('email')"
                    placeholder="Contoh: nama@email.com" />
            </div>
        </x-card>

        {{-- Employment Information --}}
        <x-card title="Data Kepegawaian">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.select
                    name="department_id"
                    label="Departemen"
                    required
                    :error="$errors->first('department_id')">
                    <option value="">Pilih Departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id', $worker->department_id ?? '') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    name="employment_status"
                    label="Status Kepegawaian"
                    required
                    :error="$errors->first('employment_status')">
                    <option value="">Pilih Status Kepegawaian</option>
                    <option value="permanent" {{ old('employment_status', $worker->employment_status ?? '') == 'permanent' ? 'selected' : '' }}>Tetap</option>
                    <option value="contract" {{ old('employment_status', $worker->employment_status ?? 'contract') == 'contract' ? 'selected' : '' }}>Kontrak</option>
                    <option value="internship" {{ old('employment_status', $worker->employment_status ?? '') == 'internship' ? 'selected' : '' }}>Magang</option>
                </x-form.select>

                <x-form.input
                    name="hire_date"
                    label="Tanggal Masuk"
                    type="date"
                    :value="old('hire_date', $worker->hire_date?->format('Y-m-d') ?? '')"
                    required
                    :error="$errors->first('hire_date')" />

                <x-form.input
                    name="resign_date"
                    label="Tanggal Resign (Opsional)"
                    type="date"
                    :value="old('resign_date', $worker->resign_date?->format('Y-m-d') ?? '')"
                    :error="$errors->first('resign_date')"
                    help="Kosongkan jika pegawai masih aktif" />

                <x-form.select
                    name="status"
                    label="Status"
                    required
                    :error="$errors->first('status')">
                    <option value="">Pilih Status</option>
                    <option value="active" {{ old('status', $worker->status ?? 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $worker->status ?? '') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    <option value="resigned" {{ old('status', $worker->status ?? '') == 'resigned' ? 'selected' : '' }}>Resign</option>
                </x-form.select>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                <x-button
                    variant="secondary"
                    onclick="window.location.href='{{ route('admin.workers.index') }}'">
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

@push('scripts')
<script>
    function previewWorkerPhoto(input) {
        const file = input.files && input.files[0];
        const preview = document.getElementById('worker-photo-preview');
        const placeholder = document.getElementById('worker-photo-placeholder');

        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const reader = new window.FileReader();
        reader.onload = function (event) {
            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            preview.src = event.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    // Compress image before upload
    function compressImage(file, maxSizeMB = 0.5) {
        return new window.Promise((resolve, reject) => {
            const maxSize = maxSizeMB * 1024 * 1024;

            if (file.size <= maxSize) {
                resolve(file);
                return;
            }

            const reader = new window.FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new window.Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

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

                    let quality = 0.8;
                    const tryCompress = () => {
                        canvas.toBlob((blob) => {
                            if (blob.size <= maxSize || quality <= 0.1) {
                                const compressedFile = new window.File([blob], file.name, {
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

    // Handle photo compression
    const photoInput = document.querySelector('input[name="photo"]');
    if (photoInput) {
        photoInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const form = this.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHTML = submitBtn.innerHTML;

            try {
                if (file.size > 2 * 1024 * 1024) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengompres foto...';

                    const compressedFile = await compressImage(file, 0.5);

                    const dataTransfer = new window.DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    photoInput.files = dataTransfer.files;

                    console.log('Original:', (file.size / 1024 / 1024).toFixed(2), 'MB →', 'Compressed:', (compressedFile.size / 1024 / 1024).toFixed(2), 'MB');

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHTML;
                }
            } catch (error) {
                console.error('Error compressing image:', error);
                alert('Gagal mengompres foto. Silakan coba dengan foto yang lebih kecil.');
                photoInput.value = '';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHTML;
            }
        });
    }
</script>
@endpush
@endsection
