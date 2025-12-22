@extends('layouts.employee')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee.profile.show') }}" 
           class="mr-4 text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Edit Profile</h1>
            <p class="text-gray-600 mt-1">Perbarui informasi pribadi Anda</p>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('employee.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Photo Preview -->
            <div class="mb-6 text-center">
                <img id="photoPreview" 
                     src="{{ $worker->photo_url ? Storage::url($worker->photo_url) : 'https://ui-avatars.com/api/?name=' . urlencode($worker->name) }}" 
                     alt="{{ $worker->name }}"
                     class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-green-100 mb-4">
                
                <div class="relative inline-block">
                    <label for="photo" class="cursor-pointer px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition inline-block">
                        <i class="fas fa-camera mr-2"></i>Ubah Foto
                    </label>
                    <input type="file" 
                           name="photo" 
                           id="photo" 
                           accept="image/jpeg,image/jpg,image/png"
                           onchange="previewPhoto(event)"
                           class="hidden">
                </div>
                @error('photo')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">Format: JPG, JPEG, PNG. Maksimal 2MB</p>
            </div>

            <!-- Personal Information (Read-only) -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Informasi Pribadi (Tidak dapat diubah)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-600">Nama Lengkap</label>
                        <p class="text-sm font-medium text-gray-800">{{ $worker->name }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">NIP</label>
                        <p class="text-sm font-medium text-gray-800">{{ $worker->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">NIK</label>
                        <p class="text-sm font-medium text-gray-800">{{ $worker->nik ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Tanggal Lahir</label>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $worker->birth_date ? \Carbon\Carbon::parse($worker->birth_date)->format('d F Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       required
                       value="{{ old('email', $user->email) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                    No. Telepon
                </label>
                <input type="text" 
                       name="phone_number" 
                       id="phone_number" 
                       value="{{ old('phone_number', $worker->phone_number) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('phone_number') border-red-500 @enderror"
                       placeholder="08123456789">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Address -->
            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Alamat
                </label>
                <textarea name="address" 
                          id="address" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 @error('address') border-red-500 @enderror"
                          placeholder="Masukkan alamat lengkap">{{ old('address', $worker->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('employee.profile.show') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
