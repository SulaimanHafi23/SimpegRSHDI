@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h1 class="text-xl sm:text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-money-bill-wave mr-3 text-green-600"></i>
                    Manajemen Payroll
                </h1>
                <p class="text-gray-600 mt-2">Kelola gaji karyawan</p>
            </div>
            <div class="w-full sm:w-auto">
                <a href="{{ route('admin.payroll.generate') }}" 
                   class="inline-flex items-center justify-center w-full sm:w-auto px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-150">
                    <i class="fas fa-cog mr-2"></i>
                    <span class="hidden sm:inline">Generate Payroll</span>
                    <span class="sm:hidden">Generate</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.payroll.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <input type="month" 
                       name="period" 
                       value="{{ $filters['period'] ?? '' }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                <select name="worker_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Pegawai</option>
                    @foreach($workers as $worker)
                        <option value="{{ $worker->id }}" {{ ($filters['worker_id'] ?? '') == $worker->id ? 'selected' : '' }}>
                            {{ $worker->nip }} - {{ $worker->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ ($filters['status'] ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ ($filters['status'] ?? '') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="paid" {{ ($filters['status'] ?? '') == 'paid' ? 'selected' : '' }}>Dibayar</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Payroll Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700">
                    <tr>
                        <th class="hidden md:table-cell px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Periode</th>
                        <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">NIP</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Nama Pegawai</th>
                        <th class="hidden md:table-cell px-6 py-4 text-right text-xs font-bold text-white uppercase tracking-wider">Gaji Bersih</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payrolls as $payroll)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}
                                </div>
                            </td>
                            <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payroll->worker->nip }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payroll->worker->name }}</div>
                                <div class="text-sm text-gray-500">{{ $payroll->worker->department->name ?? '-' }}</div>
                                <div class="md:hidden text-xs text-green-600 font-bold mt-1">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</div>
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-green-600">
                                    Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $payroll->status_badge }}">
                                    <span class="hidden sm:inline">{{ $payroll->status_label }}</span>
                                    <i class="sm:hidden fas {{ $payroll->status === 'draft' ? 'fa-file-alt' : ($payroll->status === 'approved' ? 'fa-check-circle' : 'fa-money-bill-wave') }}"></i>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <a href="{{ route('admin.payroll.show', $payroll) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3" 
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($payroll->status === 'draft')
                                    <a href="{{ route('admin.payroll.edit', $payroll) }}" 
                                       class="text-yellow-600 hover:text-yellow-900 mr-3" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.payroll.destroy', $payroll) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus payroll ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                    <p class="text-gray-500 text-lg">Belum ada data payroll</p>
                                    <a href="{{ route('admin.payroll.generate') }}" class="mt-4 text-blue-600 hover:text-blue-800">
                                        Generate Payroll Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payrolls->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
