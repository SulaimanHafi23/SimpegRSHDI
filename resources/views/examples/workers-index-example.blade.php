@extends('layouts.admin')

@section('title', 'Data Pegawai')

@section('content')
<div class="space-y-6">
    {{-- Page Header dengan Actions --}}
    <x-page-header 
        title="Data Pegawai" 
        description="Kelola data seluruh pegawai"
        icon="fas fa-users">
        <x-slot:actions>
            <x-button 
                variant="primary" 
                icon="fas fa-file-excel"
                onclick="window.location.href='{{ route('admin.workers.export') }}'">
                Export
            </x-button>
            <x-button 
                variant="success" 
                icon="fas fa-plus"
                onclick="window.location.href='{{ route('admin.workers.create') }}'">
                Tambah Pegawai
            </x-button>
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
            title="Cuti" 
            :value="$workers->where('status', 'on_leave')->count()" 
            icon="fas fa-user-clock" 
            color="yellow" />
        
        <x-stats-card 
            title="Non-Aktif" 
            :value="$workers->where('status', 'inactive')->count()" 
            icon="fas fa-user-times" 
            color="red" />
    </div>

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
    <x-filter-section action="{{ route('admin.workers.index') }}">
        <x-form.input 
            name="search" 
            label="Pencarian" 
            placeholder="Cari NIP, nama, email..."
            :value="request('search')" />

        <x-form.select 
            name="status" 
            label="Status"
            :options="[
                'active' => 'Aktif',
                'on_leave' => 'Cuti',
                'inactive' => 'Non-Aktif'
            ]"
            :selected="request('status')"
            placeholder="Semua Status" />

        <x-form.select 
            name="department_id" 
            label="Departemen"
            :selected="request('department_id')"
            placeholder="Semua Departemen">
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select 
            name="per_page" 
            label="Per Halaman"
            :options="[15 => '15', 25 => '25', 50 => '50', 100 => '100']"
            :selected="request('per_page', 15)" />
    </x-filter-section>

    {{-- Data Table --}}
    <x-card title="Daftar Pegawai" :no-padding="true">
        @if($workers->count() > 0)
            <x-table responsive>
                <x-slot:thead>
                    <tr>
                        <x-table.cell header>No</x-table.cell>
                        <x-table.cell header>Foto</x-table.cell>
                        <x-table.cell header>NIP</x-table.cell>
                        <x-table.cell header>Nama</x-table.cell>
                        <x-table.cell header>Departemen</x-table.cell>
                        <x-table.cell header>Status</x-table.cell>
                        <x-table.cell header class="text-center">Aksi</x-table.cell>
                    </tr>
                </x-slot:thead>

                @foreach($workers as $index => $worker)
                    <x-table.row>
                        <x-table.cell>{{ $workers->firstItem() + $index }}</x-table.cell>
                        <x-table.cell>
                            @if($worker->photo)
                                <img src="{{ asset('storage/' . $worker->photo) }}" 
                                     alt="{{ $worker->name }}" 
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                            @endif
                        </x-table.cell>
                        <x-table.cell>{{ $worker->nip }}</x-table.cell>
                        <x-table.cell>
                            <div class="font-medium">{{ $worker->name }}</div>
                            <div class="text-sm text-gray-500">{{ $worker->email }}</div>
                        </x-table.cell>
                        <x-table.cell>{{ $worker->department->name ?? '-' }}</x-table.cell>
                        <x-table.cell>
                            @if($worker->status === 'active')
                                <x-badge variant="success" icon="fas fa-check-circle">Aktif</x-badge>
                            @elseif($worker->status === 'on_leave')
                                <x-badge variant="warning" icon="fas fa-clock">Cuti</x-badge>
                            @else
                                <x-badge variant="danger" icon="fas fa-times-circle">Non-Aktif</x-badge>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="text-center">
                            <x-dropdown align="right" width="48">
                                <x-slot:trigger>
                                    <x-button variant="outline-secondary" size="sm" icon="fas fa-ellipsis-v" />
                                </x-slot:trigger>

                                <x-dropdown.item 
                                    icon="fas fa-eye" 
                                    :href="route('admin.workers.show', $worker->id)">
                                    Detail
                                </x-dropdown.item>
                                
                                @can('edit-workers')
                                    <x-dropdown.item 
                                        icon="fas fa-edit" 
                                        :href="route('admin.workers.edit', $worker->id)">
                                        Edit
                                    </x-dropdown.item>
                                @endcan
                                
                                <x-dropdown.divider />
                                
                                @can('delete-workers')
                                    <x-dropdown.item 
                                        icon="fas fa-trash" 
                                        onclick="confirmDelete({{ $worker->id }})"
                                        class="text-red-600 hover:bg-red-50">
                                        Hapus
                                    </x-dropdown.item>
                                @endcan
                            </x-dropdown>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>

            <x-slot:cardFooter>
                <x-pagination :paginator="$workers" />
            </x-slot:cardFooter>
        @else
            <x-empty-state 
                icon="fas fa-users" 
                title="Belum ada pegawai"
                description="Belum ada data pegawai yang tersedia"
                actionText="Tambah Pegawai"
                :actionUrl="route('admin.workers.create')" />
        @endif
    </x-card>
</div>

{{-- Delete Confirmation Modal --}}
<x-modal name="delete-worker" title="Konfirmasi Hapus" size="sm">
    <p class="text-gray-600">Apakah Anda yakin ingin menghapus pegawai ini? Tindakan ini tidak dapat dibatalkan.</p>
    
    <x-slot:footer>
        <x-button variant="outline-secondary" @click="$dispatch('close-modal-delete-worker')">
            Batal
        </x-button>
        <form id="delete-form" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">
                Hapus
            </x-button>
        </form>
    </x-slot:footer>
</x-modal>

@push('scripts')
<script>
function confirmDelete(id) {
    const form = document.getElementById('delete-form');
    form.action = `/admin/workers/${id}`;
    window.dispatchEvent(new CustomEvent('open-modal-delete-worker'));
}
</script>
@endpush
@endsection
