{{-- filepath: resources/views/dashboard.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - SIDIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    {{-- Navbar --}}
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-indigo-600">SIDIA</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <span class="text-gray-700 text-xs sm:text-sm hidden sm:inline">
                        <strong>{{ \Illuminate\Support\Facades\Auth::user()->worker->full_name ?? \Illuminate\Support\Facades\Auth::user()->username }}</strong>
                        <span class="text-gray-500">({{ \Illuminate\Support\Facades\Auth::user()->roles->pluck('name')->implode(', ') }})</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded text-xs sm:text-sm transition duration-150">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Sweet Alert for notifications --}}
            <x-sweet-alert />

            {{-- Dashboard Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Dashboard
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        {{-- User Info Card --}}
                        <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-200">
                            <h3 class="text-lg font-semibold text-indigo-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Informasi User
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex justify-between">
                                    <span class="font-medium">Username:</span>
                                    <span class="truncate ml-2">{{ \Illuminate\Support\Facades\Auth::user()->username }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium">Email:</span>
                                    <span class="truncate ml-2">{{ \Illuminate\Support\Facades\Auth::user()->email }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium">Role:</span>
                                    <span class="text-indigo-600 font-semibold">{{ \Illuminate\Support\Facades\Auth::user()->roles->pluck('name')->implode(', ') }}</span>
                                </li>
                                @if(\Illuminate\Support\Facades\Auth::user()->worker)
                                    <li class="flex justify-between">
                                        <span class="font-medium">NIP:</span>
                                        <span>{{ \Illuminate\Support\Facades\Auth::user()->worker->nip }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="font-medium">Posisi:</span>
                                        <span>{{ \Illuminate\Support\Facades\Auth::user()->worker->department->name ?? '-' }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        {{-- Quick Links Card --}}
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Quick Links
                            </h3>
                            <div class="space-y-2">
                                @can('dashboard.admin')
                                    <a href="{{ route('admin.dashboard') }}" class="block p-3 bg-white rounded-lg hover:bg-indigo-50 border border-gray-200 hover:border-indigo-300 transition">
                                        <span class="text-gray-800 font-medium">Admin Dashboard</span>
                                    </a>
                                @endrole

                                @can('dashboard.hr')
                                    <a href="{{ route('hr.dashboard') }}" class="block p-3 bg-white rounded-lg hover:bg-indigo-50 border border-gray-200 hover:border-indigo-300 transition">
                                        <span class="text-gray-800 font-medium">HR Dashboard</span>
                                    </a>
                                @endrole

                                @can('dashboard.manager')
                                    <a href="{{ route('manager.dashboard') }}" class="block p-3 bg-white rounded-lg hover:bg-indigo-50 border border-gray-200 hover:border-indigo-300 transition">
                                        <span class="text-gray-800 font-medium">Manager Dashboard</span>
                                    </a>
                                @endrole

                                <a href="#" class="block p-3 bg-white rounded-lg hover:bg-indigo-50 border border-gray-200 hover:border-indigo-300 transition">
                                    <span class="text-gray-800 font-medium">Profile Saya</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
