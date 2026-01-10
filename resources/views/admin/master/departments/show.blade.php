@extends('layouts.admin')

@section('title', 'Detail Departemen')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-gray-600 mb-2">
            <a href="{{ route('admin.master.departments.index') }}" class="hover:text-green-600">Departemen</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-green-600">Detail</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-building text-green-600 mr-2"></i>
                Detail Departemen
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.master.departments.index') }}"
                   class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <a href="{{ route('admin.master.departments.edit', $department->id) }}"
                   class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <form action="{{ route('admin.master.departments.destroy', $department->id) }}"
                      method="POST"
                      class="inline"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Department Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Departemen</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Kode</label>
                        <p class="text-gray-900 font-medium">{{ $department->code }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Nama Departemen</label>
                        <p class="text-gray-900 font-medium">{{ $department->name }}</p>
                    </div>

                    <div class="col-span-2">
                        <label class="text-sm text-gray-600">Deskripsi</label>
                        <p class="text-gray-900">{{ $department->description ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Status</label>
                        <div>
                            @if($department->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Jumlah Pegawai</label>
                        <p class="text-gray-900 font-medium">
                            <i class="fas fa-users text-blue-500 mr-1"></i>
                            {{ $department->workers->count() }} Pegawai
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="space-y-4">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600">Total Pegawai</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $department->workers->count() }}</p>
                    </div>
                    <i class="fas fa-users text-3xl text-blue-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-clock text-gray-600 mr-2"></i>Informasi Waktu
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Dibuat:</span>
                        <span class="text-gray-900">{{ $department->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Terakhir Update:</span>
                        <span class="text-gray-900">{{ $department->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workers List -->
    @if($department->workers->count() > 0)
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-green-600 mr-2"></i>
            Daftar Pegawai
        </h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($department->workers as $worker)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $worker->nip }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $worker->email ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $worker->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($worker->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
