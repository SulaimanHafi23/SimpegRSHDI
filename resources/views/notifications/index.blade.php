@extends('layouts.admin')

@section('title', 'Notifikasi')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifikasi</h1>
            <p class="text-gray-600">Kelola notifikasi Anda</p>
        </div>
        @if($unreadCount > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-check-double mr-2"></i>Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Notifikasi</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $notifications->total() }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-bell text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Belum Dibaca</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $unreadCount }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-envelope text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sudah Dibaca</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $notifications->total() - $unreadCount }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-envelope-open text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('notifications.index') }}" method="GET" class="flex gap-4">
            <div class="flex-1">
                <select name="is_read" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Belum Dibaca</option>
                    <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Sudah Dibaca</option>
                </select>
            </div>

            <div class="flex-1">
                <select name="type" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="leave_approved" {{ request('type') === 'leave_approved' ? 'selected' : '' }}>Cuti Disetujui</option>
                    <option value="leave_rejected" {{ request('type') === 'leave_rejected' ? 'selected' : '' }}>Cuti Ditolak</option>
                    <option value="overtime_approved" {{ request('type') === 'overtime_approved' ? 'selected' : '' }}>Lembur Disetujui</option>
                    <option value="overtime_rejected" {{ request('type') === 'overtime_rejected' ? 'selected' : '' }}>Lembur Ditolak</option>
                    <option value="business_trip_approved" {{ request('type') === 'business_trip_approved' ? 'selected' : '' }}>Perjalanan Dinas Disetujui</option>
                    <option value="business_trip_rejected" {{ request('type') === 'business_trip_rejected' ? 'selected' : '' }}>Perjalanan Dinas Ditolak</option>
                    <option value="shift_swap_request" {{ request('type') === 'shift_swap_request' ? 'selected' : '' }}>Permintaan Tukar Shift</option>
                </select>
            </div>

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>

            <a href="{{ route('notifications.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a>
        </form>
    </div>

    <!-- Notifications List -->
    @if($notifications->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="divide-y divide-gray-200">
            @foreach($notifications as $notification)
            <div class="p-6 hover:bg-gray-50 transition {{ $notification->is_read ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-4 flex-1">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            @if(str_contains($notification->type, 'approved'))
                                <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-xl"></i>
                                </div>
                            @elseif(str_contains($notification->type, 'rejected'))
                                <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-times text-red-600 text-xl"></i>
                                </div>
                            @elseif(str_contains($notification->type, 'request'))
                                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                                </div>
                            @else
                                <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <i class="fas fa-bell text-yellow-600 text-xl"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <h3 class="text-base font-semibold text-gray-900">{{ $notification->title }}</h3>
                                @if(!$notification->is_read)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Baru
                                    </span>
                                @endif
                            </div>
                            <p class="text-gray-700 text-sm">{{ $notification->message }}</p>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 ml-4">
                        @if(!$notification->is_read)
                        <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Tandai dibaca">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif

    @else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-bell-slash text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak ada notifikasi</h3>
        <p class="text-gray-600">
            @if(request('is_read') || request('type'))
                Tidak ada notifikasi yang cocok dengan filter Anda.
            @else
                Anda belum memiliki notifikasi.
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
