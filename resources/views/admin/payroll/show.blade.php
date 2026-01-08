@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-3 text-green-600"></i>
                    Detail Slip Gaji
                </h1>
                <p class="text-gray-600 mt-2">
                    {{ $payroll->worker->name }} - {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}
                </p>
            </div>
            <div class="flex space-x-2">
                @if($payroll->status === 'draft' && auth()->user()->can('approve-payroll'))
                    <form action="{{ route('admin.payroll.approve', $payroll) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Setujui payroll ini?')"
                                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                            <i class="fas fa-check mr-2"></i>Setujui
                        </button>
                    </form>
                @endif

                @if($payroll->status === 'approved' && auth()->user()->can('approve-payroll'))
                    <button type="button" 
                            onclick="document.getElementById('markPaidModal').classList.remove('hidden')"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                        <i class="fas fa-money-bill-wave mr-2"></i>Tandai Dibayar
                    </button>
                @endif

                <a href="{{ route('admin.payroll.index') }}" 
                   class="px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Worker Info & Status -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Worker Info -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Pegawai</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">NIP</p>
                        <p class="font-semibold">{{ $payroll->worker->nip }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama</p>
                        <p class="font-semibold">{{ $payroll->worker->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Departemen</p>
                        <p class="font-semibold">{{ $payroll->worker->department->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $payroll->status_badge }}">
                            {{ $payroll->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Period Info -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Periode</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Periode</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Mulai</p>
                        <p class="font-semibold">{{ $payroll->period_start->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Selesai</p>
                        <p class="font-semibold">{{ $payroll->period_end->format('d M Y') }}</p>
                    </div>
                    @if($payroll->payment_date)
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Pembayaran</p>
                            <p class="font-semibold">{{ $payroll->payment_date->format('d M Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Kehadiran</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Hari Kerja</span>
                        <span class="font-semibold">{{ $payroll->total_days_worked }} hari</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Hadir</span>
                        <span class="font-semibold text-green-600">{{ $payroll->total_present }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Terlambat</span>
                        <span class="font-semibold text-yellow-600">{{ $payroll->total_late }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Absen</span>
                        <span class="font-semibold text-red-600">{{ $payroll->total_absent }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t">
                        <span class="text-sm text-gray-600">Lembur</span>
                        <span class="font-semibold">{{ $payroll->total_overtime_hours }} jam</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Salary Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-6">Rincian Gaji</h3>

                <!-- Basic Salary -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-wallet text-blue-600 mr-2"></i>
                        Gaji Pokok
                    </h4>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Gaji Pokok</span>
                            <span class="text-lg font-bold text-blue-600">
                                Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Earnings -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                        Pendapatan (+)
                    </h4>
                    <div class="space-y-2">
                        <!-- Overtime -->
                        @if($payroll->overtime_amount > 0)
                            <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                                <div>
                                    <span class="text-gray-700">Lembur</span>
                                    <span class="text-xs text-gray-500 ml-2">({{ $payroll->total_overtime_hours }} jam)</span>
                                </div>
                                <span class="font-semibold text-green-600">
                                    Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <!-- Other Earnings -->
                        @foreach($payroll->details as $detail)
                            @if($detail->salaryComponent->type === 'earning')
                                <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                                    <div>
                                        <span class="text-gray-700">{{ $detail->salaryComponent->name }}</span>
                                        @if($detail->description)
                                            <span class="text-xs text-gray-500 ml-2">({{ $detail->description }})</span>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-green-600">
                                        Rp {{ number_format($detail->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Deductions -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-minus-circle text-red-600 mr-2"></i>
                        Potongan (-)
                    </h4>
                    <div class="space-y-2">
                        <!-- Tax -->
                        @if($payroll->tax_amount > 0)
                            <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                                <span class="text-gray-700">PPh 21</span>
                                <span class="font-semibold text-red-600">
                                    Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <!-- Other Deductions -->
                        @foreach($payroll->details as $detail)
                            @if($detail->salaryComponent->type === 'deduction')
                                <div class="flex justify-between items-center py-2 px-4 bg-gray-50 rounded">
                                    <div>
                                        <span class="text-gray-700">{{ $detail->salaryComponent->name }}</span>
                                        @if($detail->description)
                                            <span class="text-xs text-gray-500 ml-2">({{ $detail->description }})</span>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-red-600">
                                        Rp {{ number_format($detail->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Summary -->
                <div class="border-t-2 border-gray-200 pt-4">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-700">Total Pendapatan</span>
                            <span class="text-lg font-bold text-green-600">
                                Rp {{ number_format($payroll->total_earnings, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-700">Total Potongan</span>
                            <span class="text-lg font-bold text-red-600">
                                Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                            <span class="text-xl font-bold text-gray-800">Gaji Bersih</span>
                            <span class="text-2xl font-bold text-blue-600">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($payroll->notes)
                    <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                        <p class="text-sm font-semibold text-gray-700 mb-1">Catatan:</p>
                        <p class="text-sm text-gray-600">{{ $payroll->notes }}</p>
                    </div>
                @endif

                @if($payroll->approver)
                    <div class="mt-6 p-4 bg-green-50 rounded">
                        <p class="text-sm text-gray-600">
                            Disetujui oleh <strong>{{ $payroll->approver->worker->name ?? $payroll->approver->email }}</strong>
                            pada {{ $payroll->approved_at->format('d M Y H:i') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div id="markPaidModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tandai Sebagai Dibayar</h3>
            <form action="{{ route('admin.payroll.mark-paid', $payroll) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pembayaran</label>
                    <input type="date" 
                           name="payment_date" 
                           value="{{ now()->format('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('markPaidModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Tandai Dibayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
