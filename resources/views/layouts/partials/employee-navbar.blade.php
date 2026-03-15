{{-- filepath: resources/views/layouts/partials/employee-navbar.blade.php --}}
<header class="sticky top-0 z-30 text-white shadow-2xl border-b border-[#f5a623]/25" style="background:linear-gradient(135deg,#0a3d1f 0%,#155a2e 100%)">
    <div class="px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Mobile Menu Button -->
            <button onclick="toggleUnifiedSidebar()" class="p-2 mr-3 transition duration-200 rounded-lg lg:hidden hover:bg-[#1e7a3e]">
                <i class="text-xl fas fa-bars"></i>
            </button>

            <!-- Logo & Greeting -->
            <div class="flex-1">
                @php
                    $worker = auth()->user()->worker ?? null;
                    $displayName = $worker->name ?? auth()->user()->username ?? auth()->user()->name ?? auth()->user()->email ?? '';
                    $notificationCount = 0;
                    $recentNotifications = collect();
                    try {
                        $notificationCount = auth()->user()->unreadNotifications()->count();
                        $recentNotifications = auth()->user()->unreadNotifications()->latest()->limit(5)->get();
                    } catch (\Throwable $e) {
                        $notificationCount = 0;
                        $recentNotifications = collect();
                    }
                @endphp
                <h1 class="text-lg font-bold">Hi, {{ $displayName }} 👋</h1>
                <p class="text-xs text-yellow-100">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>

            <!-- Right Side Icons -->
            <div class="flex items-center space-x-2">
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <a href="{{ route('employee.notifications.index') }}"
                       class="relative inline-flex items-center justify-center w-10 h-10 rounded-full border border-white/20 bg-white/10 transition hover:bg-white/20"
                       title="Notifikasi">
                        <i class="fas fa-bell text-sm"></i>
                        @if($notificationCount > 0)
                            <span class="absolute -top-1 -right-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">
                                {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Notification Preview Dropdown -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute right-0 top-12 z-50 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-4 py-3" style="background:linear-gradient(135deg,#0a3d1f 0%,#155a2e 100%)">
                            <span class="text-sm font-semibold text-white">Notifikasi</span>
                            @if($notificationCount > 0)
                                <span class="text-xs text-green-100">{{ $notificationCount }} belum dibaca</span>
                            @endif
                        </div>
                        <!-- List -->
                        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                            @forelse($recentNotifications as $notif)
                                <div class="flex items-start gap-3 px-4 py-3 hover:bg-green-50 transition-colors">
                                    <div class="mt-1.5 flex-shrink-0 w-2 h-2 rounded-full bg-green-500"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">
                                            {{ $notif->data['title'] ?? 'Notifikasi' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                            {{ $notif->data['message'] ?? '-' }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            {{ $notif->created_at?->diffForHumans() ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                                    <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                    <p class="text-sm">Tidak ada notifikasi baru</p>
                                </div>
                            @endforelse
                        </div>
                        <!-- Footer -->
                        <a href="{{ route('employee.notifications.index') }}"
                           class="block text-center text-sm font-medium text-green-700 hover:text-green-900 hover:bg-green-50 py-3 border-t border-gray-100 transition-colors">
                            Lihat Semua Notifikasi &rarr;
                        </a>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative hidden sm:block" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center p-2 space-x-2 transition duration-200 rounded-lg hover:bg-green-500">
                        @php
                            $user = auth()->user();
                            if ($worker && ($worker->photo_url ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo_url)) {
                                $avatarUrl = \Illuminate\Support\Facades\Storage::url($worker->photo_url);
                            } elseif (($user->photo ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
                                $avatarUrl = \Illuminate\Support\Facades\Storage::url($user->photo);
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
