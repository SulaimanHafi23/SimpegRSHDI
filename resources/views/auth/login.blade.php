{{-- filepath: resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">

        <!-- LEFT SIDE - Branding & Image (GREEN & YELLOW THEME) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-green-600 via-green-700 to-green-900 relative overflow-hidden">
            <!-- Animated Background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-yellow-300 rounded-full mix-blend-overlay filter blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-400 rounded-full mix-blend-overlay filter blur-3xl animate-pulse" style="animation-delay: 4s;"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-between p-12 text-white w-full">
                <!-- Logo & Title -->
                <div>
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="h-14 w-14 bg-white/20 backdrop-blur-lg rounded-xl flex items-center justify-center border border-white/30 shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">SIMPEGRS HDI</h1>
                            <p class="text-sm text-yellow-100">Employee Management System</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="space-y-8">
                    <!-- Hospital Image/Illustration -->
                    <div class="relative">
                        <div class="p-8 rounded-2xl border-2 border-yellow-300/30 backdrop-blur-xl shadow-xl bg-white/10">
                            <div class="aspect-video bg-white/10 rounded-xl flex items-center justify-center overflow-hidden ring-2 ring-yellow-400/20">
                                <!-- Replace with actual hospital image -->
                                <img
                                    src="{{ asset('images/login.jpeg') }}"
                                    alt="RSUD Haji Darlan Ismail"
                                    class="w-full h-full object-cover"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >
                                <!-- Fallback Icon -->
                                <div class="hidden flex-col items-center justify-center space-y-4">
                                    <i class="fas fa-hospital text-6xl text-yellow-100/60"></i>
                                    <p class="text-lg font-semibold text-yellow-50/80">RSUD Haji Darlan Ismail</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <div class="space-y-4">
                        <h2 class="text-3xl font-bold leading-tight">
                            Sistem Informasi Manajemen<br>
                            <span class="text-yellow-300">Pegawai yang Modern</span>
                        </h2>
                        <p class="text-lg text-green-100">
                            Kelola data pegawai, absensi, cuti, dan payroll dengan mudah dan efisien
                        </p>
                    </div>

                    <!-- Features -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl border border-yellow-300/20 backdrop-blur-lg shadow-lg bg-white/10">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-yellow-400/30 rounded-lg flex items-center justify-center ring-2 ring-yellow-300/30">
                                    <i class="fas fa-user-check text-yellow-100"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-yellow-100">500+</div>
                                    <div class="text-sm text-green-100">Employees</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-yellow-300/20 backdrop-blur-lg shadow-lg bg-white/10">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 bg-yellow-400/30 rounded-lg flex items-center justify-center ring-2 ring-yellow-300/30">
                                    <i class="fas fa-chart-line text-yellow-100"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-yellow-100">99.9%</div>
                                    <div class="text-sm text-green-100">Uptime</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between text-sm text-green-100/60">
                    <p>&copy; {{ date('Y') }} RSUD Haji Darlan Ismail</p>
                    <div class="flex items-center space-x-4">
                        <span class="hover:text-yellow-300 transition duration-200 cursor-pointer">Privacy</span>
                        <span class="hover:text-yellow-300 transition duration-200 cursor-pointer">Terms</span>
                        <span class="hover:text-yellow-300 transition duration-200 cursor-pointer">Help</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE - Login Form with Background Image -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden">
            <!-- Background Image with Opacity -->
            <div class="absolute inset-0 z-0">
                <img
                    src="{{ asset('images/hospital.jpg') }}"
                    alt="Background"
                    class="w-full h-full object-cover"
                    onerror="this.style.display='none';"
                >
                <!-- Overlay untuk transparansi & blur -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-gray-15/90 to-white/65 backdrop-blur-sm"></div>
            </div>

            <!-- Animated Background Decoration (Tetap ada untuk fallback) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
                <div class="absolute top-0 right-0 w-96 h-96 bg-green-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-96 h-96 bg-yellow-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
            </div>

            <!-- Login Card -->
            <div class="w-full max-w-md relative z-10">
                <!-- Mobile Logo (Hidden on Desktop) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="inline-flex h-16 w-16 bg-gradient-to-br from-green-600 to-green-800 rounded-xl items-center justify-center shadow-lg mb-4">
                        <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">SIMPEGRS HDI</h1>
                    <p class="text-sm text-gray-600">Employee Management System</p>
                </div>

                <div class="p-6 sm:p-8 shadow-2xl rounded-xl border-t-4 border-green-600 backdrop-blur-lg bg-white/95">
                    <!-- Header -->
                    <div class="mb-6 sm:mb-8">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome Back! 👋</h2>
                        <p class="text-sm sm:text-base text-gray-600">Sign in to access your account</p>
                    </div>

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                                <div>
                                    <strong class="font-semibold text-red-800">Error!</strong>
                                    <p class="text-sm text-red-700 mt-1">{{ $errors->first() }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 text-xl mr-3 mt-0.5"></i>
                                <div>
                                    <strong class="font-semibold text-green-800">Success!</strong>
                                    <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                        @csrf

                        <!-- NIP/Email Field -->
                        <div>
                            <label for="login" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user text-green-600 mr-1"></i>
                                NIP / Email
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    name="login"
                                    id="login"
                                    value="{{ old('login') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white/90 transition duration-200 @error('login') border-red-500 @enderror"
                                    placeholder="Enter your NIP or Email"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('login')
                                <p class="text-red-600 text-sm mt-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock text-green-600 mr-1"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white/90 transition duration-200 @error('password') border-red-500 @enderror"
                                    placeholder="Enter your password"
                                    required
                                >
                                <button
                                    type="button"
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-green-600 transition duration-200"
                                >
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-600 text-sm mt-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="remember_me" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded cursor-pointer">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-3 text-base group bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-md hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 active:scale-95">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Demo Credentials</span>
                        </div>
                    </div>

                    <!-- Demo Info -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm">
                        <p class="font-semibold text-green-800 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Super Admin Login:
                        </p>
                        <div class="space-y-1 text-green-700">
                            <p><strong>Email:</strong> superadmin@example.com</p>
                            <p><strong>Password:</strong> password</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Links -->
                <div class="text-center mt-6 text-sm text-gray-600 backdrop-blur-sm bg-white/50 px-4 py-2 rounded-lg">
                    <p>Need help? <span class="text-green-600 hover:text-green-700 font-semibold cursor-pointer">Contact Support</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
