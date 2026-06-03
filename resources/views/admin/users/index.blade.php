@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Manajemen User"
        description="Kelola akun pengguna sistem"
        icon="fas fa-users-cog">
        <x-slot:actions>
            @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('user.manage'))
                <x-button
                    variant="success"
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.users.create') }}'">
                    Tambah User
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.users.index') }}">
        <x-form.input
            name="search"
            label="Pencarian"
            placeholder="Cari nama, email, username..."
            :value="$filters['search'] ?? ''" />

        <x-form.select
            name="role"
            label="Role"
            :selected="$filters['role'] ?? ''"
            placeholder="Semua Role">
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="is_active"
            label="Status"
            :options="[
                '1' => 'Aktif',
                '0' => 'Nonaktif',
                'deleted' => 'Terhapus',
                'all' => 'Semua'
            ]"
            :selected="$filters['is_active'] ?? ''"
            placeholder="Semua Status" />
    </x-filter-section>

    {{-- Data Table --}}
    <x-card>
        @if($users->isEmpty())
            <x-empty-state
                icon="fas fa-users"
                title="Tidak ada data user"
                description="Silakan tambahkan user baru"
                actionText="Tambah User"
                :actionUrl="route('admin.users.create')" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>User</x-table.cell>
                        <x-table.cell header>Email / Username</x-table.cell>
                        <x-table.cell header>Role</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Last Login</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($users as $user)
                    <x-table.row>
                        <x-table.cell>
                            <div class="flex items-center">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="flex items-center hover:opacity-90" title="Lihat profil {{ $user->name }}">
                    @if($user->worker && ($user->worker->photo_url ?? false) && Storage::disk('public')->exists($user->worker->photo_url))
                        <img class="h-10 w-10 rounded-full object-cover"
                             src="{{ Storage::url($user->worker->photo_url) }}"
                             alt="Foto {{ $user->name }}">
                        @else
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                <span class="text-green-600 font-semibold text-lg">
                                                    {{ strtoupper(substr($user->username ?? $user->name ?? '', 0, 1)) }}
                                                </span>
                                            </div>
                                    @endif

                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        @if($user->worker)
                                            <div class="text-sm text-gray-500">{{ $user->worker->department->name ?? '-' }}</div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            <div class="text-sm text-gray-500">{{ $user->username }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            @foreach($user->roles as $role)
                                <x-badge variant="primary" class="mr-1">{{ ucfirst($role->name) }}</x-badge>
                            @endforeach
                        </x-table.cell>

                        <x-table.cell>
                            @if($user->trashed())
                                <x-badge variant="danger">Terhapus</x-badge>
                                <div class="text-xs text-gray-500 mt-1">{{ $user->deleted_at?->diffForHumans() }}</div>
                            @else
                                <x-badge :variant="$user->is_active ? 'success' : 'danger'">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            <span class="text-sm text-gray-500">
                                {{ $user->last_login ? $user->last_login->diffForHumans() : 'Belum pernah login' }}
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('user.manage'))
                                    @unless($user->trashed())
                                        <a href="{{ route('admin.users.show', $user->id) }}"
                                           class="text-blue-600 hover:text-blue-900"
                                           title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    @endunless
                                @endif

                                @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('user.manage'))
                                    @unless($user->trashed())
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                           class="text-green-600 hover:text-green-900"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endunless
                                @endif

                                @if($user->id !== auth()->id() && !$user->trashed())
                                    @if(auth()->user()->can('dashboard.admin') || auth()->user()->can('user.manage'))
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Hapus"
                                                    onclick="event.preventDefault(); showDeleteConfirm(() => this.closest('form').submit());">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if($user->trashed() && (auth()->user()->can('dashboard.admin') || auth()->user()->can('user.manage')))
                                    <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-emerald-600 hover:text-emerald-900"
                                                title="Pulihkan Akun"
                                                onclick="event.preventDefault(); showConfirmAlert('Pulihkan Akun?', 'Akun ini akan dipulihkan dan dapat digunakan kembali.', () => this.closest('form').submit());">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m12 10h-7m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$users" />
                </div>
            @endif
        @endif
    </x-card>
</div>
@endsection
