@extends('layouts.admin')

@section('title', 'Tambah Role')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Tambah Role Baru" 
        description="Buat role baru dengan permissions"
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
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Role Info --}}
            <div class="lg:col-span-1">
                <x-card title="Informasi Role">
                    <div class="space-y-4">
                        <x-form.input 
                            name="name" 
                            label="Nama Role" 
                            placeholder="Contoh: manager"
                            :value="old('name')"
                            required
                            help="Nama role dalam format lowercase tanpa spasi" />

                        <x-form.input 
                            name="display_name" 
                            label="Display Name" 
                            placeholder="Contoh: Manager"
                            :value="old('display_name')" />

                        <x-form.textarea 
                            name="description" 
                            label="Deskripsi" 
                            rows="4"
                            placeholder="Deskripsi role..."
                            :value="old('description')" />

                        {{-- Info Box --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                <div class="text-sm text-blue-700">
                                    <p class="font-semibold mb-1">Tips:</p>
                                    <ul class="list-disc ml-4 space-y-1">
                                        <li>Nama role harus unik</li>
                                        <li>Gunakan lowercase untuk nama</li>
                                        <li>Pilih permissions yang sesuai</li>
                                    </ul>
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
                                            :checked="in_array($permission->id, old('permissions', []))"
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
                        variant="success" 
                        icon="fas fa-save">
                        Simpan Role
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
</script>
@endpush
@endsection
