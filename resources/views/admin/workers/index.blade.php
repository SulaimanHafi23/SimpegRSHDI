@extends('layouts.admin')

@section('title', 'Data Pegawai')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Pegawai</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola data seluruh pegawai</p>
        </div>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.workers.export') }}" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                <i class="fas fa-file-excel mr-2"></i>Export
            </a>
            <a href="{{ route('admin.workers.create') }}" 
               class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                <i class="fas fa-plus mr-2"></i>Tambah Pegawai
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Pegawai</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $workers->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Aktif</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $workers->where('status', 'active')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Kontrak</p>
                    <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $workers->where('employment_status', 'contract')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-user-times text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Non-Aktif</p>
                    <p class="text-2xl font-bold text-red-600">{{ $workers->where('status', 'inactive')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama/NIP..." 
                   class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            
            <select name="department_id" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Departemen</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ ($filters['department_id'] ?? '') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            
            <select name="employment_status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Status Kepegawaian</option>
                <option value="permanent" {{ ($filters['employment_status'] ?? '') == 'permanent' ? 'selected' : '' }}>Tetap</option>
                <option value="contract" {{ ($filters['employment_status'] ?? '') == 'contract' ? 'selected' : '' }}>Kontrak</option>
                <option value="probation" {{ ($filters['employment_status'] ?? '') == 'probation' ? 'selected' : '' }}>Probation</option>
                <option value="internship" {{ ($filters['employment_status'] ?? '') == 'internship' ? 'selected' : '' }}>Magang</option>
            </select>
            
            <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Status</option>
                <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
            
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
        </form>
    </div>

    <!-- Workers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Kepegawaian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($workers as $index => $worker)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $workers->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($worker->photo_url)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($worker->photo_url) }}" alt="{{ $worker->name }}">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $worker->nip }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $worker->department->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($worker->employment_status == 'permanent') bg-green-100 text-green-800
                                    @elseif($worker->employment_status == 'contract') bg-yellow-100 text-yellow-800
                                    @elseif($worker->employment_status == 'probation') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($worker->employment_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $worker->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $worker->status == 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.workers.show', $worker->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.workers.edit', $worker->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 mr-3" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.workers.destroy', $worker->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users text-5xl mb-4 text-gray-400"></i>
                                <p>Tidak ada data pegawai</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($workers->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $workers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
