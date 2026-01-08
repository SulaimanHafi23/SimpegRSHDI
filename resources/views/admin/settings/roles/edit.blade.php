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
                        $groupedPermissions = $permissions->groupBy(function($permission) {
                            return explode('-', $permission->name)[1] ?? 'other';
                        });
                    @endphp

                    <div class="space-y-6">
                        @foreach($groupedPermissions as $group => $perms)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-700 uppercase">
                                        {{ ucfirst($group) }} Permissions
                                    </h4>
                                    <x-badge variant="secondary" size="sm">
                                        {{ $perms->count() }} items
                                    </x-badge>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($perms as $permission)
                                        <x-form.checkbox 
                                            name="permissions[]" 
                                            :label="ucwords(str_replace('-', ' ', $permission->name))"
                                            :value="$permission->id"
                                            :checked="in_array($permission->id, old('permissions', $rolePermissions))"
                                            class="permission-checkbox" />
                                    @endforeach
                                </div>
                            </div>
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
