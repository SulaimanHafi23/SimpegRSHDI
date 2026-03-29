@extends('layouts.admin')

@section('title', 'Profile Saya')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-800">Profile Saya</h1>
        <p class="mt-2 text-xs sm:text-sm text-gray-600">Kelola informasi akun dan keamanan Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Profile Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="text-center">
                    <div class="relative inline-block">
                        @if($user->worker && $user->worker->photo_url && Storage::disk('public')->exists($user->worker->photo_url))
                            <img src="{{ Storage::url($user->worker->photo_url) }}" alt="{{ $user->name }}"
                                 class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-green-500">
                        @elseif($user->photo && Storage::disk('public')->exists($user->photo))
                            <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}"
                                 class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-green-500">
                        @else
                            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-green-500 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold border-4 border-green-600">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h2 class="mt-4 text-lg sm:text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                    <p class="text-sm sm:text-base text-gray-600">{{ $user->email }}</p>

                    <div class="mt-4 space-y-2">
                        @foreach($user->roles as $role)
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                @if($role->name === 'Super Admin') bg-red-100 text-red-800
                                @elseif($role->name === 'HR') bg-blue-100 text-blue-800
                                @elseif($role->name === 'Manager') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                <i class="fas fa-user-shield mr-1"></i> {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                @if($user->worker)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Informasi Pegawai</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">NIP:</span>
                            <span class="font-medium">{{ $user->worker->nip }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->worker->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->worker->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Akun:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Terdaftar:</span>
                            <span class="font-medium">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Profile Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-user-edit text-green-600 mr-2"></i>Update Profile
                </h2>

                <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        @if($user->worker)
                        <!-- NIP (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                            <input type="text" value="{{ $user->worker->nip }}" readonly
                                   class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-700 cursor-not-allowed">
                        </div>
                        <!-- Departemen (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                            <input type="text" value="{{ $user->worker->department->name ?? '-' }}" readonly
                                   class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-700 cursor-not-allowed">
                        </div>
                        <!-- Gender (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                            <input type="text" value="{{ $user->worker->gender ?? '-' }}" readonly
                                   class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-700 cursor-not-allowed">
                        </div>
                        <!-- Tanggal Lahir (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="text" value="{{ $user->worker->birth_date?->format('d F Y') ?? '-' }}" readonly
                                   class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-700 cursor-not-allowed">
                        </div>
                        <!-- Alamat (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea readonly class="w-full px-3 py-2 border border-gray-200 bg-gray-100 rounded-lg text-gray-700 cursor-not-allowed">{{ $user->worker->address ?? '-' }}</textarea>
                        </div>
                        @endif
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->username) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Photo -->
                        <div>
                            <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">
                                Foto Profile
                            </label>
                            <input type="file" name="photo" id="photo" accept="image/*"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   onchange="previewPhoto(event)">
                            <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG (Max: 2MB)</p>
                            @error('photo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Photo Preview -->
                            <div id="photoPreview" class="mt-3 hidden">
                                <img src="" alt="Preview" class="w-32 h-32 rounded-lg object-cover">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition inline-flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <div class="flex space-x-3">
                            <button type="button" onclick="this.form.reset(); document.getElementById('photoPreview').classList.add('hidden')"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-times mr-2"></i>Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <i class="fas fa-lock text-yellow-600 mr-2"></i>Ubah Password
                </h2>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="current_password" id="current_password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   required>
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   required minlength="8">
                            <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   required minlength="8">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition inline-flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <div class="flex space-x-3">
                            <button type="button" onclick="this.form.reset()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-times mr-2"></i>Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                                <i class="fas fa-key mr-2"></i>Ubah Password
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Lupa Password -->
                @if (Route::has('password.request'))
                    <div class="mt-4 text-center">
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                            <i class="fas fa-question-circle mr-1"></i> Lupa Password?
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Max allowed size we want to target (bytes). Adjust to your server limits.
    const MAX_BYTES = 2 * 1024 * 1024; // 2 MB

    const form = document.getElementById('profileForm');
    const fileInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const previewImg = photoPreview ? photoPreview.querySelector('img') : null;

    // Expose a preview function for the file input onchange (keeps existing behavior)
    window.previewPhoto = function(event) {
        const file = event.target.files[0];
        previewPhotoFile(file);
    }

    function previewPhotoFile(file) {
        if (!file) {
            if (photoPreview) photoPreview.classList.add('hidden');
            return;
        }
        if (!previewImg) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            photoPreview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }

    async function compressImageFile(file, maxBytes) {
        // Return compressed Blob or null if cannot compress enough
        if (!file.type.startsWith('image/')) return null;

        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = new Image();
                img.onload = async function() {
                    // Resize to a max dimension to reduce size
                    const MAX_DIM = 1200;
                    let width = img.width;
                    let height = img.height;
                    if (width > MAX_DIM || height > MAX_DIM) {
                        const ratio = Math.min(MAX_DIM / width, MAX_DIM / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Try decreasing quality until under limit or quality floor
                    let quality = 0.85;
                    let blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', quality));
                    // If original format is png and has transparency, converting to jpeg may drop transparency
                    // but jpeg results in much smaller size for photos. We accept that tradeoff here.
                    while (blob && blob.size > maxBytes && quality > 0.35) {
                        quality -= 0.15;
                        // eslint-disable-next-line no-await-in-loop
                        blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', quality));
                    }

                    if (!blob) return resolve(null);
                    if (blob.size > maxBytes) return resolve(null);
                    return resolve(blob);
                };
                img.onerror = () => reject(new Error('Gagal memproses gambar'));
                img.src = ev.target.result;
            };
            reader.onerror = () => reject(new Error('Gagal membaca file'));
            reader.readAsDataURL(file);
        });
    }

    form.addEventListener('submit', async function(e) {
        if (!fileInput || !fileInput.files || !fileInput.files[0]) return;

        const originalFile = fileInput.files[0];
        // If file already within limit, proceed. Otherwise try compressing.
        if (originalFile.size <= MAX_BYTES) return;

        // Try to compress client-side
        try {
            const compressedBlob = await compressImageFile(originalFile, MAX_BYTES);
            if (!compressedBlob) {
                e.preventDefault();
                alert('Ukuran file terlalu besar dan tidak dapat dikompres cukup kecil. Silakan pilih gambar yang lebih kecil atau hubungi admin untuk menaikkan limit server.');
                return;
            }

            // Create a File from the blob and replace file input
            const newFile = new File([compressedBlob], (originalFile.name.replace(/\.[^.]+$/, '') + '.jpg'), { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);
            fileInput.files = dataTransfer.files;

            // Update preview to compressed version
            previewPhotoFile(newFile);
            // Allow form to submit with compressed file
        } catch (err) {
            // If compression fails, prevent the submit and inform user
            e.preventDefault();
            console.error('Compression error', err);
            alert('Terjadi kesalahan saat mengompres gambar. Silakan coba lagi dengan file yang lebih kecil.');
        }
    });
});
</script>
@endsection
