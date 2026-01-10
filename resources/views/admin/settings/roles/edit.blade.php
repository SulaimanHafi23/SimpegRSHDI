@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Edit Role" 
        description="Ubah informasi role dan permissions"
        icon="fas fa-user-tag">
        <x-slot:actions>
            <x-button 
                variant="secondary" 
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.roles.index') }}'">
                Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Alert Messages --}}
    @if(session('success'))
        <x-alert type="success" dismissible>
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" dismissible>
            {{ session('error') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" dismissible>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mt-2 ml-4 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    {{-- Form --}}
    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Role Info --}}
            <div class="lg:col-span-1">
                <x-card title="Informasi Role">
                    <div class="space-y-4">
                        <x-form.input 
                            name="name" 
                            label="Nama Role" 
                            placeholder="Contoh: manager"
                            :value="old('name', $role->name)"
                            required
                            help="Nama role dalam format lowercase tanpa spasi" />

                        <x-form.input 
                            name="display_name" 
                            label="Display Name" 
                            placeholder="Contoh: Manager"
                            :value="old('display_name', $role->display_name)" />

                        <x-form.textarea 
                            name="description" 
                            label="Deskripsi" 
                            rows="4"
                            placeholder="Deskripsi role..."
                            :value="old('description', $role->description)" />

                        {{-- Role Stats --}}
                        <div class="pt-4 border-t border-gray-200">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Total Users:</span>
                                    <x-badge variant="success">{{ $role->users->count() }}</x-badge>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Total Permissions:</span>
                                    <x-badge variant="info">{{ $role->permissions->count() }}</x-badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Right Column: Permissions --}}
            <div class="lg:col-span-2">
                <x-card title="Permissions">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Pilih Permissions</h3>
                            <div class="flex gap-2">
                                <x-button 
                                    type="button" 
                                    variant="outline" 
                                    size="sm"
                                    onclick="selectAllPermissions()">
                                    Pilih Semua
                                </x-button>
                                <x-button 
                                    type="button" 
                                    variant="outline" 
                                    size="sm"
                                    onclick="deselectAllPermissions()">
                                    Hapus Semua
                                </x-button>
                            </div>
                        </div>
                    </x-slot:header>

                    @php
                        $rolePermissions = $role->permissions->pluck('id')->toArray();
                        
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

                        foreach($permissions as $permission) {
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
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($group['permissions'] as $permission)
                                                <x-form.checkbox 
                                                    name="permissions[]" 
                                                    :label="ucwords(str_replace(['-', '.'], [' ', ' › '], $permission->name))"
                                                    :value="$permission->id"
                                                    :checked="in_array($permission->id, old('permissions', $rolePermissions))"
                                                    class="permission-checkbox" />
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if($permissions->isEmpty())
                        <x-empty-state 
                            icon="fas fa-shield-alt"
                            title="Tidak ada permissions"
                            description="Belum ada permissions yang tersedia" />
                    @endif
                </x-card>
            </div>
        </div>

        {{-- Action Buttons --}}
        <x-card class="mt-6">
            <div class="flex items-center justify-between">
                <x-button 
                    type="button"
                    variant="outline" 
                    icon="fas fa-times"
                    onclick="window.location.href='{{ route('admin.roles.index') }}'">
                    Batal
                </x-button>

                <div class="flex gap-3">
                    <x-button 
                        type="button"
                        variant="secondary" 
                        icon="fas fa-redo"
                        onclick="this.closest('form').reset()">
                        Reset
                    </x-button>
                    
                    <x-button 
                        type="submit"
                        variant="primary" 
                        icon="fas fa-save">
                        Update Role
                    </x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>

@push('scripts')
<script>
    function selectAllPermissions() {
        document.querySelectorAll('.permission-checkbox input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = true;
        });
    }

    function deselectAllPermissions() {
        document.querySelectorAll('.permission-checkbox input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    // Warning before leaving page if form has changes
    let formChanged = false;
    const form = document.querySelector('form');
    
    form.addEventListener('change', function() {
        formChanged = true;
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    form.addEventListener('submit', function() {
        formChanged = false; // Allow navigation on submit
    });
</script>
@endpush
@endsection
