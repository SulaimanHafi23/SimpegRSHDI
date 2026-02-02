<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Maintenance Mode - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full">
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12">
                <div class="text-center">
                    {{-- Maintenance Icon --}}
                    <div class="mb-8">
                        <div class="inline-flex items-center justify-center w-32 h-32 bg-blue-100 rounded-full">
                            <i class="fas fa-tools text-6xl text-blue-600 animate-pulse"></i>
                        </div>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        Sedang Dalam Perbaikan
                    </h1>
                    
                    {{-- Message --}}
                    <p class="text-lg text-gray-600 mb-8">
                        Kami sedang melakukan pemeliharaan sistem untuk meningkatkan layanan.
                    </p>

                    @if(isset($exception) && $exception->getMessage())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ $exception->getMessage() }}
                            </p>
                        </div>
                    @else
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                            <div class="flex items-center justify-center mb-4">
                                <i class="fas fa-clock text-blue-600 text-3xl"></i>
                            </div>
                            <p class="text-blue-800 font-semibold mb-2">
                                Estimasi Waktu Pemeliharaan
                            </p>
                            <p class="text-blue-600">
                                Sistem akan kembali normal dalam waktu dekat
                            </p>
                        </div>
                    @endif

                    {{-- Progress Animation --}}
                    <div class="mb-8">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full animate-progress"></div>
                        </div>
                    </div>

                    {{-- Info Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4">
                            <i class="fas fa-shield-alt text-blue-600 text-2xl mb-2"></i>
                            <p class="text-sm font-semibold text-blue-900">Data Aman</p>
                            <p class="text-xs text-blue-700">Semua data Anda tetap aman</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4">
                            <i class="fas fa-rocket text-green-600 text-2xl mb-2"></i>
                            <p class="text-sm font-semibold text-green-900">Peningkatan</p>
                            <p class="text-xs text-green-700">Fitur baru sedang dipersiapkan</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4">
                            <i class="fas fa-bolt text-purple-600 text-2xl mb-2"></i>
                            <p class="text-sm font-semibold text-purple-900">Optimasi</p>
                            <p class="text-xs text-purple-700">Performa lebih cepat</p>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="pt-8 border-t border-gray-200">
                        <p class="text-sm text-gray-500 mb-4">
                            Butuh bantuan darurat?
                        </p>
                        <div class="flex justify-center gap-6 text-sm">
                            <a href="mailto:admin@simpegrshdi.com" class="text-blue-600 hover:text-blue-700 flex items-center gap-2">
                                <i class="fas fa-envelope"></i>
                                admin@simpegrshdi.com
                            </a>
                            <a href="tel:+6281234567890" class="text-blue-600 hover:text-blue-700 flex items-center gap-2">
                                <i class="fas fa-phone"></i>
                                0812-3456-7890
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-8">
                <p class="text-sm text-gray-600">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
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
    </style>
</body>
</html>
