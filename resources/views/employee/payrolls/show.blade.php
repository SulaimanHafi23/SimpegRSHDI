@extends('layouts.employee')

@section('title', 'Detail Slip Gaji')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('employee.payrolls.index') }}"
           class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Slip Gaji</h1>
            <p class="text-sm text-gray-500">{{ $payroll->payrollPeriod?->month_name }}</p>
        </div>
    </div>

    @php $badge = $payroll->status_badge; @endphp

    <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
        {{-- Header Info --}}
        <div class="p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $worker->name }} &bull; {{ $worker->nip }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    @if($badge['variant'] === 'success') bg-green-100 text-green-700
                    @else bg-gray-100 text-gray-600
                    @endif">{{ $badge['label'] }}</span>
                @if($payroll->status === 'paid')
                    <a href="{{ route('employee.payrolls.slip-pdf', $payroll->id) }}" target="_blank"
                       class="px-3 py-1 bg-red-50 text-red-700 rounded-lg text-xs hover:bg-red-100">
                        <i class="fas fa-file-pdf mr-1"></i>Download PDF
                    </a>
                @endif
            </div>
        </div>

        {{-- Gaji Pokok --}}
        <div class="p-5">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Gaji Pokok</span>
                <span class="font-semibold">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Tunjangan --}}
        @php $earnings = $payroll->earnings_list; @endphp
        @if(count($earnings) > 0)
            <div class="p-5 space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Tunjangan / Penghasilan Tambahan</p>
                @foreach($earnings as $comp)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $comp['name'] }}</span>
                        <span class="text-green-700">+Rp {{ number_format($comp['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between text-sm font-semibold border-t pt-2">
                    <span>Total Tunjangan</span>
                    <span class="text-green-700">+Rp {{ number_format($payroll->total_earnings, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif

        {{-- Potongan --}}
        @php $deductions = $payroll->deductions_list; @endphp
        @if(count($deductions) > 0)
            <div class="p-5 space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Potongan</p>
                @foreach($deductions as $comp)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $comp['name'] }}</span>
                        <span class="text-red-600">-Rp {{ number_format($comp['amount'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between text-sm font-semibold border-t pt-2">
                    <span>Total Potongan</span>
                    <span class="text-red-600">-Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif

        {{-- Gaji Bersih --}}
        <div class="p-5 bg-blue-50">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-800">Gaji Bersih</span>
                <span class="text-2xl font-bold text-blue-700">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</span>
            </div>
            @if($payroll->paid_at)
                <p class="text-xs text-gray-500 mt-1 text-right">Dibayarkan: {{ optional($payroll->paid_at)->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
