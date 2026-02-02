{{-- filepath: resources/views/layouts/admin.blade.php --}}
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
            <!-- Navbar -->
            @include('layouts.partials.admin-navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8 w-full max-w-full {{ auth()->check() && auth()->user()->hasRole('Employee') ? 'pb-20 lg:pb-8' : '' }}">
                <!-- Breadcrumb -->
                @if(isset($breadcrumbs))
                    <x-ui.breadcrumb :items="$breadcrumbs" />
                @endif

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
                        <x-ui.alert type="success" :message="session('success')" />
                    @endif

                    @if(session('error'))
                        <x-ui.alert type="error" :message="session('error')" />
                    @endif

                    @if(session('warning'))
                        <x-ui.alert type="warning" :message="session('warning')" />
                    @endif

                    @if(session('info'))
                        <x-ui.alert type="info" :message="session('info')" />
                    @endif
                @else
                    {{-- Use Sweet Alert for other pages --}}
                    <x-sweet-alert />
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.partials.admin-footer')
        </div>
    </div>

    <!-- Bottom Navigation for Employee Role -->
    @if(auth()->check() && auth()->user()->hasRole('Employee'))
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
                pendingOvertimes: [],
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

                    // Load pending overtimes
                    fetch('/overtimes?status=pending&per_page=5')
                        .then(response => response.ok ? response.json() : Promise.reject('Failed'))
                        .then(data => {
                            if (data.data) {
                                this.pendingOvertimes = data.data.map(ot => ({
                                    id: ot.id,
                                    worker_name: ot.worker?.name || '-',
                                    total_hours: ot.total_hours || 0,
                                    date: ot.overtime_date || ot.date || '-'
                                }));
                                this.updateTotal();
                            }
                        })
                        .catch(() => {
                            this.pendingOvertimes = [];
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
                                       this.pendingOvertimes.length +
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
