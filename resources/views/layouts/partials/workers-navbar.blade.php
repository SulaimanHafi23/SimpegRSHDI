{{-- filepath: resources/views/layouts/partials/workers-navbar.blade.php --}}
<header class="bg-gradient-to-r from-green-600 to-green-700 text-white shadow-lg fixed top-0 left-0 right-0 z-40">
    <div class="px-4 lg:px-6 py-3 lg:py-4">
        <div class="flex items-center justify-between">
            <!-- Left Section -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle (hidden on desktop) -->
                <button id="mobile-menu-toggle" class="lg:hidden p-2 hover:bg-green-500 rounded-lg transition duration-200">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Logo & Title for Desktop -->
                <div class="hidden lg:block">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hospital text-green-700 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold">SIMPEG RSHDI</h1>
                            <p class="text-xs text-yellow-100">Portal Pegawai</p>
                        </div>
                    </div>
                </div>

                <!-- Greeting for Mobile -->
                <div class="lg:hidden">
                    <h1 class="text-base font-bold">Hi, {{ Str::limit(auth()->user()->worker->name ?? auth()->user()->name, 15) }} 👋</h1>
                    <p class="text-xs text-yellow-100 hidden sm:block">{{ \Carbon\Carbon::now()->isoFormat('D MMM YYYY') }}</p>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-2 lg:space-x-4">
                <!-- Date & Time for Desktop -->
                <div class="hidden lg:block text-right mr-4">
                    <p class="text-sm font-semibold">{{ \Carbon\Carbon::now()->isoFormat('dddd') }}</p>
                    <p class="text-xs text-yellow-100">{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}</p>
                </div>

                <!-- Notifications -->
                <button class="relative p-2 hover:bg-green-500 rounded-lg transition duration-200">
                    <i class="fas fa-bell text-lg lg:text-xl"></i>
                    <span class="absolute top-1 right-1 h-2 w-2 bg-yellow-400 rounded-full animate-pulse"></span>
                </button>

                <!-- User Menu Dropdown (Desktop) -->
                <div class="hidden lg:block relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 p-2 hover:bg-green-500 rounded-lg transition duration-200">
                        @if(auth()->user()->photo)
                            <img src="{{ asset(auth()->user()->photo) }}" alt="{{ auth()->user()->name }}" 
                                 class="w-8 h-8 rounded-full object-cover border-2 border-yellow-300">
                        @else
                            <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center text-green-700 text-sm font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                        <a href="{{ route('workers.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="{{ route('admin.settings.shifts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-2"></i> Pengaturan
                        </a>
                        <hr class="my-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Sidebar Overlay -->
<div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<!-- Mobile Sidebar -->
<aside id="mobile-sidebar" class="fixed left-0 top-0 h-screen w-64 bg-white shadow-lg z-50 transform -translate-x-full transition-transform duration-300 lg:hidden">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="p-4 bg-gradient-to-r from-green-600 to-green-700 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hospital text-green-700 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">SIMPEG</h2>
                        <p class="text-xs text-yellow-100">Portal Pegawai</p>
                    </div>
                </div>
                <button id="mobile-sidebar-close" class="p-2 hover:bg-green-500 rounded-lg transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                @if(auth()->user()->photo)
                    <img src="{{ asset(auth()->user()->photo) }}" alt="{{ auth()->user()->name }}" 
                         class="w-12 h-12 rounded-full object-cover border-2 border-green-500">
                @else
                    <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white text-lg font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->worker->position->name ?? 'Pegawai' }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('workers.dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-home w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('workers.attendance.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.attendance*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-camera w-5 text-center"></i>
                <span>Absensi</span>
            </a>

            <a href="{{ route('workers.schedule') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.schedule') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-alt w-5 text-center"></i>
                <span>Jadwal Shift</span>
            </a>

            <a href="{{ route('workers.leaves.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.leaves*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-plus w-5 text-center"></i>
                <span>Cuti</span>
            </a>

            <a href="{{ route('workers.overtimes.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.overtimes*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-clock w-5 text-center"></i>
                <span>Lembur</span>
            </a>

            <a href="{{ route('workers.documents') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.documents') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-file-alt w-5 text-center"></i>
                <span>Dokumen</span>
            </a>

            <div class="border-t border-gray-200 my-2"></div>

            <a href="{{ route('workers.profile') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.profile') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-user w-5 text-center"></i>
                <span>Profile</span>
            </a>
        </nav>

        <!-- Logout Button -->
        <div class="p-4 border-t border-gray-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileSidebarClose = document.getElementById('mobile-sidebar-close');
    const mobileSidebarOverlay = document.getElementById('mobile-sidebar-overlay');

    function openMobileSidebar() {
        mobileSidebar.classList.remove('-translate-x-full');
        mobileSidebarOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        mobileSidebar.classList.add('-translate-x-full');
        mobileSidebarOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', openMobileSidebar);
    }

    if (mobileSidebarClose) {
        mobileSidebarClose.addEventListener('click', closeMobileSidebar);
    }

    if (mobileSidebarOverlay) {
        mobileSidebarOverlay.addEventListener('click', closeMobileSidebar);
    }

    // Close sidebar when clicking a link
    const sidebarLinks = mobileSidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', closeMobileSidebar);
    });
});
</script>
