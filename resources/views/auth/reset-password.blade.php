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

    <style>
        :root {
            --green-dark: #0a3d1f;
            --green-main: #155a2e;
            --green-mid: #1e7a3e;
            --green-light: #28a04f;
            --gold: #f5a623;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(151, 199, 171, 0.35);
            position: relative;
            overflow: hidden;
            box-shadow:
                0 24px 56px rgba(26, 87, 57, 0.2),
                0 0 0 1px rgba(245, 166, 35, 0.18),
                0 0 38px rgba(245, 166, 35, 0.24);
        }

        .reset-card::before {
            content: '';
            position: absolute;
            inset: -30% auto auto -20%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(245, 166, 35, 0.34) 0%, rgba(245, 166, 35, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        .reset-card > * {
            position: relative;
            z-index: 1;
        }

        .reset-input {
            border: 1px solid rgba(134, 181, 153, 0.42);
            background: rgba(255, 255, 255, 0.96);
        }

        .reset-input:focus {
            border-color: var(--green-light);
            box-shadow:
                0 0 0 3px rgba(40, 160, 79, 0.18),
                0 0 0 6px rgba(245, 166, 35, 0.14);
            outline: none;
        }

        .reset-submit {
            background: linear-gradient(135deg, var(--gold), #d97706);
            color: #1a1a1a;
        }

        .reset-submit:hover {
            box-shadow: 0 12px 28px rgba(245, 166, 35, 0.35);
            filter: brightness(1.03);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-green-50 via-white to-yellow-50 relative overflow-hidden">
        {{-- Background Decorations --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-yellow-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="w-full max-w-md relative z-10 px-4">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <div class="inline-flex h-16 w-16 bg-linear-to-br from-green-600 to-green-800 rounded-xl items-center justify-center shadow-lg mb-4 overflow-hidden">
                    <img src="{{ asset('images/logo-rs.png') }}" alt="RSUD Logo" class="h-full w-full object-cover"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-hospital text-white text-2xl\'></i>';">
                </div>
                <h1 class="text-xl font-bold text-gray-900">SIDIA</h1>
                <p class="text-sm text-gray-600">Sistem Informasi Darlan Ismail dan Absensi</p>
            </div>

            <div class="p-6 sm:p-8 shadow-2xl rounded-xl border-t-4 border-[#28a04f] backdrop-blur-lg reset-card">
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
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed reset-input"
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
                                class="w-full px-4 py-3 pr-12 rounded-lg transition duration-200 reset-input @error('password') border-red-500 @enderror"
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
                                class="w-full px-4 py-3 pr-12 rounded-lg transition duration-200 reset-input"
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
                        class="w-full py-3 text-base font-semibold rounded-lg shadow-md hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#f5a623] focus:ring-offset-2 transition-all duration-300 active:scale-95 disabled:opacity-50 reset-submit"
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
                    <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-[#2f6a4b] hover:text-[#1f5c3b] font-medium transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Login
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-6 text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} Muhammad Sulaiman Hafi &amp; Muhammad Hafidl Badali x RSUD HDI. All rights reserved.</p>
                <div class="mt-3 flex items-center justify-center gap-4 text-xs text-gray-400">
                    <a href="{{ route('public.privacy') }}" class="hover:text-[#f5a623] transition duration-200">Privacy</a>
                    <span>•</span>
                    <a href="{{ route('public.terms') }}" class="hover:text-[#f5a623] transition duration-200">Terms</a>
                    <span>•</span>
                    <a href="{{ route('public.help') }}" class="hover:text-[#f5a623] transition duration-200">Help</a>
                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
