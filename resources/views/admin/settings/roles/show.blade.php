@extends('layouts.admin')

@section('title', 'Detail Role')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Detail Role" 
        description="Informasi lengkap role dan permissions"
        icon="fas fa-user-tag">
        <x-slot:actions>
            <x-button 
                variant="secondary" 
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.roles.index') }}'">
                Kembali
            </x-button>
            @can('edit-roles')
                <x-button 
                    variant="primary" 
                    icon="fas fa-edit"
                    onclick="window.location.href='{{ route('admin.roles.edit', $role->id) }}'">
                    Edit Role
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Alert Messages --}}
    @if(session('success'))
        <x-alert type="success" dismissible>
            {{ session('success') }}
        </x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Role Info & Stats --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Role Information --}}
            <x-card title="Informasi Role">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nama Role</label>
                        <p class="text-lg font-semibold text-gray-900 mt-1">{{ $role->name }}</p>
                    </div>

                    @if($role->display_name)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Display Name</label>
                            <p class="text-base text-gray-900 mt-1">{{ $role->display_name }}</p>
                        </div>
                    @endif

                    @if($role->description)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Deskripsi</label>
                            <p class="text-sm text-gray-700 mt-1">{{ $role->description }}</p>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-200">
                        <label class="text-sm font-medium text-gray-500">Dibuat</label>
                        <p class="text-sm text-gray-700 mt-1">
                            {{ $role->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                    @if($role->updated_at && $role->updated_at != $role->created_at)
                        <div class="pt-3 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Terakhir Diupdate</label>
                            <p class="text-sm text-gray-700 mt-1">
                                {{ $role->updated_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Statistics --}}
            <div class="grid grid-cols-1 gap-4">
                <x-stats-card 
                    title="Total Users" 
                    :value="$role->users->count()" 
                    icon="fas fa-users" 
                    color="green" />
                
                <x-stats-card 
                    title="Total Permissions" 
                    :value="$role->permissions->count()" 
                    icon="fas fa-shield-alt" 
                    color="blue" />
            </div>

            {{-- Quick Actions --}}
            <x-card title="Quick Actions">
                <div class="space-y-2">
                    @can('edit-roles')
                        <x-button 
                            variant="outline" 
                            icon="fas fa-edit"
                            class="w-full justify-start"
                            onclick="window.location.href='{{ route('admin.roles.edit', $role->id) }}'">
                            Edit Role
                        </x-button>
                    @endcan

                    <x-button 
                        variant="outline" 
                        icon="fas fa-users"
                        class="w-full justify-start"
                        onclick="alert('View users with this role')">
                        Lihat Users
                    </x-button>

                    @can('delete-roles')
                        @if($role->users->count() == 0)
                            <x-button 
                                variant="outline" 
                                icon="fas fa-trash"
                                class="w-full justify-start text-red-600 hover:bg-red-50"
                                onclick="if(confirm('Yakin ingin menghapus role ini?')) { document.getElementById('delete-form').submit(); }">
                                Hapus Role
                            </x-button>

                            <form id="delete-form" action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-xs text-yellow-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Role tidak dapat dihapus karena masih digunakan oleh {{ $role->users->count() }} user
                                </p>
                            </div>
                        @endif
                    @endcan
                </div>
            </x-card>
        </div>

        {{-- Right Column: Permissions & Users --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Permissions List --}}
            <x-card title="Permissions">
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Daftar Permissions</h3>
                        <x-badge variant="info" size="lg">
                            {{ $role->permissions->count() }} permissions
                        </x-badge>
                    </div>
                </x-slot:header>

                @if($role->permissions->isEmpty())
                    <x-empty-state 
                        icon="fas fa-shield-alt"
                        title="Tidak ada permissions"
                        description="Role ini belum memiliki permissions"
                        actionText="Tambah Permissions"
                        :actionUrl="route('admin.roles.edit', $role->id)" />
                @else
                    @php
                        $groupedPermissions = $role->permissions->groupBy(function($permission) {
                            return explode('-', $permission->name)[1] ?? 'other';
                        });
                    @endphp

                    <div class="space-y-4">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700 uppercase flex items-center">
                                        <i class="fas fa-folder text-gray-400 mr-2"></i>
                                        {{ ucfirst($group) }} Module
                                    </h4>
                                    <x-badge variant="secondary" size="sm">
                                        {{ $permissions->count() }} items
                                    </x-badge>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    @foreach($permissions as $permission)
                                        <div class="flex items-center space-x-2 bg-gray-50 rounded px-3 py-2">
                                            <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                            <span class="text-sm text-gray-700">
                                                {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            {{-- Users with this Role --}}
            <x-card title="Users dengan Role Ini">
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Daftar Users</h3>
                        <x-badge variant="success" size="lg">
                            {{ $role->users->count() }} users
                        </x-badge>
                    </div>
                </x-slot:header>

                @if($role->users->isEmpty())
                    <x-empty-state 
                        icon="fas fa-users"
                        title="Tidak ada users"
                        description="Belum ada user yang menggunakan role ini" />
                @else
                    <x-table>
                        <x-slot:thead>
                            <x-table.row>
                                <x-table.cell header>User</x-table.cell>
                                <x-table.cell header>Email</x-table.cell>
                                <x-table.cell header>Status</x-table.cell>
                                <x-table.cell header>Last Login</x-table.cell>
                            </x-table.row>
                        </x-slot:thead>

                        @foreach($role->users as $user)
                            <x-table.row>
                                <x-table.cell>
                                    <div class="flex items-center space-x-3">
                                        @if($user->photo)
                                            <img src="{{ asset('storage/' . $user->photo) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="w-8 h-8 rounded-full">
                                        @else
                                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->username }}</div>
                                        </div>
                                    </div>
                                </x-table.cell>

                                <x-table.cell>{{ $user->email }}</x-table.cell>

                                <x-table.cell>
                                    <x-badge :variant="$user->is_active ? 'success' : 'danger'">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </x-table.cell>

                                <x-table.cell>
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->diffForHumans() }}
                                    @else
                                        <span class="text-gray-400">Belum pernah login</span>
                                    @endif
                                </x-table.cell>
                            </x-table.row>
                        @endforeach
                    </x-table>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
