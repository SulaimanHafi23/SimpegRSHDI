{{-- filepath: resources/views/layouts/partials/workers-sidebar.blade.php --}}
<!-- Sidebar for Desktop/Tablet (hidden on mobile) -->
<aside class="hidden lg:block fixed left-0 top-0 h-screen w-64 border-r border-[#f5a623]/15 shadow-2xl z-30 pt-16 overflow-hidden" style="background:linear-gradient(160deg,#0a3d1f 0%,#0d2b17 100%)">
    <!-- Gold radial glow -->
    <div class="pointer-events-none absolute -top-12 -right-12 w-52 h-52 rounded-full opacity-25" style="background:radial-gradient(circle,#f5a623 0%,transparent 70%);filter:blur(36px)"></div>
    <div class="flex flex-col h-full">
        <!-- User Info -->
        <div class="relative p-6 border-b border-[#f5a623]/20 flex-shrink-0" style="background:rgba(245,166,35,0.06)">
            <div class="flex items-center space-x-3">
                @if(auth()->user()->photo)
                    <img src="{{ asset(auth()->user()->photo) }}" alt="{{ auth()->user()->name }}"
                         class="w-12 h-12 rounded-full object-cover border-2 border-[#f5a623]">
                @else
                    <div class="w-12 h-12 rounded-full bg-[#28a04f] flex items-center justify-center text-white text-lg font-bold border-2 border-[#f5a623]/40">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[#a7f3d0] truncate">{{ auth()->user()->worker->department->name ?? 'Pegawai' }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="relative flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('workers.dashboard') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('workers.dashboard') ? 'text-[#1a1a1a] font-semibold shadow-lg' : 'text-[#d1fae5] hover:bg-white/10 hover:text-white' }}"
               @if(request()->routeIs('workers.dashboard')) style="background:linear-gradient(135deg,#f5a623,#d97706)" @endif>
                <i class="fas fa-home w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('workers.attendance.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('workers.attendance*') ? 'text-[#1a1a1a] font-semibold shadow-lg' : 'text-[#d1fae5] hover:bg-white/10 hover:text-white' }}"
               @if(request()->routeIs('workers.attendance*')) style="background:linear-gradient(135deg,#f5a623,#d97706)" @endif>
                <i class="fas fa-camera w-5 text-center"></i>
                <span>Absensi</span>
            </a>

            <a href="{{ route('workers.schedule') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('workers.schedule') ? 'text-[#1a1a1a] font-semibold shadow-lg' : 'text-[#d1fae5] hover:bg-white/10 hover:text-white' }}"
               @if(request()->routeIs('workers.schedule')) style="background:linear-gradient(135deg,#f5a623,#d97706)" @endif>
                <i class="fas fa-calendar-alt w-5 text-center"></i>
                <span>Jadwal Shift</span>
            </a>

            <a href="{{ route('workers.leaves.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('workers.leaves*') ? 'text-[#1a1a1a] font-semibold shadow-lg' : 'text-[#d1fae5] hover:bg-white/10 hover:text-white' }}"
               @if(request()->routeIs('workers.leaves*')) style="background:linear-gradient(135deg,#f5a623,#d97706)" @endif>
                <i class="fas fa-calendar-plus w-5 text-center"></i>
                <span>Cuti</span>
            </a>

            <a href="{{ route('workers.documents') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('workers.documents') ? 'text-[#1a1a1a] font-semibold shadow-lg' : 'text-[#d1fae5] hover:bg-white/10 hover:text-white' }}"
               @if(request()->routeIs('workers.documents')) style="background:linear-gradient(135deg,#f5a623,#d97706)" @endif>
                <i class="fas fa-file-alt w-5 text-center"></i>
                <span>Dokumen</span>
            </a>

            <div class="border-t border-white/15 my-2"></div>

            <a href="{{ route('workers.profile') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('workers.profile') ? 'text-[#1a1a1a] font-semibold shadow-lg' : 'text-[#d1fae5] hover:bg-white/10 hover:text-white' }}"
               @if(request()->routeIs('workers.profile')) style="background:linear-gradient(135deg,#f5a623,#d97706)" @endif>
                <i class="fas fa-user w-5 text-center"></i>
                <span>Profile</span>
            </a>
        </nav>

        <!-- Logout Button -->
        <div class="relative p-4 border-t border-white/15">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-300 hover:bg-red-500/20 hover:text-red-200 transition">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
