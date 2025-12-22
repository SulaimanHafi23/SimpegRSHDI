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
            @can('view-workers')
                <x-button 
                    variant="primary" 
                    icon="fas fa-file-excel"
                    onclick="window.location.href='{{ route('admin.workers.export') }}'">
                    Export
                </x-button>
            @endcan
            @can('create-workers')
                <x-button 
                    variant="success" 
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.workers.create') }}'">
                    Tambah Pegawai
                </x-button>
            @endcan
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
                                @can('view-workers')
                                    <a href="{{ route('admin.workers.show', $worker->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                @endcan

                                @can('edit-workers')
                                    <a href="{{ route('admin.workers.edit', $worker->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900" 
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endcan

                                @can('delete-workers')
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
@endsection
