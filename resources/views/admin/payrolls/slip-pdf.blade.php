<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->worker?->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 12px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #1e40af; }
        .header p { font-size: 11px; color: #555; margin-top: 2px; }
        .slip-title { text-align: center; font-size: 13px; font-weight: bold; margin: 12px 0; text-transform: uppercase; letter-spacing: 1px; }
        .info-grid { display: table; width: 100%; margin-bottom: 12px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 120px; padding: 3px 6px; color: #555; }
        .info-value { display: table-cell; padding: 3px 6px; font-weight: 600; }
        .info-colon { display: table-cell; padding: 3px 2px; color: #555; }
        table.comp { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.comp th { background: #1e40af; color: white; padding: 6px 10px; text-align: left; font-size: 10px; }
        table.comp td { padding: 5px 10px; border-bottom: 1px solid #e5e7eb; }
        table.comp tr:last-child td { border-bottom: none; }
        .section-title { font-weight: bold; font-size: 10px; color: #374151; padding: 6px 10px; background: #f3f4f6; border-left: 3px solid #1e40af; margin: 8px 0 0 0; }
        .amount { text-align: right; }
        .total-row td { font-weight: bold; background: #eff6ff; border-top: 2px solid #1e40af; }
        .net-box { margin-top: 12px; padding: 10px 16px; background: #1e40af; color: white; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
        .net-box .label { font-size: 12px; }
        .net-box .amount { font-size: 16px; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .badge-paid { display: inline-block; background: #d1fae5; color: #065f46; padding: 2px 10px; border-radius: 20px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RSUD H. Damanhuri Barabai</h1>
        <p>Jl. H. Damanhuri No. 1, Barabai, Hulu Sungai Tengah</p>
    </div>

    <div class="slip-title">Slip Gaji Karyawan</div>

    @php $period = $payroll->payrollPeriod; @endphp

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Nama</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $payroll->worker?->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NIP</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $payroll->worker?->nip }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Departemen</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $payroll->worker?->department?->name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periode</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ $period?->month_name ?? '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Terbit</span>
            <span class="info-colon">:</span>
            <span class="info-value">{{ optional($payroll->paid_at ?? now())->format('d/m/Y') }}</span>
        </div>
        @if($payroll->status === 'paid')
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-colon">:</span>
            <span class="info-value"><span class="badge-paid">Lunas</span></span>
        </div>
        @endif
    </div>

    {{-- Gaji Pokok --}}
    <div class="section-title">Gaji Pokok</div>
    <table class="comp">
        <tbody>
            <tr>
                <td>Gaji Pokok</td>
                <td class="amount">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tunjangan/Earning --}}
    @php $earnings = $payroll->earnings_list; @endphp
    @if(count($earnings) > 0)
        <div class="section-title">Tunjangan / Penghasilan Tambahan</div>
        <table class="comp">
            <tbody>
                @foreach($earnings as $comp)
                    <tr>
                        <td>{{ $comp['name'] ?? '-' }}</td>
                        <td class="amount">Rp {{ number_format($comp['amount'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total Tunjangan</td>
                    <td class="amount">Rp {{ number_format($payroll->total_earnings, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- Potongan/Deduction --}}
    @php $deductions = $payroll->deductions_list; @endphp
    @if(count($deductions) > 0)
        <div class="section-title">Potongan</div>
        <table class="comp">
            <tbody>
                @foreach($deductions as $comp)
                    <tr>
                        <td>{{ $comp['name'] ?? '-' }}</td>
                        <td class="amount">Rp {{ number_format($comp['amount'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total Potongan</td>
                    <td class="amount">Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- Gaji Bersih --}}
    <div class="net-box">
        <span class="label">GAJI BERSIH DITERIMA</span>
        <span class="amount">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</span>
    </div>

    <div class="footer">
        Dokumen ini diterbitkan secara otomatis oleh Sistem Informasi Kepegawaian RSUD H. Damanhuri Barabai.<br>
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
