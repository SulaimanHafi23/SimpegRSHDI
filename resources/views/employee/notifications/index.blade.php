@extends('layouts.employee')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Notifikasi</h1>
        @if($notifications->total() > 0)
        <form action="{{ route('employee.notifications.mark-all-read') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                Tandai Semua Sudah Dibaca
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 alert-dismissible">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 alert-dismissible">
        {{ session('error') }}
    </div>
    @endif

    <!-- Filter Tabs -->
    <div class="mb-4 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('employee.notifications.index') }}" 
               class="@if(!request('is_read')) border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Semua
            </a>
            <a href="{{ route('employee.notifications.index', ['is_read' => 0]) }}" 
               class="@if(request('is_read') === '0') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Belum Dibaca
            </a>
            <a href="{{ route('employee.notifications.index', ['is_read' => 1]) }}" 
               class="@if(request('is_read') === '1') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Sudah Dibaca
            </a>
        </nav>
    </div>

    @if($notifications->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada notifikasi</h3>
        <p class="mt-1 text-sm text-gray-500">Anda belum memiliki notifikasi</p>
    </div>
    @else
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <ul class="divide-y divide-gray-200">
            @foreach($notifications as $notification)
            <li class="@if(!$notification->read_at) bg-blue-50 @endif hover:bg-gray-50 transition">
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h3 class="text-sm font-semibold text-gray-900 @if(!$notification->read_at) font-bold @endif">
                                    {{ $notification->title }}
                                </h3>
                                @if(!$notification->read_at)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Baru
                                </span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $notification->message }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                            @if(!$notification->read_at)
                            <form action="{{ route('employee.notifications.mark-read', $notification->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('employee.notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

<script>
    // Auto dismiss alerts
    setTimeout(function() {
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        });
    }, 3000);
</script>
@endsection
