{{-- filepath: resources/views/layouts/partials/admin-navbar.blade.php --}}
<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
    <div class="flex items-center justify-between px-4 sm:px-6 py-4">
        <!-- Mobile Menu Button -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition duration-200">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <!-- Page Title -->
        <div class="flex-1">
            <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h2>
            <p class="text-xs sm:text-sm text-gray-600 hidden sm:block">@yield('page-description', 'Welcome back!')</p>
        </div>

        <!-- Right Side -->
        <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Notifications -->
            <button class="relative p-2 text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition duration-200">
                <i class="fas fa-bell text-lg sm:text-xl"></i>
                <span class="absolute top-1 right-1 h-2 w-2 bg-yellow-500 rounded-full animate-pulse"></span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 sm:space-x-3 p-2 rounded-lg hover:bg-green-50 transition duration-200">
                    <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                         alt="Avatar"
                         class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border-2 border-green-500">
                    <div class="text-left hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-600">{{ auth()->user()->email }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-600 text-xs sm:text-sm"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     @click.away="open = false"
                     x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                    <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i> Profile Saya
                    </a>
                    <a href="{{ route('admin.settings.shifts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <hr class="my-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-5"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
