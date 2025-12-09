{{-- filepath: resources/views/layouts/partials/employee-navbar.blade.php --}}
<header class="bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg sticky top-0 z-50">
    <div class="px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo & Greeting -->
            <div>
                <h1 class="text-lg font-bold">Hi, {{ auth()->user()->worker->name ?? auth()->user()->name }} 👋</h1>
                <p class="text-xs text-primary-100">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>

            <!-- Notifications -->
            <button class="relative p-2 hover:bg-primary-500 rounded-lg transition duration-200">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full animate-pulse"></span>
            </button>
        </div>
    </div>
</header>
