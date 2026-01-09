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
                        // Kelompokkan permissions berdasarkan kategori yang lebih terstruktur
                        $permissionGroups = [
                            'dashboard' => [
                                'title' => '🏠 Dashboard',
                                'icon' => 'fas fa-tachometer-alt',
                                'color' => 'purple',
                                'border' => 'border-purple-200',
                                'bg' => 'bg-purple-50',
                                'text' => 'text-purple-900',
                                'iconColor' => 'text-purple-600',
                                'itemBg' => 'bg-purple-50',
                                'itemBorder' => 'border-purple-100',
                                'checkColor' => 'text-purple-600',
                                'permissions' => []
                            ],
                            'master' => [
                                'title' => '📋 Master Data',
                                'icon' => 'fas fa-database',
                                'color' => 'blue',
                                'border' => 'border-blue-200',
                                'bg' => 'bg-blue-50',
                                'text' => 'text-blue-900',
                                'iconColor' => 'text-blue-600',
                                'itemBg' => 'bg-blue-50',
                                'itemBorder' => 'border-blue-100',
                                'checkColor' => 'text-blue-600',
                                'permissions' => []
                            ],
                            'management' => [
                                'title' => '👥 Manajemen',
                                'icon' => 'fas fa-users-cog',
                                'color' => 'green',
                                'border' => 'border-green-200',
                                'bg' => 'bg-green-50',
                                'text' => 'text-green-900',
                                'iconColor' => 'text-green-600',
                                'itemBg' => 'bg-green-50',
                                'itemBorder' => 'border-green-100',
                                'checkColor' => 'text-green-600',
                                'permissions' => []
                            ],
                            'approval' => [
                                'title' => '✅ Persetujuan',
                                'icon' => 'fas fa-check-double',
                                'color' => 'yellow',
                                'border' => 'border-yellow-200',
                                'bg' => 'bg-yellow-50',
                                'text' => 'text-yellow-900',
                                'iconColor' => 'text-yellow-600',
                                'itemBg' => 'bg-yellow-50',
                                'itemBorder' => 'border-yellow-100',
                                'checkColor' => 'text-yellow-600',
                                'permissions' => []
                            ],
                            'employee' => [
                                'title' => '👤 Akses Pegawai',
                                'icon' => 'fas fa-user',
                                'color' => 'indigo',
                                'border' => 'border-indigo-200',
                                'bg' => 'bg-indigo-50',
                                'text' => 'text-indigo-900',
                                'iconColor' => 'text-indigo-600',
                                'itemBg' => 'bg-indigo-50',
                                'itemBorder' => 'border-indigo-100',
                                'checkColor' => 'text-indigo-600',
                                'permissions' => []
                            ],
                            'report' => [
                                'title' => '📊 Laporan',
                                'icon' => 'fas fa-chart-bar',
                                'color' => 'pink',
                                'border' => 'border-pink-200',
                                'bg' => 'bg-pink-50',
                                'text' => 'text-pink-900',
                                'iconColor' => 'text-pink-600',
                                'itemBg' => 'bg-pink-50',
                                'itemBorder' => 'border-pink-100',
                                'checkColor' => 'text-pink-600',
                                'permissions' => []
                            ],
                            'settings' => [
                                'title' => '⚙️ Pengaturan',
                                'icon' => 'fas fa-cog',
                                'color' => 'gray',
                                'border' => 'border-gray-200',
                                'bg' => 'bg-gray-50',
                                'text' => 'text-gray-900',
                                'iconColor' => 'text-gray-600',
                                'itemBg' => 'bg-gray-50',
                                'itemBorder' => 'border-gray-100',
                                'checkColor' => 'text-gray-600',
                                'permissions' => []
                            ]
                        ];

                        // Master Data permissions
                        $masterDataModules = ['religion', 'gender', 'department', 'location', 'shift', 'leave-type', 'document-type', 'department-document-type', 'holiday'];
                        
                        // Management permissions
                        $managementModules = ['worker', 'attendance', 'schedule', 'worker-document'];
                        
                        // Approval permissions (yang punya .approve)
                        $approvalActions = ['.approve'];
                        
                        // Employee-specific permissions (yang punya .request, .view, .checkin)
                        $employeeActions = ['.request', '.view', '.checkin'];
                        
                        // Settings permissions
                        $settingsModules = ['role', 'user'];

                        foreach($role->permissions as $permission) {
                            $permName = $permission->name;
                            
                            // Dashboard
                            if (str_contains($permName, 'dashboard')) {
                                $permissionGroups['dashboard']['permissions'][] = $permission;
                            }
                            // Master Data - semua yang manage dan ada di masterDataModules
                            elseif (collect($masterDataModules)->contains(fn($mod) => str_contains($permName, $mod))) {
                                $permissionGroups['master']['permissions'][] = $permission;
                            }
                            // Management - worker, attendance, schedule, worker-document yang manage
                            elseif (collect($managementModules)->contains(fn($mod) => str_contains($permName, $mod)) && 
                                    str_contains($permName, '.manage')) {
                                $permissionGroups['management']['permissions'][] = $permission;
                            }
                            // Approval - semua yang ada .approve
                            elseif (str_contains($permName, '.approve')) {
                                $permissionGroups['approval']['permissions'][] = $permission;
                            }
                            // Report
                            elseif (str_contains($permName, 'report')) {
                                $permissionGroups['report']['permissions'][] = $permission;
                            }
                            // Employee Access - .request, .view (except report.view), .checkin
                            elseif ((str_contains($permName, '.request') || 
                                    (str_contains($permName, '.view') && !str_contains($permName, 'report')) || 
                                    str_contains($permName, '.checkin'))) {
                                $permissionGroups['employee']['permissions'][] = $permission;
                            }
                            // Settings - role, user
                            elseif (collect($settingsModules)->contains(fn($mod) => str_contains($permName, $mod))) {
                                $permissionGroups['settings']['permissions'][] = $permission;
                            }
                        }
                    @endphp

                    <div class="space-y-4">
                        @foreach($permissionGroups as $groupKey => $group)
                            @if(count($group['permissions']) > 0)
                                <div class="border-2 {{ $group['border'] }} rounded-lg overflow-hidden">
                                    {{-- Group Header --}}
                                    <div class="{{ $group['bg'] }} px-4 py-3 border-b {{ $group['border'] }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <i class="{{ $group['icon'] }} {{ $group['iconColor'] }}"></i>
                                                <h4 class="text-sm font-bold {{ $group['text'] }}">
                                                    {{ $group['title'] }}
                                                </h4>
                                            </div>
                                            <x-badge variant="{{ $group['color'] }}" size="sm">
                                                {{ count($group['permissions']) }} permissions
                                            </x-badge>
                                        </div>
                                    </div>
                                    
                                    {{-- Group Permissions --}}
                                    <div class="bg-white px-4 py-3">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($group['permissions'] as $permission)
                                                <div class="flex items-center space-x-2 {{ $group['itemBg'] }} rounded-lg px-3 py-2 border {{ $group['itemBorder'] }}">
                                                    <i class="fas fa-check-circle {{ $group['checkColor'] }} text-sm"></i>
                                                    <span class="text-sm text-gray-700 font-medium">
                                                        {{ ucwords(str_replace(['-', '.'], [' ', ' › '], $permission->name)) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
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
