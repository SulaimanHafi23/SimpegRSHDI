{{-- filepath: resources/views/layouts/partials/employee-navbar.blade.php --}}
<header class="sticky top-0 z-30 text-white shadow-lg bg-gradient-to-r from-green-600 to-green-700">
    <div class="px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Mobile Menu Button -->
            <button onclick="toggleUnifiedSidebar()" class="p-2 mr-3 transition duration-200 rounded-lg lg:hidden hover:bg-green-500">
                <i class="text-xl fas fa-bars"></i>
            </button>

            <!-- Logo & Greeting -->
            <div class="flex-1">
                @php
                    $worker = auth()->user()->worker ?? null;
                    $displayName = $worker->name ?? auth()->user()->username ?? auth()->user()->name ?? auth()->user()->email ?? '';
                @endphp
                <h1 class="text-lg font-bold">Hi, {{ $displayName }} 👋</h1>
                <p class="text-xs text-yellow-100">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>

            <!-- Right Side Icons -->
            <div class="flex items-center space-x-2">
                <!-- Profile Dropdown -->
                <div class="relative hidden sm:block" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center p-2 space-x-2 transition duration-200 rounded-lg hover:bg-green-500">
                        @php
                            $user = auth()->user();
                            if ($worker && ($worker->photo_url ?? false) && Storage::disk('public')->exists($worker->photo_url)) {
                                $avatarUrl = Storage::url($worker->photo_url);
                            } elseif (($user->photo ?? false) && Storage::disk('public')->exists($user->photo)) {
                                $avatarUrl = Storage::url($user->photo);
                            } else {
                                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($displayName);
                            }
                        @endphp
                        <img src="{{ $avatarUrl }}"
                             alt="Avatar"
                             class="object-cover w-8 h-8 border-2 border-yellow-300 rounded-full">
                        <i class="text-xs fas fa-chevron-down"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 z-50 w-48 py-2 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
                        <a href="{{ route('employee.profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="mr-2 fas fa-user"></i> Profile Saya
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
    </div>
</header>
