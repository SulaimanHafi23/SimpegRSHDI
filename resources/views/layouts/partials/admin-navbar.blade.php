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
            <div class="relative" 
                 x-data="adminNotifications()" 
                 x-init="loadPendingRequests()"
                 @click.away="open = false">
                <button @click="open = !open; if(open) loadPendingRequests()" 
                        class="relative p-2 text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition duration-200">
                    <i class="fas fa-bell text-lg sm:text-xl"></i>
                    <span x-show="totalPending > 0" 
                          class="absolute top-1 right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                        <span x-text="totalPending > 9 ? '9+' : totalPending"></span>
                    </span>
                </button>

                <!-- Notifications Dropdown -->
                <div x-show="open"
                     x-transition
                     class="absolute right-0 z-50 w-96 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg top-full">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Pengajuan Pending</h3>
                            <span class="text-xs font-medium text-yellow-600" x-text="totalPending + ' pending'"></span>
                        </div>
                    </div>
                    <div class="overflow-y-auto max-h-96">
                        <template x-if="pendingLeaves.length === 0 && pendingOvertimes.length === 0 && pendingDocuments.length === 0">
                            <div class="p-4 text-sm text-center text-gray-500">
                                Tidak ada pengajuan pending
                            </div>
                        </template>
                        
                        <!-- Pending Leaves -->
                        <template x-if="pendingLeaves.length > 0">
                            <div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Cuti (<span x-text="pendingLeaves.length"></span>)
                                </div>
                                <template x-for="leave in pendingLeaves" :key="leave.id">
                                    <a :href="'/leaves/' + leave.id" 
                                       class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900" x-text="leave.worker_name"></h4>
                                                <p class="mt-1 text-xs text-gray-600" x-text="leave.leave_type + ' - ' + leave.total_days + ' hari'"></p>
                                                <p class="mt-1 text-xs text-gray-400" x-text="leave.date_range"></p>
                                            </div>
                                            <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">Pending</span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- Pending Overtimes -->
                        <template x-if="pendingOvertimes.length > 0">
                            <div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Lembur (<span x-text="pendingOvertimes.length"></span>)
                                </div>
                                <template x-for="overtime in pendingOvertimes" :key="overtime.id">
                                    <a :href="'/overtimes/' + overtime.id" 
                                       class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900" x-text="overtime.worker_name"></h4>
                                                <p class="mt-1 text-xs text-gray-600" x-text="overtime.total_hours + ' jam'"></p>
                                                <p class="mt-1 text-xs text-gray-400" x-text="overtime.date"></p>
                                            </div>
                                            <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">Pending</span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- Pending Documents -->
                        <template x-if="pendingDocuments.length > 0">
                            <div>
                                <div class="px-4 py-2 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Dokumen (<span x-text="pendingDocuments.length"></span>)
                                </div>
                                <template x-for="doc in pendingDocuments" :key="doc.id">
                                    <a :href="'/worker-documents/' + doc.id" 
                                       class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900" x-text="doc.worker_name"></h4>
                                                <p class="mt-1 text-xs text-gray-600" x-text="doc.document_type"></p>
                                                <p class="mt-1 text-xs text-gray-400" x-text="doc.file_name"></p>
                                            </div>
                                            <span class="px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full">Pending</span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            @if(auth()->check() && auth()->user()->hasRole('Employee'))
            <!-- Employee Notifications -->
            <div class="relative" x-data="{ openNotif: false, notifications: [], unreadCount: 0 }" x-init="fetch('{{ route('employee.notifications.unread') }}')
                        .then(r => r.json()).then(d => { notifications = d; unreadCount = notifications.length })" @click.away="openNotif = false">
                <button @click="openNotif = !openNotif; if(openNotif){ fetch('{{ route('employee.notifications.unread') }}').then(r=>r.json()).then(d => { notifications = d; unreadCount = notifications.length }) }"
                        class="relative p-2 text-gray-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition duration-200">
                    <i class="fas fa-envelope text-lg sm:text-xl"></i>
                    <span x-show="unreadCount > 0"
                          class="absolute top-1 right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                        <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                    </span>
                </button>

                <!-- Notifications Dropdown -->
                <div x-show="openNotif" x-transition class="absolute right-0 z-50 w-96 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg top-full">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Notifikasi Terbaru</h3>
                            <span class="text-xs font-medium text-yellow-600" x-text="unreadCount + ' baru'"></span>
                        </div>
                    </div>

                    <div class="overflow-y-auto max-h-96">
                        <template x-if="notifications.length === 0">
                            <div class="p-4 text-sm text-center text-gray-500">Tidak ada notifikasi baru</div>
                        </template>

                        <template x-for="notif in notifications" :key="notif.id">
                            <a :href="notif.data && notif.data.type === 'business_trip' ? '/employee/business-trips/' + notif.data.business_trip_id : '{{ route('employee.notifications.index') }}'"
                               class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900" x-text="notif.title"></h4>
                                        <p class="mt-1 text-xs text-gray-600" x-text="notif.message"></p>
                                        <p class="mt-1 text-xs text-gray-400" x-text="notif.created_at"></p>
                                    </div>

                                    <div class="ml-3 flex-shrink-0">
                                        <button @click.prevent="fetch('{{ url('employee/notifications') }}' + '/' + notif.id + '/mark-read', {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})
                                                .then(()=>{ notifications = notifications.filter(n=>n.id !== notif.id); unreadCount = notifications.length })"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Tandai dibaca">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>

                    <div class="p-4 border-t border-gray-200 text-sm text-center">
                        <button @click.prevent="fetch('{{ route('employee.notifications.mark-all-read') }}', {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}})
                                .then(()=>{ notifications = []; unreadCount = 0 })" class="text-blue-600">Tandai semua dibaca</button>
                        <a href="{{ route('employee.notifications.index') }}" class="ml-4 text-gray-600">Lihat semua</a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 sm:space-x-3 p-2 rounded-lg hover:bg-green-50 transition duration-200">
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
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i> Profile Saya
                    </a>
                    <a href="{{ route('admin.roles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i> Settings
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
</header>
