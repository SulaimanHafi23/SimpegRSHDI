@extends('layouts.employee')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-3 text-green-600"></i>
                    Slip Gaji
                </h1>
                <p class="text-gray-600 mt-2">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <button onclick="window.print()" 
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
                <a href="{{ route('employee.payroll.index') }}" 
                   class="px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Slip Gaji -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden print:shadow-none">
        <!-- Company Header (for print) -->
        <div class="hidden print:block bg-gray-50 p-6 border-b-2 border-gray-200">
            <h2 class="text-2xl font-bold text-center text-gray-800">RUMAH SAKIT</h2>
            <p class="text-center text-gray-600 mt-2">SLIP GAJI KARYAWAN</p>
            <p class="text-center text-gray-600">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</p>
        </div>

        <!-- Employee Info -->
        <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 print:bg-white">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 mb-3">Informasi Pegawai</h3>
                    <div class="space-y-2">
                        <div class="flex">
                            <span class="text-gray-600 w-32">NIP</span>
                            <span class="font-semibold">: {{ $payroll->worker->nip }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-gray-600 w-32">Nama</span>
                            <span class="font-semibold">: {{ $payroll->worker->name }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-gray-600 w-32">Departemen</span>
                            <span class="font-semibold">: {{ $payroll->worker->department->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 mb-3">Periode & Status</h3>
                    <div class="space-y-2">
                        <div class="flex">
                            <span class="text-gray-600 w-32">Periode</span>
                            <span class="font-semibold">: {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</span>
                        </div>
                        <div class="flex">
                            <span class="text-gray-600 w-32">Status</span>
                            <span>: <span class="px-2 py-1 text-xs font-semibold rounded {{ $payroll->status_badge }} print:bg-transparent print:px-0">{{ $payroll->status_label }}</span></span>
                        </div>
                        @if($payroll->payment_date)
                            <div class="flex">
                                <span class="text-gray-600 w-32">Tgl Bayar</span>
                                <span class="font-semibold">: {{ $payroll->payment_date->format('d M Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="p-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Kehadiran</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-green-50 print:bg-white print:border print:border-green-200 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $payroll->total_present }}</p>
                </div>
                <div class="bg-yellow-50 print:bg-white print:border print:border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $payroll->total_late }}</p>
                </div>
                <div class="bg-red-50 print:bg-white print:border print:border-red-200 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Absen</p>
                    <p class="text-2xl font-bold text-red-600">{{ $payroll->total_absent }}</p>
                </div>
                <div class="bg-blue-50 print:bg-white print:border print:border-blue-200 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-600 mb-1">Lembur</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $payroll->total_overtime_hours }} jam</p>
                </div>
            </div>
        </div>

        <!-- Salary Details -->
        <div class="p-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Rincian Gaji</h3>

            <!-- Basic Salary -->
            <div class="mb-6">
                <div class="flex justify-between items-center p-4 bg-blue-50 print:bg-gray-100 rounded-lg">
                    <span class="font-semibold text-gray-700">Gaji Pokok</span>
                    <span class="text-xl font-bold text-blue-600">
                        Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Earnings -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-plus-circle text-green-600 mr-2 print:hidden"></i>
                    Pendapatan
                </h4>
                <div class="space-y-2">
                    @if($payroll->overtime_amount > 0)
                        <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                            <div>
                                <span class="text-gray-700">Lembur</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $payroll->total_overtime_hours }} jam)</span>
                            </div>
                            <span class="font-semibold text-green-600">
                                + Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    @foreach($payroll->details as $detail)
                        @if($detail->salaryComponent->type === 'earning')
                            <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                                <span class="text-gray-700">{{ $detail->salaryComponent->name }}</span>
                                <span class="font-semibold text-green-600">
                                    + Rp {{ number_format($detail->amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Deductions -->
            <div class="mb-6">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-minus-circle text-red-600 mr-2 print:hidden"></i>
                    Potongan
                </h4>
                <div class="space-y-2">
                    @if($payroll->tax_amount > 0)
                        <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                            <span class="text-gray-700">PPh 21</span>
                            <span class="font-semibold text-red-600">
                                - Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    @foreach($payroll->details as $detail)
                        @if($detail->salaryComponent->type === 'deduction')
                            <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                                <span class="text-gray-700">{{ $detail->salaryComponent->name }}</span>
                                <span class="font-semibold text-red-600">
                                    - Rp {{ number_format($detail->amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Total Summary -->
            <div class="space-y-3 pt-4 border-t-2 border-gray-300">
                <div class="flex justify-between items-center text-lg">
                    <span class="font-semibold text-gray-700">Total Pendapatan</span>
                    <span class="font-bold text-green-600">
                        Rp {{ number_format($payroll->total_earnings, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-lg">
                    <span class="font-semibold text-gray-700">Total Potongan</span>
                    <span class="font-bold text-red-600">
                        Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-green-100 print:bg-gray-100 rounded-lg mt-4">
                    <span class="text-xl font-bold text-gray-800">GAJI BERSIH</span>
                    <span class="text-3xl font-bold text-green-600">
                        Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer (for print) -->
        <div class="hidden print:block p-6 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-12 mt-12">
                <div class="text-center">
                    <p class="mb-16">Mengetahui,</p>
                    <p class="border-t border-gray-400 inline-block px-12">HRD</p>
                </div>
                <div class="text-center">
                    <p class="mb-16">Penerima,</p>
                    <p class="border-t border-gray-400 inline-block px-12">{{ $payroll->worker->name }}</p>
                </div>
            </div>
            <p class="text-center text-xs text-gray-500 mt-8">
                Dokumen ini dicetak otomatis oleh sistem pada {{ now()->format('d M Y H:i') }}
            </p>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .container, .container * {
        visibility: visible;
    }
    .container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    /* Hide non-printable elements */
    button, .print\\:hidden {
        display: none !important;
    }
}
</style>
@endpush
@endsection
