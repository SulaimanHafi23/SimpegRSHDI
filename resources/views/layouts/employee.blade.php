{{-- filepath: resources/views/layouts/employee.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Mobile Responsive Fix -->
    <style>
        @media (max-width: 768px) {
            html, body {
                overflow-x: hidden !important;
                max-width: 100vw !important;
                width: 100vw !important;
            }
            * {
                max-width: 100vw !important;
            }
            .min-w-\[200px\], .min-w-\[300px\], .min-w-\[400px\] {
                min-width: auto !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 overflow-x-hidden">
    <div class="min-h-screen flex overflow-x-hidden max-w-full">
        <!-- Backdrop Overlay for Mobile -->
        <div id="sidebar-backdrop"
             class="fixed inset-0 backdrop-blur-sm bg-white/30 z-30 lg:hidden transition-opacity duration-300 opacity-0 pointer-events-none"
             onclick="toggleUnifiedSidebar()"></div>

        <!-- Unified Sidebar -->
        @include('layouts.partials.unified-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64 overflow-x-hidden w-full max-w-full">
            <!-- Top Navbar -->
            @include('layouts.partials.employee-navbar')

            <!-- Page Content with bottom padding for mobile footer -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8 overflow-x-hidden w-full max-w-full">
            <div class="max-w-7xl mx-auto w-full overflow-x-hidden">
                <!-- Alert Messages -->
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
                        <div class="alert-dismissible mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center shadow-md">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert-dismissible mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center shadow-md">
                            <i class="fas fa-exclamation-circle mr-3"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert-dismissible mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-center shadow-md">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <span>{{ session('warning') }}</span>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert-dismissible mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg flex items-center shadow-md">
                            <i class="fas fa-info-circle mr-3"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                    @endif
                @else
                    {{-- Use Sweet Alert for other pages --}}
                    <x-sweet-alert />
                @endif

                <!-- Page Content -->
                @yield('content')
            </div>
        </main>
        </div>
    </div>

    <!-- Bottom Navigation for Mobile -->
    @include('layouts.partials.employee-footer')

    <!-- Mobile Sidebar Overlay -->
    <div id="employee-sidebar-overlay" class="fixed inset-0 backdrop-blur-sm bg-white/30 hidden lg:hidden transition-opacity duration-300" style="z-index: 35;"></div>

    @stack('scripts')

    <script>
        function toggleEmployeeSidebar() {
            const sidebar = document.getElementById('employee-sidebar');
            const overlay = document.getElementById('employee-sidebar-overlay');

            if (sidebar && overlay) {
                const isHidden = sidebar.classList.contains('-translate-x-full');

                if (isHidden) {
                    // Open sidebar
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    // Close sidebar
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }
        }

        // Initialize overlay click handler and auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            // Overlay handler
            const overlay = document.getElementById('employee-sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', toggleEmployeeSidebar);
            }

            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert-dismissible');
            if (alerts.length > 0) {
                alerts.forEach(function(alert) {
                    setTimeout(function() {
                        if (alert && alert.parentNode) {
                            alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-10px)';
                            setTimeout(function() {
                                if (alert && alert.parentNode) {
                                    alert.remove();
                                }
                            }, 500);
                        }
                    }, 5000);
                });
            }
        });    </script>
</body>
</html>
