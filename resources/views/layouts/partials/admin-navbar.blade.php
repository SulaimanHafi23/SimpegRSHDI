{{-- filepath: resources/views/layouts/partials/admin-navbar.blade.php --}}
<header class="sticky top-0 z-30 text-white shadow-lg bg-gradient-to-r from-green-600 to-green-700">
    <div class="px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Mobile Menu Button -->
            <button onclick="toggleUnifiedSidebar()" class="p-2 mr-3 transition duration-200 rounded-lg lg:hidden hover:bg-green-500">
                <i class="text-xl fas fa-bars"></i>
            </button>

            <!-- Page Title -->
            <div class="flex-1">
                <h2 class="text-lg font-bold sm:text-xl lg:text-2xl">@yield('page-title', 'Dashboard')</h2>
                <p class="hidden text-xs text-yellow-100 sm:block sm:text-sm">@yield('page-description', 'Welcome back!')</p>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center p-2 space-x-2 transition duration-200 rounded-lg sm:space-x-3 hover:bg-green-500">
                    @php
                        $worker = auth()->user()->worker ?? null;
                        $user = auth()->user();
                        $avatarUrl = null;
                        if ($worker && ($worker->photo_url ?? false) && Storage::disk('public')->exists($worker->photo_url)) {
                            $avatarUrl = Storage::url($worker->photo_url);
                        } elseif (($user->photo ?? false) && Storage::disk('public')->exists($user->photo)) {
                            $avatarUrl = Storage::url($user->photo);
                        } else {
                            $nameForAvatar = $worker->name ?? $user->username ?? $user->email ?? $user->name ?? '';
                            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($nameForAvatar);
                        }
                    @endphp
                    <img src="{{ $avatarUrl }}"
                         alt="Avatar"
                         class="w-8 h-8 border-2 border-yellow-400 rounded-full sm:h-10 sm:w-10">
                    <div class="hidden text-left sm:block">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-yellow-100">{{ auth()->user()->email }}</p>
                    </div>
                    <i class="text-xs text-white fas fa-chevron-down sm:text-sm"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     @click.away="open = false"
                     x-transition
                     class="absolute right-0 z-50 w-48 py-2 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="mr-2 fas fa-user"></i> Profile Saya
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="mr-2 fas fa-cog"></i> Settings
                    </a>
                    <hr class="my-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50">
                            <i class="mr-2 fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
