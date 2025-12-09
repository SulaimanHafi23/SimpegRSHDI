{{-- filepath: resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SIMPEGRS HDI') }} - Welcome</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <!-- Main Container -->
    <div class="min-h-screen bg-gradient-to-br from-green-50 via-yellow-50 to-green-50 relative overflow-hidden">

        <!-- Animated Background (GREEN & YELLOW) -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-green-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-slow"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-slow animation-delay-2000"></div>
            <div class="absolute top-1/3 left-1/2 w-96 h-96 bg-green-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-slow animation-delay-4000"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10">
            <!-- Navigation -->
            <nav class="px-6 py-6">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="h-12 w-12 bg-gradient-to-br from-green-600 to-green-800 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ config('app.name', 'SIMPEGRS HDI') }}</h1>
                            <p class="text-xs text-gray-600">Employee Management System</p>
                        </div>
                    </div>

                    <!-- Sign In Button (GREEN) -->
                    <a href="{{ route('login') }}" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold px-6 py-2.5 text-sm rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-105">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </a>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="max-w-7xl mx-auto px-6 py-20 sm:py-32">
                <div class="grid lg:grid-cols-2 gap-12 items-center">

                    <!-- Left Content -->
                    <div class="space-y-8 animate-slide-right">
                        <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md border border-green-100">
                            <span class="flex h-2 w-2 relative mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-600"></span>
                            </span>
                            <span class="text-sm font-semibold text-gray-700">v2.0 Now Available</span>
                        </div>

                        <div>
                            <h2 class="text-5xl sm:text-6xl font-extrabold text-gray-900 leading-tight">
                                Welcome to
                                <span class="bg-gradient-to-r from-green-600 via-green-700 to-yellow-600 bg-clip-text text-transparent">
                                    SIMPEGRS HDI
                                </span>
                            </h2>
                            <p class="mt-6 text-xl text-gray-600 leading-relaxed">
                                Sistem Informasi Manajemen Pegawai yang modern dan efisien untuk
                                <span class="font-semibold text-gray-900">RSUD Haji Darlan Ismail</span>
                            </p>
                        </div>

                        <!-- Features Grid -->
                        <div class="grid grid-cols-2 gap-4 pt-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Attendance System</h3>
                                    <p class="text-sm text-gray-600">Real-time tracking</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check text-yellow-600"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Leave Management</h3>
                                    <p class="text-sm text-gray-600">Easy approval flow</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Payroll System</h3>
                                    <p class="text-sm text-gray-600">Automated process</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check text-yellow-600"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Reports & Analytics</h3>
                                    <p class="text-sm text-gray-600">Insightful data</p>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Buttons (GREEN & YELLOW) -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-4 relative z-20">
                            <a href="{{ route('login') }}" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold px-8 py-4 text-base rounded-lg shadow-md hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-105 group text-center">
                                <i class="fas fa-sign-in-alt mr-2 group-hover:translate-x-1 transition-transform"></i>
                                Get Started
                            </a>
                            <a href="#features" class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-lg shadow-md hover:shadow-xl border-2 border-gray-200 hover:border-green-300 transition-all duration-300 text-center cursor-pointer transform hover:scale-105">
                                <i class="fas fa-info-circle mr-2"></i>
                                Learn More
                            </a>
                        </div>

                        <!-- Trust Indicators -->
                        <div class="flex items-center space-x-6 pt-8 border-t border-gray-200">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">500+</div>
                                <div class="text-sm text-gray-600">Employees</div>
                            </div>
                            <div class="h-12 w-px bg-gray-300"></div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">99.9%</div>
                                <div class="text-sm text-gray-600">Uptime</div>
                            </div>
                            <div class="h-12 w-px bg-gray-300"></div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">24/7</div>
                                <div class="text-sm text-gray-600">Support</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Content - Dashboard Preview -->
                    <div class="relative animate-slide-left">
                        <!-- Main Card -->
                        <div class="relative z-10 bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                            <!-- Mock Dashboard -->
                            <div class="space-y-6">
                                <!-- Header -->
                                <div class="flex items-center justify-between pb-6 border-b border-gray-200">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">Dashboard Preview</h3>
                                        <p class="text-sm text-gray-600">Your workplace at a glance</p>
                                    </div>
                                    <div class="h-10 w-10 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-chart-line text-white"></i>
                                    </div>
                                </div>

                                <!-- Stats Grid (GREEN & YELLOW) -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-2xl">👥</span>
                                            <span class="text-xs font-semibold text-green-600 bg-green-200 px-2 py-1 rounded-full">+12%</span>
                                        </div>
                                        <div class="text-2xl font-bold text-green-900">245</div>
                                        <div class="text-xs text-green-700">Active Staff</div>
                                    </div>

                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-2xl">✅</span>
                                            <span class="text-xs font-semibold text-yellow-600 bg-yellow-200 px-2 py-1 rounded-full">+8%</span>
                                        </div>
                                        <div class="text-2xl font-bold text-yellow-900">98%</div>
                                        <div class="text-xs text-yellow-700">Attendance</div>
                                    </div>

                                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-2xl">📋</span>
                                            <span class="text-xs font-semibold text-green-600 bg-green-200 px-2 py-1 rounded-full">5</span>
                                        </div>
                                        <div class="text-2xl font-bold text-green-900">12</div>
                                        <div class="text-xs text-green-700">Pending</div>
                                    </div>

                                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-2xl">💰</span>
                                            <span class="text-xs font-semibold text-yellow-600 bg-yellow-200 px-2 py-1 rounded-full">✓</span>
                                        </div>
                                        <div class="text-2xl font-bold text-yellow-900">45M</div>
                                        <div class="text-xs text-yellow-700">Payroll</div>
                                    </div>
                                </div>

                                <!-- Activity List -->
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="h-8 w-8 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">JD</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">John Doe checked in</p>
                                            <p class="text-xs text-gray-600">2 minutes ago</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="h-8 w-8 bg-yellow-500 rounded-full flex items-center justify-center text-white text-xs font-bold">AS</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">Leave approved</p>
                                            <p class="text-xs text-gray-600">15 minutes ago</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="h-8 w-8 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">MR</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">New payslip generated</p>
                                            <p class="text-xs text-gray-600">1 hour ago</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Cards (GREEN & YELLOW) -->
                        <div class="absolute -top-6 -right-6 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-4 w-32 animate-float z-20">
                            <div class="text-center">
                                <div class="text-2xl mb-1">⏰</div>
                                <div class="text-xs text-white font-semibold">08:15 AM</div>
                                <div class="text-xs text-green-100">Check-in</div>
                            </div>
                        </div>

                        <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl shadow-xl p-4 w-32 border border-yellow-100 animate-float animation-delay-2000 z-20">
                            <div class="text-center">
                                <div class="text-2xl mb-1">📊</div>
                                <div class="text-xs text-gray-900 font-semibold">Reports</div>
                                <div class="text-xs text-gray-600">Ready</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features" class="max-w-7xl mx-auto px-6 py-20 border-t border-gray-200">
                <div class="text-center mb-16 animate-fade-in">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Powerful Features</h2>
                    <p class="text-lg text-gray-600">Everything you need to manage your workforce efficiently</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="card p-8 card-hover cursor-default border-t-4 border-green-500">
                        <div class="h-14 w-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-clock text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Smart Attendance</h3>
                        <p class="text-gray-600">Real-time attendance tracking with geolocation and face recognition support.</p>
                    </div>

                    <div class="card p-8 card-hover cursor-default border-t-4 border-yellow-500">
                        <div class="h-14 w-14 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-umbrella-beach text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Leave Management</h3>
                        <p class="text-gray-600">Streamlined leave request and approval process with automated notifications.</p>
                    </div>

                    <div class="card p-8 card-hover cursor-default border-t-4 border-green-500">
                        <div class="h-14 w-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-money-bill-wave text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Payroll Processing</h3>
                        <p class="text-gray-600">Automated salary calculation with tax computation and slip generation.</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="border-t border-gray-200 py-12">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <div class="text-center md:text-left">
                            <p class="text-sm text-gray-600">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                            <p class="text-xs text-gray-500 mt-1">RSUD Haji Darlan Ismail</p>
                        </div>
                        <div class="flex items-center space-x-6">
                            <a href="#" class="text-sm text-gray-600 hover:text-green-600 transition duration-200">Privacy</a>
                            <a href="#" class="text-sm text-gray-600 hover:text-green-600 transition duration-200">Terms</a>
                            <a href="#" class="text-sm text-gray-600 hover:text-green-600 transition duration-200">Contact</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
