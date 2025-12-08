{{-- filepath: resources/views/dashboard.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - SIMPEG RSHDI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    {{-- Navbar --}}
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-indigo-600">SIMPEG RSHDI</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 text-sm">
                        <strong>{{ Auth::user()->worker->full_name ?? Auth::user()->username }}</strong>
                        <span class="text-gray-500">({{ Auth::user()->roles->pluck('name')->implode(', ') }})</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-150">
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
            
            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Dashboard Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Dashboard
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- User Info Card --}}
                        <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-200">
                            <h3 class="text-lg font-semibold text-indigo-800 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Informasi User
                            </h3>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex justify-between">
                                    <span class="font-medium">Username:</span>
                                    <span>{{ Auth::user()->username }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium">Email:</span>
                                    <span>{{ Auth::user()->email }}</span>
                                </li>
                                <li class="flex justify-between">
                                    <span class="font-medium">Role:</span>
                                    <span class="text-indigo-600 font-semibold">{{ Auth::user()->roles->pluck('name')->implode(', ') }}</span>
                                </li>
                                @if(Auth::user()->worker)
                                    <li class="flex justify-between">
                                        <span class="font-medium">NIP:</span>
                                        <span>{{ Auth::user()->worker->nip }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="font-medium">Posisi:</span>
                                        <span>{{ Auth::user()->worker->position->name ?? '-' }}</span>
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
                                @role('Super Admin')
                                    <a href="{{ route('admin.dashboard') }}" class="block p-3 bg-white rounded-lg hover:bg-indigo-50 border border-gray-200 hover:border-indigo-300 transition">
                                        <span class="text-gray-800 font-medium">Admin Dashboard</span>
                                    </a>
                                @endrole
                                
                                @role('HR')
                                    <a href="{{ route('hr.dashboard') }}" class="block p-3 bg-white rounded-lg hover:bg-indigo-50 border border-gray-200 hover:border-indigo-300 transition">
                                        <span class="text-gray-800 font-medium">HR Dashboard</span>
                                    </a>
                                @endrole
                                
                                @role('Manager')
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
