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
            @can('create-users')
                <x-button 
                    variant="success" 
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.users.create') }}'">
                    Tambah User
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error">
            {{ session('error') }}
        </x-alert>
    @endif

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.users.index') }}">
        <div class="md:col-span-1">
            <x-form.input 
                name="search" 
                label="Pencarian" 
                placeholder="Cari nama, email, username..."
                :value="$filters['search'] ?? ''" />
        </div>

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
                '0' => 'Nonaktif'
            ]"
            :selected="$filters['is_active'] ?? ''"
            placeholder="Semua Status" />
    </x-filter-section>

    {{-- Table Section --}}
    <x-card title="Daftar User" :no-padding="true">
        @if($users->count() > 0)
            <x-table responsive striped hover>
                <x-slot:thead>
                    <tr>
                        <x-table.cell header>User</x-table.cell>
                        <x-table.cell header>Email / Username</x-table.cell>
                        <x-table.cell header>Role</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Last Login</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </tr>
                </x-slot:thead>

                @foreach($users as $user)
                    <x-table.row>
                        <x-table.cell>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($user->worker && $user->worker->photo)
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ Storage::url($user->worker->photo) }}" 
                                             alt="{{ $user->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="text-green-600 font-semibold text-lg">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    @if($user->worker)
                                        <div class="text-sm text-gray-500">{{ $user->worker->nip }}</div>
                                    @endif
                                </div>
                            </div>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            <div class="text-sm text-gray-500">{{ $user->username }}</div>
                        </x-table.cell>

                        <x-table.cell>
                            @foreach($user->roles as $role)
                                <x-badge 
                                    variant="{{ $role->name === 'Super Admin' ? 'danger' : ($role->name === 'HR' ? 'primary' : 'info') }}"
                                    size="sm">
                                    {{ $role->name }}
                                </x-badge>
                            @endforeach
                        </x-table.cell>

                        <x-table.cell>
                            @if($user->is_active)
                                <x-badge variant="success" icon="fas fa-check-circle">Aktif</x-badge>
                            @else
                                <x-badge variant="danger" icon="fas fa-times-circle">Nonaktif</x-badge>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            @if($user->last_login_at)
                                <div class="text-sm text-gray-900">
                                    {{ $user->last_login_at->format('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $user->last_login_at->format('H:i') }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Belum pernah login</span>
                            @endif
                        </x-table.cell>

                        <x-table.cell>
                            <x-dropdown align="right" width="48">
                                <x-slot:trigger>
                                    <x-button variant="outline-secondary" size="sm" icon="fas fa-ellipsis-v" />
                                </x-slot:trigger>

                                @can('view-users')
                                    <x-dropdown.item 
                                        icon="fas fa-eye" 
                                        :href="route('admin.users.show', $user->id)">
                                        Detail
                                    </x-dropdown.item>
                                @endcan

                                @can('edit-users')
                                    <x-dropdown.item 
                                        icon="fas fa-edit" 
                                        :href="route('admin.users.edit', $user->id)">
                                        Edit
                                    </x-dropdown.item>
                                @endcan

                                @can('toggle-user-status')
                                    <x-dropdown.item 
                                        icon="fas fa-{{ $user->is_active ? 'ban' : 'check' }}" 
                                        onclick="toggleStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </x-dropdown.item>
                                @endcan

                                @can('delete-users')
                                    @if($user->id !== auth()->id())
                                        <x-dropdown.divider />
                                        <x-dropdown.item 
                                            icon="fas fa-trash" 
                                            onclick="confirmDelete({{ $user->id }})"
                                            class="text-red-600 hover:bg-red-50">
                                            Hapus
                                        </x-dropdown.item>
                                    @endif
                                @endcan
                            </x-dropdown>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-slot:cardFooter>
                <x-pagination :paginator="$users" />
            </x-slot:cardFooter>
        @else
            <x-empty-state 
                icon="fas fa-users" 
                title="Belum ada user"
                description="Belum ada data user yang tersedia"
                actionText="Tambah User"
                :actionUrl="route('admin.users.create')" />
        @endif
    </x-card>
</div>

{{-- Delete Confirmation Modal --}}
<x-modal name="delete-user" title="Konfirmasi Hapus User" size="sm">
    <p class="text-gray-600">
        Apakah Anda yakin ingin menghapus user ini? 
        Tindakan ini tidak dapat dibatalkan.
    </p>
    
    <x-slot:footer>
        <x-button variant="outline-secondary" @click="$dispatch('close-modal-delete-user')">
            Batal
        </x-button>
        <form id="delete-form" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger" icon="fas fa-trash">
                Hapus
            </x-button>
        </form>
    </x-slot:footer>
</x-modal>

{{-- Toggle Status Modal --}}
<x-modal name="toggle-status" title="Konfirmasi Ubah Status" size="sm">
    <p class="text-gray-600" id="status-message"></p>
    
    <x-slot:footer>
        <x-button variant="outline-secondary" @click="$dispatch('close-modal-toggle-status')">
            Batal
        </x-button>
        <form id="status-form" method="POST" style="display: inline;">
            @csrf
            @method('PATCH')
            <x-button type="submit" variant="warning" icon="fas fa-sync">
                Ubah Status
            </x-button>
        </form>
    </x-slot:footer>
</x-modal>

@push('scripts')
<script>
function confirmDelete(id) {
    const form = document.getElementById('delete-form');
    form.action = `/admin/users/${id}`;
    window.dispatchEvent(new CustomEvent('open-modal-delete-user'));
}

function toggleStatus(id, activate) {
    const form = document.getElementById('status-form');
    const message = document.getElementById('status-message');
    
    form.action = `/admin/users/${id}/toggle-status`;
    message.textContent = activate 
        ? 'Apakah Anda yakin ingin mengaktifkan user ini?' 
        : 'Apakah Anda yakin ingin menonaktifkan user ini?';
    
    window.dispatchEvent(new CustomEvent('open-modal-toggle-status'));
}
</script>
@endpush
@endsection
