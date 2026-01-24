@extends('layouts.admin')

@section('title', 'Data Pegawai')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Data Pegawai" 
        description="Kelola data seluruh pegawai"
        icon="fas fa-users">
        <x-slot:actions>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                <x-button 
                    variant="primary" 
                    icon="fas fa-file-excel"
                    onclick="window.location.href='{{ route('admin.workers.export') }}'">
                    Export
                </x-button>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                <x-button 
                    variant="success" 
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.workers.create') }}'">
                    Tambah Pegawai
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stats-card 
            title="Total Pegawai" 
            :value="$workers->total()" 
            icon="fas fa-users" 
            color="blue" />
        
        <x-stats-card 
            title="Aktif" 
            :value="$workers->where('status', 'active')->count()" 
            icon="fas fa-user-check" 
            color="green" />
        
        <x-stats-card 
            title="Kontrak" 
            :value="$workers->where('employment_status', 'contract')->count()" 
            icon="fas fa-user-clock" 
            color="yellow" />
        
        <x-stats-card 
            title="Non-Aktif" 
            :value="$workers->where('status', 'inactive')->count()" 
            icon="fas fa-user-times" 
            color="red" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.workers.index') }}">
        <x-form.input 
            name="search" 
            label="Pencarian" 
            placeholder="Cari nama/NIP..."
            :value="$filters['search'] ?? ''" />

        <x-form.select 
            name="department_id" 
            label="Departemen"
            :selected="$filters['department_id'] ?? ''"
            placeholder="Semua Departemen">
            @foreach($departments as $department)
                <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select 
            name="employment_status" 
            label="Status Kepegawaian"
            :options="[
                'permanent' => 'Tetap',
                'contract' => 'Kontrak',
                'probation' => 'Probation',
                'internship' => 'Magang'
            ]"
            :selected="$filters['employment_status'] ?? ''"
            placeholder="Semua Status Kepegawaian" />

        <x-form.select 
            name="status" 
            label="Status"
            :options="[
                'active' => 'Aktif',
                'inactive' => 'Non-Aktif'
            ]"
            :selected="$filters['status'] ?? ''"
            placeholder="Semua Status" />
    </x-filter-section>

    {{-- Workers Table --}}
    <x-card>
        @if($workers->isEmpty())
            <x-empty-state 
                icon="fas fa-users"
                title="Tidak ada data pegawai"
                description="Silakan tambahkan data pegawai baru"
                actionText="Tambah Pegawai"
                :actionUrl="route('admin.workers.create')" />
        @else
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Pegawai</x-table.cell>
                        <x-table.cell header>NIP</x-table.cell>
                        <x-table.cell header>Departemen</x-table.cell>
                        <x-table.cell header>Status Kepegawaian</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($workers as $index => $worker)
                    <x-table.row>
                        <x-table.cell>
                            {{ $workers->firstItem() + $index }}
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    @if($worker->photo_url)
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ Storage::url($worker->photo_url) }}" 
                                             alt="{{ $worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-gray-600 font-medium">{{ substr($worker->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $worker->email }}</div>
                                </div>
                            </div>
                        </x-table.cell>

                        <x-table.cell>{{ $worker->nip }}</x-table.cell>

                        <x-table.cell>{{ $worker->department->name ?? '-' }}</x-table.cell>

                        <x-table.cell>
                            @php
                                $employmentBadges = [
                                    'permanent' => ['variant' => 'success', 'label' => 'Tetap'],
                                    'contract' => ['variant' => 'warning', 'label' => 'Kontrak'],
                                    'probation' => ['variant' => 'primary', 'label' => 'Probation'],
                                    'internship' => ['variant' => 'secondary', 'label' => 'Magang'],
                                ];
                                $badge = $employmentBadges[$worker->employment_status] ?? ['variant' => 'secondary', 'label' => ucfirst($worker->employment_status)];
                            @endphp
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <x-badge :variant="$worker->status == 'active' ? 'success' : 'danger'">
                                {{ $worker->status == 'active' ? 'Aktif' : 'Non-Aktif' }}
                            </x-badge>
                        </x-table.cell>

                        <x-table.cell>
                            <div class="flex justify-end space-x-2">
                                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                                    <a href="{{ route('admin.workers.show', $worker->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                @endif

                                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                                    <a href="{{ route('admin.workers.edit', $worker->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900" 
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endif

                                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                                    <button onclick="if(confirm('Apakah Anda yakin ingin menghapus pegawai ini?')) { document.getElementById('delete-form-{{ $worker->id }}').submit(); }" 
                                            class="text-red-600 hover:text-red-900" 
                                            title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    <form id="delete-form-{{ $worker->id }}" 
                                          action="{{ route('admin.workers.destroy', $worker->id) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                                {{-- Account management: create or edit user account for this worker --}}
                                @if($worker->user)
                                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('user.manage'))
                    <button type="button" 
                        class="text-yellow-600 hover:text-yellow-900 open-account-btn" 
                        title="Edit Akun"
                        data-mode="edit"
                        data-user-id="{{ $worker->user->id }}"
                        data-email="{{ $worker->user->email }}"
                        data-username="{{ $worker->user->username }}"
                        data-roles="{{ $worker->user->roles->pluck('id')->join(',') }}"
                        data-worker-id="{{ $worker->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    @endif
                                @else
                                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('user.manage'))
                                            <button type="button" 
                                                    class="text-green-600 hover:text-green-900 open-account-btn" 
                                                    title="Buat Akun"
                                                    data-mode="create"
                                                    data-worker-id="{{ $worker->id }}"
                                                    data-email="{{ $worker->email ?? '' }}"
                                                    data-nip="{{ $worker->nip ?? '' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            @if($workers->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$workers" />
                </div>
            @endif
        @endif
    </x-card>
</div>

{{-- Account modal overlay (create / edit) --}}
<div id="account-modal" class="fixed inset-0 backdrop-blur-sm bg-white/30 hidden items-center justify-center z-50" onclick="if(event.target === this) document.getElementById('account-modal').classList.add('hidden')">
    <div class="bg-white/60 backdrop-blur-lg rounded-lg w-full max-w-md mx-4 shadow-lg border border-white/20" onclick="event.stopPropagation()">
        <div class="p-4 border-b border-white/20 flex items-center justify-between">
            <h3 id="account-modal-title" class="font-semibold">Kelola Akun Pengguna</h3>
            <button type="button" id="account-modal-close" class="text-gray-600 hover:text-gray-900">&times;</button>
        </div>
        <form id="account-form" method="POST" class="p-4">
            @csrf
            <div id="method-spoof"></div>
            <input type="hidden" name="worker_id" id="account-worker-id">

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="account-username" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <input type="text" id="account-roles-search" placeholder="Cari role..." class="mt-1 block w-full border rounded px-3 py-2" />
                <select name="roles[]" id="account-roles" class="mt-2 block w-full border rounded px-3 py-2" multiple size="6">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Ketik untuk mencari, lalu pilih satu atau lebih role untuk akun ini.</p>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="account-email" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="account-password" class="mt-1 block w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password ketika melakukan edit.</p>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="account-password-confirmation" class="mt-1 block w-full border rounded px-3 py-2">
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="account-cancel" class="px-3 py-2 border rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded" id="account-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('account-modal');
    const closeBtn = document.getElementById('account-modal-close');
    const cancelBtn = document.getElementById('account-cancel');
    const form = document.getElementById('account-form');
    const title = document.getElementById('account-modal-title');
    const methodSpoof = document.getElementById('method-spoof');

    function openModal(config) {
        // config: { mode: 'create'|'edit', workerId, userId?, email? }
        form.reset();
        methodSpoof.innerHTML = '';
        if (config.mode === 'create') {
            title.textContent = 'Buat Akun Pengguna';
            form.action = "{{ route('admin.users.store') }}";
            form.querySelector('#account-email').required = true;
            form.querySelector('#account-password').required = true;
            form.querySelector('#account-password-confirmation').required = true;
            document.getElementById('account-worker-id').value = config.workerId;
            document.getElementById('account-email').value = config.email || '';
            // prefill username from email local-part or NIP
            const usernameInput = document.getElementById('account-username');
            usernameInput.value = '';
            if (config.email) {
                usernameInput.value = (config.email.split('@')[0] || '').replace(/[^a-zA-Z0-9._-]/g, '');
            } else if (config.nip) {
                usernameInput.value = config.nip;
            }
            usernameInput.required = true;
        } else if (config.mode === 'edit') {
            title.textContent = 'Edit Akun Pengguna';
            // use PUT to users.update
            form.action = `/users/${config.userId}`;
            methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('account-worker-id').value = config.workerId;
            document.getElementById('account-email').value = config.email || '';
            // prefill username from existing user
            document.getElementById('account-username').value = config.username || '';
            // password optional on edit
            form.querySelector('#account-password').required = false;
            form.querySelector('#account-password-confirmation').required = false;
                // populate roles selection if provided
                const rolesSelect = document.getElementById('account-roles');
                // clear previous selection
                Array.from(rolesSelect.options).forEach(opt => opt.selected = false);
                if (config.roles) {
                    const arr = String(config.roles).split(',').filter(Boolean);
                    arr.forEach(id => {
                        const opt = rolesSelect.querySelector(`option[value="${id}"]`);
                        if (opt) opt.selected = true;
                    });
                }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Roles select search/filter
    const rolesSearch = document.getElementById('account-roles-search');
    const rolesSelect = document.getElementById('account-roles');
    if (rolesSearch && rolesSelect) {
        rolesSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            Array.from(rolesSelect.options).forEach(opt => {
                const text = opt.text.toLowerCase();
                opt.hidden = q.length > 0 ? !text.includes(q) : false;
            });
        });
    }

        document.querySelectorAll('.open-account-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const mode = this.dataset.mode;
                const workerId = this.dataset.workerId;
                const userId = this.dataset.userId;
                const email = this.dataset.email;
                const username = this.dataset.username;
                const nip = this.dataset.nip;
                const roles = this.dataset.roles;
                openModal({ mode, workerId, userId, email, username, nip, roles });
            });
        });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    // close on overlay click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
});
</script>
@endpush
@endsection
