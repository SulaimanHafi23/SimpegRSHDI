{{-- filepath: resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'SIDIA') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-rs.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

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
    <script>
        // Initialize sidebar state on page load - comprehensive fix
        function initializeSidebarState() {
            const sidebar = document.getElementById('unified-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const employeeBackdrop = document.getElementById('employee-sidebar-overlay');

            if (sidebar) {
                // Ensure sidebar is closed (off-screen)
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            }

            if (backdrop) {
                // Ensure backdrop is hidden and not clickable
                backdrop.classList.add('hidden');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.style.opacity = '0';
                backdrop.style.pointerEvents = 'none';
                backdrop.style.display = 'none';
            }

            if (sidebarOverlay) {
                // Hide sidebar overlay if exists
                sidebarOverlay.classList.add('hidden');
                sidebarOverlay.style.display = 'none';
            }

            if (employeeBackdrop) {
                employeeBackdrop.classList.add('hidden');
                employeeBackdrop.style.display = 'none';
                employeeBackdrop.style.opacity = '0';
                employeeBackdrop.style.pointerEvents = 'none';
            }
        }

        // Run initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeSidebarState);
        } else {
            // DOM is already loaded
            initializeSidebarState();
        }

        // Also initialize after a short delay to ensure all scripts are loaded
        setTimeout(initializeSidebarState, 100);

        // Initialize again when page is fully loaded
        window.addEventListener('load', initializeSidebarState);

        // Reset state when a tab is restored from history cache or duplicated
        window.addEventListener('pageshow', initializeSidebarState);
    </script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Backdrop Overlay for Mobile -->
        <div id="sidebar-backdrop"
             class="fixed inset-0 hidden backdrop-blur-sm bg-white/30 z-40 lg:hidden transition-opacity duration-300 opacity-0 pointer-events-none"
             onclick="toggleUnifiedSidebar()"></div>

        <!-- Unified Sidebar -->
        @include('layouts.partials.unified-sidebar')

        <!-- Main Content -->
        <div class="min-h-screen flex flex-col lg:ml-64">
            <!-- Navbar -->
            @include('layouts.partials.admin-navbar')

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 {{ auth()->check() && auth()->user()->can('dashboard.employee') ? 'pb-20 lg:pb-8' : '' }}">
                <!-- Breadcrumb -->
                @if(isset($breadcrumbs))
                    <x-ui.breadcrumb :items="$breadcrumbs" />
                @endif

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

                <x-sweet-alert />

                <!-- Page Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.partials.admin-footer')
        </div>
    </div>

    <!-- Bottom Navigation for Employee Role -->
    @if(auth()->check() && auth()->user()->can('dashboard.employee'))
        @include('layouts.partials.employee-footer')
    @endif

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 backdrop-blur-sm bg-white/30 hidden lg:hidden transition-opacity duration-300" style="z-index: 35;"></div>

    @stack('scripts')

    <script>
        // Admin Notifications Alpine Component
        function adminNotifications() {
            return {
                open: false,
                pendingLeaves: [],
                pendingDocuments: [],
                totalPending: 0,

                loadPendingRequests() {
                    // Load pending leaves
                    fetch('/leaves?status=pending&per_page=5')
                        .then(response => response.ok ? response.json() : Promise.reject('Failed'))
                        .then(data => {
                            if (data.data) {
                                this.pendingLeaves = data.data.map(leave => ({
                                    id: leave.id,
                                    worker_name: leave.worker?.name || '-',
                                    leave_type: leave.leave_type?.name || leave.leave_type || '-',
                                    total_days: leave.total_days || 0,
                                    date_range: (leave.start_date || '') + ' s/d ' + (leave.end_date || '')
                                }));
                                this.updateTotal();
                            }
                        })
                        .catch(() => {
                            this.pendingLeaves = [];
                        });

                    // Load pending documents
                    fetch('/worker-documents?status=pending&per_page=5')
                        .then(response => response.ok ? response.json() : Promise.reject('Failed'))
                        .then(data => {
                            if (data.data) {
                                this.pendingDocuments = data.data.map(doc => ({
                                    id: doc.id,
                                    worker_name: doc.worker?.name || '-',
                                    document_type: doc.document_type?.name || doc.document_type || '-',
                                    file_name: doc.file_name || '-'
                                }));
                                this.updateTotal();
                            }
                        })
                        .catch(() => {
                            this.pendingDocuments = [];
                        });
                },

                updateTotal() {
                    this.totalPending = this.pendingLeaves.length +
                                       this.pendingDocuments.length;
                }
            };
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

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
            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }

            // Auto-dismiss alerts only when explicitly marked with data-auto-dismiss="true"
            const autoAlerts = document.querySelectorAll('[data-auto-dismiss="true"]');
            autoAlerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 3000);
            });
        });
    </script>
</body>
</html>
