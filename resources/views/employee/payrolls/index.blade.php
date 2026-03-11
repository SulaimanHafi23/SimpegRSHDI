@extends('layouts.employee')

@section('title', 'Slip Gaji Saya')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Slip Gaji Saya</h1>
        <p class="text-sm text-gray-600 mt-1">Riwayat slip gaji Anda</p>
    </div>

    @if($payrolls->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
            <i class="fas fa-file-invoice-dollar text-4xl text-gray-300 mb-3"></i>
            <p class="font-medium">Belum ada slip gaji</p>
            <p class="text-sm mt-1">Slip akan tersedia setelah tim HR memproses penggajian</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($payrolls as $payroll)
                @php $badge = $payroll->status_badge; @endphp
                <div class="bg-white rounded-lg shadow p-5 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $payroll->payrollPeriod?->month_name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ optional($payroll->payrollPeriod?->start_date)->format('d/m/Y') }}
                                – {{ optional($payroll->payrollPeriod?->end_date)->format('d/m/Y') }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($badge['variant'] === 'success') bg-green-100 text-green-700
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $badge['label'] }}
                        </span>
                    </div>

                    <div class="text-right mb-4">
                        <p class="text-xs text-gray-500">Gaji Bersih</p>
                        <p class="text-xl font-bold text-blue-700">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('employee.payrolls.show', $payroll->id) }}"
                           class="flex-1 py-2 text-center text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 font-medium">
                            <i class="fas fa-eye mr-1"></i>Detail
                        </a>
                        @if($payroll->status === 'paid')
                            <a href="{{ route('employee.payrolls.slip-pdf', $payroll->id) }}"
                               target="_blank"
                               class="flex-1 py-2 text-center text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100 font-medium">
                                <i class="fas fa-file-pdf mr-1"></i>PDF
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($payrolls->hasPages())
            <div>{{ $payrolls->links() }}</div>
        @endif
    @endif
</div>
@endsection
