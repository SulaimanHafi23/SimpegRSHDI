<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Maintenance Mode - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-linear-to-br from-[#0a3d1f] via-[#155a2e] to-[#0d2b17] text-white">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full">
            <div class="rounded-3xl border border-[#f5a623]/20 bg-white/10 shadow-2xl backdrop-blur-xl p-8 md:p-12 relative overflow-hidden">
                <div class="absolute -top-16 -right-16 h-40 w-40 rounded-full bg-[#f5a623]/20 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-[#28a04f]/20 blur-3xl"></div>
                <div class="text-center">
                    {{-- Maintenance Icon --}}
                    <div class="mb-8">
                        <div class="inline-flex items-center justify-center w-32 h-32 bg-[#f5a623]/15 border border-[#ffd166]/20 rounded-full">
                            <i class="fas fa-tools text-6xl text-[#ffd166] animate-pulse"></i>
                        </div>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        Sedang Dalam Perbaikan
                    </h1>

                    {{-- Message --}}
                    <p class="text-lg text-emerald-50/80 mb-8">
                        Kami sedang melakukan pemeliharaan sistem untuk meningkatkan layanan.
                    </p>

                    @if(isset($exception) && $exception->getMessage())
                        <div class="bg-white/10 border border-white/10 rounded-2xl p-4 mb-8">
                            <p class="text-sm text-emerald-50/85">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ $exception->getMessage() }}
                            </p>
                        </div>
                    @else
                        <div class="bg-white/10 border border-white/10 rounded-2xl p-6 mb-8">
                            <div class="flex items-center justify-center mb-4">
                                <i class="fas fa-clock text-[#ffd166] text-3xl"></i>
                            </div>
                            <p class="text-[#fff7db] font-semibold mb-2">
                                Estimasi Waktu Pemeliharaan
                            </p>
                            <p class="text-emerald-50/75">
                                Sistem akan kembali normal dalam waktu dekat
                            </p>
                        </div>
                    @endif

                    {{-- Progress Animation --}}
                    <div class="mb-8">
                        <div class="w-full bg-white/10 rounded-full h-2">
                            <div class="bg-linear-to-r from-[#f5a623] to-[#ffd166] h-2 rounded-full animate-progress"></div>
                        </div>
                    </div>

                    {{-- Info Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-white/8 border border-white/10 rounded-2xl p-4">
                            <i class="fas fa-shield-alt text-[#ffd166] text-2xl mb-2"></i>
                            <p class="text-sm font-semibold text-white">Data Aman</p>
                            <p class="text-xs text-emerald-50/70">Semua data Anda tetap aman</p>
                        </div>
                        <div class="bg-white/8 border border-white/10 rounded-2xl p-4">
                            <i class="fas fa-rocket text-[#86efac] text-2xl mb-2"></i>
                            <p class="text-sm font-semibold text-white">Peningkatan</p>
                            <p class="text-xs text-emerald-50/70">Fitur baru sedang dipersiapkan</p>
                        </div>
                        <div class="bg-white/8 border border-white/10 rounded-2xl p-4">
                            <i class="fas fa-bolt text-[#fcd34d] text-2xl mb-2"></i>
                            <p class="text-sm font-semibold text-white">Optimasi</p>
                            <p class="text-xs text-emerald-50/70">Performa lebih cepat</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center mb-8">
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center w-full sm:w-auto sm:min-w-[210px] rounded-2xl bg-linear-to-r from-[#f5a623] to-[#ffd166] px-5 py-3 text-sm font-bold text-[#16331f] shadow-lg shadow-[#f5a623]/20 hover:brightness-105 transition">
                            <i class="fas fa-house mr-2"></i>
                            Kembali ke Beranda
                        </a>
                        <a href="{{ route('public.help') }}" class="inline-flex items-center justify-center w-full sm:w-auto sm:min-w-[210px] rounded-2xl border border-white/15 px-5 py-3 text-sm font-semibold text-white hover:border-[#ffd166]/40 hover:text-[#fff7db] transition">
                            <i class="fas fa-life-ring mr-2"></i>
                            Help Center
                        </a>
                    </div>

                    {{-- Contact Info --}}
                    <div class="pt-8 border-t border-white/10">
                        <p class="text-sm text-emerald-50/70 mb-4">
                            Butuh bantuan darurat?
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6 text-sm items-center">
                            <a href="mailto:sidia.rshdi@gmail.com" class="text-[#ffd166] hover:text-[#fff7db] flex items-center gap-2">
                                <i class="fas fa-envelope"></i>
                                sidia.rshdi@gmail.com
                            </a>
                            <a href="tel:+6281234567890" class="text-[#ffd166] hover:text-[#fff7db] flex items-center gap-2">
                                <i class="fas fa-phone"></i>
                                0812-3456-7890
                            </a>
                        </div>
                        <div class="mt-5 flex justify-center gap-4 text-xs text-emerald-50/65">
                            <a href="{{ route('public.privacy') }}" class="hover:text-[#ffd166]">Privacy</a>
                            <span>•</span>
                            <a href="{{ route('public.terms') }}" class="hover:text-[#ffd166]">Terms</a>
                            <span>•</span>
                            <a href="{{ route('public.help') }}" class="hover:text-[#ffd166]">Help</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-8">
                <p class="text-sm text-emerald-50/70">
                    &copy; {{ date('Y') }} Muhammad Sulaiman Hafi &amp; Muhammad Hafidl Badali x RSUD HDI. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 75%; }
            100% { width: 100%; }
        }

        .animate-progress {
            animation: progress 3s ease-in-out infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</body>
</html>
