@extends('layouts.admin')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Page Header with Back Button --}}
    <div class="flex items-center space-x-3">
        <x-button 
            variant="secondary" 
            size="sm"
            icon="fas fa-arrow-left"
            onclick="window.location.href='{{ route('admin.workers.index') }}'">
        </x-button>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Tambah Pegawai Baru</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">Lengkapi form di bawah untuk menambah pegawai</p>
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

    <form action="{{ route('admin.workers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
        @csrf
        
        {{-- Photo Upload --}}
        <x-card title="Foto Pegawai">
            <x-form.file 
                name="photo" 
                label="Upload Foto"
                accept="image/*"
                preview
                help="Format: JPG, PNG (Max: 2MB)" />
        </x-card>

        {{-- Personal Information --}}
        <x-card title="Data Pribadi">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.input 
                    name="nip" 
                    label="NIP" 
                    required 
                    placeholder="Masukkan NIP"
                    :error="$errors->first('nip')" />

                <x-form.input 
                    name="name" 
                    label="Nama Lengkap" 
                    required 
                    placeholder="Masukkan nama lengkap"
                    :error="$errors->first('name')" />

                <x-form.input 
                    name="birth_place" 
                    label="Tempat Lahir" 
                    required 
                    placeholder="Masukkan tempat lahir"
                    :error="$errors->first('birth_place')" />

                <x-form.input 
                    name="birth_date" 
                    label="Tanggal Lahir" 
                    type="date" 
                    required 
                    :error="$errors->first('birth_date')" />

                <x-form.select 
                    name="gender_id" 
                    label="Jenis Kelamin"
                    placeholder="Pilih Jenis Kelamin" 
                    required 
                    :error="$errors->first('gender_id')">
                    @foreach($genders as $gender)
                        <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>
                            {{ $gender->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.select 
                    name="religion_id" 
                    label="Agama"
                    placeholder="Pilih Agama" 
                    required 
                    :error="$errors->first('religion_id')">
                    @foreach($religions as $religion)
                        <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                            {{ $religion->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <div class="md:col-span-2">
                    <x-form.textarea 
                        name="address" 
                        label="Alamat" 
                        rows="3" 
                        placeholder="Masukkan alamat lengkap"
                        :error="$errors->first('address')" />
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
                    required 
                    placeholder="Masukkan nomor telepon"
                    :error="$errors->first('phone_number')" />

                <x-form.input 
                    name="email" 
                    label="Email" 
                    type="email" 
                    required
                    placeholder="Masukkan email"
                    :error="$errors->first('email')" />
            </div>
        </x-card>

        {{-- Employment Information --}}
        <x-card title="Data Kepegawaian">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.select 
                    name="department_id" 
                    label="Departemen"
                    placeholder="Pilih Departemen" 
                    required 
                    :error="$errors->first('department_id')">
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </x-form.select>

                <x-form.input 
                    name="hire_date" 
                    label="Tanggal Masuk" 
                    type="date" 
                    required 
                    :error="$errors->first('hire_date')" />

                <x-form.select 
                    name="employment_status" 
                    label="Status Kepegawaian"
                    required 
                    :error="$errors->first('employment_status')">
                    <option value="">Pilih Status</option>
                    <option value="permanent" {{ old('employment_status') == 'permanent' ? 'selected' : '' }}>Tetap</option>
                    <option value="contract" {{ old('employment_status', 'contract') == 'contract' ? 'selected' : '' }}>Kontrak</option>
                    <option value="internship" {{ old('employment_status') == 'internship' ? 'selected' : '' }}>Magang</option>
                </x-form.select>

                <x-form.select 
                    name="status" 
                    label="Status"
                    required 
                    :error="$errors->first('status')">
                    <option value="">Pilih Status</option>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    <option value="resigned" {{ old('status') == 'resigned' ? 'selected' : '' }}>Resign</option>
                </x-form.select>

                <x-form.input 
                    name="resign_date" 
                    label="Tanggal Resign (Opsional)" 
                    type="date" 
                    :error="$errors->first('resign_date')" />
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
                    Simpan
                </x-button>
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
