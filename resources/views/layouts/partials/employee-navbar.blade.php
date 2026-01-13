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
                <!-- Notifications -->
                <div class="relative"
                     x-data="notificationWidget()"
                     x-init="loadCount()"
                     @click.away="open = false">
                    <button @click="open = !open; if(open) loadNotifications()" class="relative p-2 transition duration-200 rounded-lg hover:bg-green-500">
                        <i class="text-xl fas fa-bell"></i>
                        <span x-show="unreadCount > 0" class="absolute top-0 right-0 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                            <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open"
                         x-transition
                         class="absolute right-0 z-50 w-80 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg top-full">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900">Notifikasi</h3>
                                <a href="{{ route('employee.notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>
                        <div class="overflow-y-auto max-h-96">
                            <template x-if="notifications.length === 0">
                                <div class="p-4 text-sm text-center text-gray-500">
                                    Tidak ada notifikasi baru
                                </div>
                            </template>
                            <template x-for="notif in notifications" :key="notif.id">
                                <div class="p-4 border-b border-gray-100 hover:bg-gray-50">
                                    <h4 class="text-sm font-semibold text-gray-900" x-text="notif.title"></h4>
                                    <p class="mt-1 text-xs text-gray-600" x-text="notif.message"></p>
                                    <p class="mt-1 text-xs text-gray-400" x-text="new Date(notif.created_at).toLocaleDateString('id-ID')"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

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
<script>
function notificationWidget() {
    return {
        open: false,
        unreadCount: 0,
        notifications: [],
        loadCount() {
            fetch('{{ route("employee.notifications.unread-count") }}')
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    this.unreadCount = data.count;
                }.bind(this));
        },
        loadNotifications() {
            fetch('{{ route("employee.notifications.unread") }}')
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    this.notifications = data;
                }.bind(this));
        }
    }
}
</script>
