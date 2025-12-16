{{-- filepath: resources/views/layouts/employee.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar - Hidden on mobile, visible on desktop -->
        @include('layouts.partials.employee-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Navbar -->
            @include('layouts.partials.employee-navbar')

            <!-- Page Content with bottom padding for mobile footer -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8">
            <div class="max-w-7xl mx-auto">
                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center shadow-md">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center shadow-md">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-center shadow-md">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
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
    <div id="employee-sidebar-overlay" class="fixed inset-0 bg-black/50 hidden lg:hidden transition-opacity duration-300" style="z-index: 35;"></div>

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

        // Initialize overlay click handler
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('employee-sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', toggleEmployeeSidebar);
            }
        });
        // Auto-dismiss alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-yellow-100');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 3000);
            });
        });    </script>
</body>
</html>
