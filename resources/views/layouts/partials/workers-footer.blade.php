{{-- filepath: resources/views/layouts/partials/workers-footer.blade.php --}}
<!-- Bottom Navigation - Only visible on mobile/tablet -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg lg:hidden z-30">
    <div class="flex justify-around items-center h-16">
        <a href="{{ route('workers.dashboard') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('workers.dashboard') ? 'text-green-600' : 'text-gray-600' }} hover:text-green-600 transition duration-200">
            <i class="fas fa-home text-xl"></i>
            <span class="text-xs font-medium">Home</span>
        </a>

        <a href="{{ route('workers.attendance.index') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('workers.attendance*') ? 'text-green-600' : 'text-gray-600' }} hover:text-green-600 transition duration-200">
            <i class="fas fa-camera text-xl"></i>
            <span class="text-xs font-medium">Absen</span>
        </a>

        <a href="{{ route('workers.schedule') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('workers.schedule') ? 'text-green-600' : 'text-gray-600' }} hover:text-green-600 transition duration-200">
            <i class="fas fa-calendar-alt text-xl"></i>
            <span class="text-xs font-medium">Jadwal</span>
        </a>

        <a href="{{ route('workers.profile') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('workers.profile') ? 'text-green-600' : 'text-gray-600' }} hover:text-green-600 transition duration-200">
            <i class="fas fa-user text-xl"></i>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>
</nav>
