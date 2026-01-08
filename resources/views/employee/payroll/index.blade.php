@extends('layouts.employee')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-money-check-alt mr-3 text-green-600"></i>
                    Slip Gaji Saya
                </h1>
                <p class="text-gray-600 mt-2">Riwayat slip gaji bulanan</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <input type="month" 
                       name="period" 
                       value="{{ request('period') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Dibayar</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Payroll Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($payrolls as $payroll)
            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-white font-bold text-lg">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}
                        </h3>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $payroll->status_badge }}">
                            {{ $payroll->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Net Salary -->
                        <div class="text-center pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 mb-1">Gaji Bersih</p>
                            <p class="text-3xl font-bold text-green-600">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Summary -->
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gaji Pokok</span>
                                <span class="font-semibold">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Pendapatan</span>
                                <span class="font-semibold text-green-600">Rp {{ number_format($payroll->total_earnings, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Potongan</span>
                                <span class="font-semibold text-red-600">Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Attendance -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="text-center p-2 bg-green-50 rounded">
                                    <p class="text-gray-600 text-xs">Hadir</p>
                                    <p class="font-bold text-green-600">{{ $payroll->total_present }}</p>
                                </div>
                                <div class="text-center p-2 bg-yellow-50 rounded">
                                    <p class="text-gray-600 text-xs">Terlambat</p>
                                    <p class="font-bold text-yellow-600">{{ $payroll->total_late }}</p>
                                </div>
                            </div>
                        </div>

                        @if($payroll->payment_date)
                            <div class="pt-2 text-sm text-center text-gray-500">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Dibayar: {{ $payroll->payment_date->format('d M Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4">
                    <a href="{{ route('employee.payroll.show', $payroll) }}" 
                       class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        Lihat Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada slip gaji</p>
                    <p class="text-gray-400 text-sm mt-2">Slip gaji akan muncul setelah diproses oleh HR</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($payrolls->hasPages())
        <div class="mt-6">
            {{ $payrolls->links() }}
        </div>
    @endif
</div>
@endsection
