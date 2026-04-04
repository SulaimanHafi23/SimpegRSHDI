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

        <div class="pt-16 lg:pt-20">
            <!-- Sidebar for Desktop/Tablet -->
            @include('layouts.partials.workers-sidebar')

            <!-- Page Content -->
            <div class="lg:ml-64">
                <main class="p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8">
                <div class="w-full">
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

                    @if(session('error'))
                        <x-ui.alert type="error" :message="session('error')" />
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

                    @yield('content')
                </div>
            </main>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation for Mobile -->
    @include('layouts.partials.workers-footer')

    @stack('scripts')
</body>
</html>
