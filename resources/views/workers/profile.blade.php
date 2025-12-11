@extends('layouts.workers')

@section('title', 'Profile Saya')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Profile Saya</h1>
        <p class="mt-2 text-sm text-gray-600">Kelola informasi profile dan keamanan akun Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center">
                    <div class="relative inline-block">
                        @if(auth()->user()->photo)
                            <img src="{{ asset(auth()->user()->photo) }}" alt="{{ auth()->user()->name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-green-500">
                        @else
                            <div class="w-32 h-32 rounded-full bg-green-500 flex items-center justify-center text-white text-4xl font-bold border-4 border-green-600">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-600">{{ auth()->user()->email }}</p>
                    
                    @if(auth()->user()->worker)
                    <div class="mt-4">
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-user-tie mr-1"></i> Pegawai
                        </span>
                    </div>
                    @endif
                </div>

                @if(auth()->user()->worker)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Informasi Kepegawaian</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">NIP:</span>
                            <span class="font-medium">{{ auth()->user()->worker->nip }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nama Lengkap:</span>
                            <span class="font-medium">{{ auth()->user()->worker->full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jenis Kelamin:</span>
                            <span class="font-medium">{{ auth()->user()->worker->gender->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Agama:</span>
                            <span class="font-medium">{{ auth()->user()->worker->religion->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jabatan:</span>
                            <span class="font-medium">{{ auth()->user()->worker->position->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Lokasi:</span>
                            <span class="font-medium">{{ auth()->user()->worker->location->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ auth()->user()->worker->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ auth()->user()->worker->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Kontak</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-gray-400 mt-1 mr-3 w-5"></i>
                            <div class="flex-1">
                                <span class="text-gray-600 block text-xs">Email</span>
                                <span class="font-medium">{{ auth()->user()->worker->email ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-phone text-gray-400 mt-1 mr-3 w-5"></i>
                            <div class="flex-1">
                                <span class="text-gray-600 block text-xs">No. HP</span>
                                <span class="font-medium">{{ auth()->user()->worker->phone_number ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-gray-400 mt-1 mr-3 w-5"></i>
                            <div class="flex-1">
                                <span class="text-gray-600 block text-xs">Alamat</span>
                                <span class="font-medium">{{ auth()->user()->worker->address ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Akun:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ auth()->user()->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ auth()->user()->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Terdaftar:</span>
                            <span class="font-medium">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
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
                
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}"
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
                            <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}"
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
                        <a href="{{ route('workers.dashboard') }}"
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
                
                <form action="{{ route('admin.profile.password') }}" method="POST">
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
                        <a href="{{ route('workers.dashboard') }}"
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
            </div>
        </div>
    </div>
</div>

<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            const img = preview.querySelector('img');
            img.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
