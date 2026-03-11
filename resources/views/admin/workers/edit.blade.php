@extends('layouts.admin')

@section('title', 'Edit Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-3 sm:gap-4 mb-6">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-indigo-600 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <i class="fas fa-user-edit text-white text-lg sm:text-xl"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Edit Data Pegawai</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Perbarui informasi pegawai dengan lengkap dan akurat</p>
        </div>
        <a href="{{ route('admin.workers.index') }}"
           class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shrink-0">
            <i class="fas fa-arrow-left text-xs"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>

    {{-- Info Banner --}}
    <div class="flex items-start gap-3 px-4 py-3.5 bg-blue-50 rounded-xl border border-blue-200">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-blue-800 mb-1">Tips Pengisian</p>
            <p class="text-xs sm:text-sm text-blue-700">Pastikan email, kategori payroll, dan data kepegawaian sesuai agar sinkron dengan akun pengguna dan perhitungan gaji.</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl">
            <p class="text-green-700 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
            <p class="text-red-700 text-sm">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                <div>
                    <strong class="font-bold text-red-900">Terdapat kesalahan pada form!</strong>
                    <ul class="mt-2 ml-4 list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.workers.update', $worker->id ?? 1) }}" method="POST" enctype="multipart/form-data" class="space-y-5 sm:space-y-6">
        @csrf
        @method('PUT')

        {{-- Photo Upload --}}
        <x-card title="Foto Pegawai">
            <x-form.file
                name="photo"
                label="Ganti Foto"
                accept="image/*"
                preview
                :currentFile="$worker->photo_url && Storage::disk('public')->exists($worker->photo_url) ? asset('storage/' . $worker->photo_url) : null"
                help="Format: JPG, PNG (Max: 2MB) - Kosongkan jika tidak ingin mengubah" />
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

        {{-- Payroll Information --}}
        <x-card title="Data Payroll & Pangkat">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.select
                    name="payroll_category"
                    label="Kategori Penggajian"
                    :error="$errors->first('payroll_category')">
                    <option value="">Pilih Kategori</option>
                    <option value="asn" {{ old('payroll_category', $worker->payroll_category ?? '') == 'asn' ? 'selected' : '' }}>ASN</option>
                    <option value="pppk" {{ old('payroll_category', $worker->payroll_category ?? '') == 'pppk' ? 'selected' : '' }}>PPPK</option>
                    <option value="non_asn" {{ old('payroll_category', $worker->payroll_category ?? '') == 'non_asn' ? 'selected' : '' }}>Non-ASN</option>
                    <option value="outsourced" {{ old('payroll_category', $worker->payroll_category ?? '') == 'outsourced' ? 'selected' : '' }}>Outsourcing / Pihak Ketiga</option>
                </x-form.select>

                <x-form.input
                    name="base_salary"
                    label="Gaji Pokok"
                    type="number"
                    min="0"
                    step="1000"
                    :value="old('base_salary', $worker->base_salary ?? '')"
                    :error="$errors->first('base_salary')"
                    placeholder="Contoh: 3500000" />

                <x-form.input
                    name="rank"
                    label="Pangkat"
                    :value="old('rank', $worker->rank ?? '')"
                    :error="$errors->first('rank')"
                    placeholder="Contoh: Penata Muda" />

                <x-form.input
                    name="rank_level"
                    label="Golongan"
                    :value="old('rank_level', $worker->rank_level ?? '')"
                    :error="$errors->first('rank_level')"
                    placeholder="Contoh: III/a" />

                <x-form.input
                    name="outsourced_vendor"
                    label="Vendor Outsourcing (Opsional)"
                    :value="old('outsourced_vendor', $worker->outsourced_vendor ?? '')"
                    :error="$errors->first('outsourced_vendor')"
                    placeholder="Nama perusahaan vendor" />

                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="auto_sync_salary_components" value="1" {{ old('auto_sync_salary_components') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Sinkronisasi komponen gaji default otomatis berdasarkan kategori payroll
                    </label>
                </div>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <a href="{{ route('admin.workers.index') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    Batal
                </a>
                <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </x-card>
    </form>
</div>

@push('scripts')
<script>
    // Compress image before upload
    function compressImage(file, maxSizeMB = 0.5) {
        return new Promise((resolve, reject) => {
            const maxSize = maxSizeMB * 1024 * 1024;

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

                    const dataTransfer = new DataTransfer();
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
