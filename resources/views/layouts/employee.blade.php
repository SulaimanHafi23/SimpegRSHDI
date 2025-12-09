{{-- filepath: resources/views/layouts/employee.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen pb-20">
        <!-- Top Navbar -->
        @include('layouts.partials.employee-navbar')

        <!-- Page Content -->
        <main class="p-4">
            @if(session('success'))
                <x-ui.alert type="success" :message="session('success')" />
            @endif

            @if(session('error'))
                <x-ui.alert type="error" :message="session('error')" />
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bottom Navigation -->
    @include('layouts.partials.employee-footer')

    @stack('scripts')
</body>
</html>
