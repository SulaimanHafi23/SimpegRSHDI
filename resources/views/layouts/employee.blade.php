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

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Backdrop Overlay for Mobile -->
        <div id="sidebar-backdrop"
             class="fixed inset-0 backdrop-blur-sm bg-white/30 z-30 lg:hidden transition-opacity duration-300 opacity-0 pointer-events-none"
             onclick="toggleUnifiedSidebar()"></div>

        <!-- Unified Sidebar -->
        @include('layouts.partials.unified-sidebar')

        <!-- Main Content -->
        <div class="min-h-screen flex flex-col lg:ml-64">
            <!-- Top Navbar -->
            @include('layouts.partials.employee-navbar')

            <!-- Page Content with bottom padding for mobile footer -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8">
            <div class="w-full">
                <!-- Alert Messages -->
                @php
                    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
                    $isCreateOrEditPage = str_contains($currentRoute, '.create') ||
                                         str_contains($currentRoute, '.edit') ||
                                         str_contains($currentRoute, 'create') ||
                                         str_contains($currentRoute, 'edit');

                    $isCreateEditShowPage = $isCreateOrEditPage ||
                                            str_contains($currentRoute, '.show') ||
                                            str_contains($currentRoute, 'show') ||
                                            str_contains($currentRoute, 'detail') ||
                                            str_contains($currentRoute, 'generate') ||
                                            request()->is('*create*', '*edit*', '*show*', '*detail*', '*generate*');

                    $routeParts = explode('.', $currentRoute ?? '');
                    $parentRoute = count($routeParts) > 1
                        ? implode('.', array_slice($routeParts, 0, -1))
                        : null;

                    $backHref = ($parentRoute && \Illuminate\Support\Facades\Route::has($parentRoute . '.index'))
                        ? route($parentRoute . '.index')
                        : url()->previous();
                @endphp

                @if($isCreateEditShowPage)
                    <div class="mb-4">
                        <x-ui.back-button :href="$backHref" />
                    </div>
                @endif

                @php
                    $globalErrorMessage = session('error') ?? ($errors->any() ? $errors->first() : null);
                @endphp

                @if($globalErrorMessage)
                    <x-ui.alert type="error" :message="$globalErrorMessage" />
                @endif

                @if(session('success'))
                    <x-ui.alert type="success" :message="session('success')" :auto-dismiss="true" :dismiss-after="4500" />
                @endif

                @if(session('warning'))
                    <x-ui.alert type="warning" :message="session('warning')" :auto-dismiss="true" :dismiss-after="6000" />
                @endif

                @if(session('info'))
                    <x-ui.alert type="info" :message="session('info')" :auto-dismiss="true" :dismiss-after="4500" />
                @endif

                <x-sweet-alert />

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
