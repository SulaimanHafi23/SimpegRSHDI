{{-- filepath: resources/views/auth/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reset Kata Sandi - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 via-white to-yellow-50 relative overflow-hidden">
        {{-- Background Decorations --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-yellow-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="w-full max-w-md relative z-10 px-4">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <div class="inline-flex h-16 w-16 bg-gradient-to-br from-green-600 to-green-800 rounded-xl items-center justify-center shadow-lg mb-4 overflow-hidden">
                    <img src="{{ asset('images/logo-rs.png') }}" alt="RSUD Logo" class="h-full w-full object-cover"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-hospital text-white text-2xl\'></i>';">
                </div>
                <h1 class="text-xl font-bold text-gray-900">SIMPEG RSUD</h1>
                <p class="text-sm text-gray-600">Haji Darlan Ismail</p>
            </div>

            <div class="p-6 sm:p-8 shadow-2xl rounded-xl border-t-4 border-green-600 backdrop-blur-lg bg-white/95">
                {{-- Header Icon --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center h-16 w-16 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Reset Kata Sandi</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Buat kata sandi baru yang kuat untuk akun Anda.
                    </p>
                </div>

                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <strong class="font-semibold text-red-800">Error!</strong>
                                @foreach ($errors->all() as $error)
                                    <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.update') }}" class="space-y-6" x-data="{ loading: false, showPassword: false, showConfirm: false }" @submit="loading = true">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- Email (Read-only) --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-green-600 mr-1"></i>
                            Alamat Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $email ?? '') }}"
                            readonly
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed"
                        >
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-green-600 mr-1"></i>
                            Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                id="password"
                                placeholder="Minimal 8 karakter"
                                required
                                autofocus
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white/90 transition duration-200 @error('password') border-red-500 @enderror"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-green-600 transition duration-200"
                            >
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Minimal 8 karakter
                        </p>
                        @error('password')
                            <p class="text-red-600 text-sm mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-green-600 mr-1"></i>
                            Konfirmasi Kata Sandi
                        </label>
                        <div class="relative">
                            <input
                                :type="showConfirm ? 'text' : 'password'"
                                name="password_confirmation"
                                id="password_confirmation"
                                placeholder="Ulangi kata sandi baru"
                                required
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white/90 transition duration-200"
                            >
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-green-600 transition duration-200"
                            >
                                <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-3 text-base bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-md hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 active:scale-95 disabled:opacity-50"
                    >
                        <span x-show="!loading" class="flex items-center justify-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Reset Kata Sandi
                        </span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Memproses...
                        </span>
                    </button>
                </form>

                {{-- Back to Login --}}
                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-green-600 hover:text-green-700 font-medium transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Login
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-6 text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} RSUD Haji Darlan Ismail. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
