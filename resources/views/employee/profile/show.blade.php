@extends('layouts.employee')

@section('title', 'Profile Saya')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-5xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Profile Saya</h1>
        <p class="text-gray-600 mt-1">Informasi data pribadi Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Photo & Quick Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center">
                    <img src="{{ $worker->photo_url ? Storage::url($worker->photo_url) : 'https://ui-avatars.com/api/?name=' . urlencode($worker->name) }}" 
                         alt="{{ $worker->name }}"
                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-green-100 mb-4">
                    
                    <h2 class="text-xl font-bold text-gray-800">{{ $worker->name }}</h2>
                    <p class="text-gray-600 text-sm">{{ $worker->nip ?? '-' }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('employee.profile.edit') }}" 
                           class="block w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Employment Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Status Kepegawaian</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500">Status</label>
                        <p class="text-sm font-medium">
                            @if($worker->employment_status === 'permanent')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Tetap</span>
                            @elseif($worker->employment_status === 'contract')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Kontrak</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Probation</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Tanggal Bergabung</label>
                        <p class="text-sm font-medium">{{ \Carbon\Carbon::parse($worker->hire_date)->format('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Detailed Info -->
        <div class="lg:col-span-2">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-gray-600">Nama Lengkap</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">NIK</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->nik ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Tanggal Lahir</label>
                        <p class="text-base font-medium text-gray-800">
                            {{ $worker->birth_date ? \Carbon\Carbon::parse($worker->birth_date)->format('d F Y') : '-' }}
                            @if($worker->birth_date)
                                ({{ \Carbon\Carbon::parse($worker->birth_date)->age }} tahun)
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Jenis Kelamin</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->gender->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Agama</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->religion->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Status Pernikahan</label>
                        <p class="text-base font-medium text-gray-800">
                            @if($worker->marital_status === 'single')
                                Belum Menikah
                            @elseif($worker->marital_status === 'married')
                                Menikah
                            @elseif($worker->marital_status === 'divorced')
                                Cerai
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-gray-600">Email</label>
                        <p class="text-base font-medium text-gray-800">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">No. Telepon</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->phone_number ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm text-gray-600">Alamat</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Work Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pekerjaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-gray-600">NIP</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Departemen</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->department->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Posisi</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->position ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Lokasi Kerja</label>
                        <p class="text-base font-medium text-gray-800">{{ $worker->location->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Security -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Keamanan</h3>
                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                    <div>
                        <p class="font-medium text-gray-800">Password</p>
                        <p class="text-sm text-gray-600">Terakhir diubah: {{ $user->updated_at->diffForHumans() }}</p>
                    </div>
                    <button onclick="togglePasswordModal()" 
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                        Ubah Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="hidden fixed inset-0 backdrop-blur-sm bg-white/30 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) togglePasswordModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Ubah Password</h3>
                <button onclick="togglePasswordModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('employee.profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Lama <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="current_password" 
                           id="current_password" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required
                           minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required
                           minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                </div>

                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                        Simpan
                    </button>
                    <button type="button" 
                            onclick="togglePasswordModal()"
                            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePasswordModal() {
    const modal = document.getElementById('passwordModal');
    modal.classList.toggle('hidden');
}
</script>
@endsection
