<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <!-- Background Pattern -->
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-blue-50 to-indigo-100 relative overflow-hidden">
        <!-- Animated Background Shapes -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse-slow"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse-slow animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-indigo-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse-slow animation-delay-4000"></div>
        </div>

        <!-- Main Content -->
        <div class="relative flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8 animate-slide-up">
                <!-- Logo & Header -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center">
                        <div class="relative">
                            <!-- Main Logo Circle -->
                            <div class="h-20 w-20 bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl flex items-center justify-center shadow-2xl transform hover:scale-110 transition-transform duration-300">
                                <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <!-- Pulse Effect -->
                            <div class="absolute inset-0 h-20 w-20 bg-primary-600 rounded-2xl animate-ping opacity-20"></div>
                        </div>
                    </div>

                    <h2 class="mt-6 text-4xl font-extrabold text-gray-900">
                        {{ config('app.name', 'SIMPEGRS HDI') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 font-medium">
                        Sistem Informasi Manajemen Pegawai
                    </p>

                    <!-- Decorative Line -->
                    <div class="flex items-center justify-center mt-4">
                        <div class="h-1 w-12 bg-gradient-to-r from-primary-600 to-primary-400 rounded-full"></div>
                        <div class="h-1 w-12 bg-gradient-to-r from-primary-400 to-primary-200 rounded-full ml-1"></div>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="card animate-fade-in">
                    <div class="p-8 sm:p-10">
                        @yield('content')
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center space-y-2 animate-fade-in">
                    <p class="text-sm text-gray-600">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                    <div class="flex items-center justify-center space-x-4 text-xs text-gray-500">
                        <a href="#" class="hover:text-primary-600 transition duration-200">Privacy Policy</a>
                        <span>•</span>
                        <a href="#" class="hover:text-primary-600 transition duration-200">Terms of Service</a>
                        <span>•</span>
                        <a href="#" class="hover:text-primary-600 transition duration-200">Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
