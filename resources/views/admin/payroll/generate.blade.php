@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-cog mr-3 text-green-600"></i>
                    Generate Payroll
                </h1>
                <p class="text-gray-600 mt-2">Buat slip gaji untuk periode tertentu</p>
            </div>
            <a href="{{ route('admin.payroll.index') }}" 
               class="inline-flex items-center px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-lg transition-all">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.payroll.generate.process') }}" id="payrollForm">
        @csrf

        <!-- Period Selection -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Pilih Periode</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periode <span class="text-red-500">*</span></label>
                    <input type="month" 
                           name="period" 
                           value="{{ old('period', now()->format('Y-m')) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('period')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Worker Selection -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Pilih Pegawai</h3>
                <div class="space-x-2">
                    <button type="button" onclick="selectAll()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                        <i class="fas fa-check-double mr-1"></i> Pilih Semua
                    </button>
                    <button type="button" onclick="deselectAll()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg">
                        <i class="fas fa-times mr-1"></i> Batal Pilih
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAllCheckboxes(this)">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji Pokok (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($workers as $worker)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           name="worker_ids[]" 
                                           value="{{ $worker->id }}"
                                           class="worker-checkbox">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $worker->nip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $worker->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $worker->department->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <input type="number" 
                                           name="basic_salaries[{{ $worker->id }}]" 
                                           value="5000000"
                                           min="0"
                                           step="100000"
                                           class="w-40 px-3 py-1 text-right border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @error('worker_ids')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.payroll.index') }}" 
                   class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    Generate Payroll
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleAllCheckboxes(checkbox) {
    const checkboxes = document.querySelectorAll('.worker-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.worker-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.worker-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
}
</script>
@endpush
@endsection
