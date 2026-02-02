{{-- filepath: resources/views/layouts/workers.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Top Navbar -->
        @include('layouts.partials.workers-navbar')

        <div class="flex pt-16 lg:pt-20">
            <!-- Sidebar for Desktop/Tablet -->
            @include('layouts.partials.workers-sidebar')

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8 lg:ml-64">
                <div class="max-w-7xl mx-auto">
                    @php
                        $currentRoute = Route::currentRouteName();
                        $isCreateOrEditPage = str_contains($currentRoute, '.create') ||
                                             str_contains($currentRoute, '.edit') ||
                                             str_contains($currentRoute, 'create') ||
                                             str_contains($currentRoute, 'edit');
                    @endphp

                    @if($isCreateOrEditPage)
                        {{-- Keep traditional alerts for create/edit pages --}}
                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                                <i class="fas fa-check-circle mr-3"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
                                <i class="fas fa-exclamation-circle mr-3"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        @if(session('warning'))
                            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-center">
                                <i class="fas fa-exclamation-triangle mr-3"></i>
                                <span>{{ session('warning') }}</span>
                            </div>
                        @endif
                    @else
                        {{-- Use Sweet Alert for other pages --}}
                        <x-sweet-alert />
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bottom Navigation for Mobile -->
    @include('layouts.partials.workers-footer')

    @stack('scripts')
</body>
</html>
