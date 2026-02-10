<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        .page {
            padding: 30px 35px 35px 35px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 5px;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
        }
        .logo-cell {
            width: 80px;
            text-align: center;
        }
        .logo {
            max-width: 65px;
            max-height: 65px;
        }
        .header-text-cell {
            text-align: center;
            padding-left: 10px;
        }
        .header-gov {
            margin: 0;
            font-size: 11px;
            font-weight: normal;
        }
        .header-dept {
            margin: 0;
            font-size: 11px;
            font-weight: normal;
        }
        .header-hospital {
            margin: 2px 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header-address {
            margin: 0;
            font-size: 9px;
        }
        .header-line {
            border-bottom: 3px double #000;
            margin-bottom: 15px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #047857;
            margin: 10px 0 5px 0;
        }
        .print-date {
            font-size: 10px;
            color: #666;
        }
        main {
            margin-top: 15px;
        }
        h3 {
            font-size: 14px;
            font-weight: bold;
            color: #047857;
            margin-bottom: 15px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th {
            background-color: #047857;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
        }
        table td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .info-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #a7f3d0;
            border-left: 4px solid #047857;
            border-radius: 6px;
            padding: 15px 18px;
            margin-bottom: 18px;
        }
        .info-box p {
            margin-bottom: 5px;
            font-size: 10px;
        }
        .info-box strong {
            color: #047857;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .summary-box {
            margin-top: 10px;
            margin-bottom: 18px;
            padding: 12px 18px;
            border-radius: 6px;
            border-left: 4px solid #047857;
        }
        .summary-box p {
            margin: 5px 0;
            font-size: 10px;
        }
        .summary-box strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if(file_exists(public_path('images/logo-rs.png')))
                            <img src="{{ public_path('images/logo-rs.png') }}" alt="Logo" class="logo">
                        @endif
                    </td>
                    <td class="header-text-cell">
                        <p class="header-gov">PEMERINTAH KABUPATEN TANAH LAUT</p>
                        <p class="header-dept">DINAS KESEHATAN</p>
                        <h2 class="header-hospital">UPTD RSUD HAJI DARLAN ISMAIL</h2>
                        <p class="header-address">Jl. Swadaya RT.003 Desa Bumi Harapan Kecamatan Bumi Makmur</p>
                        <p class="header-address">Kabupaten Tanah Laut Kode Pos 70853</p>
                        <p class="header-address">Email: Rsudhajidarlanismail@gmail.com</p>
                    </td>
                    <td style="width: 80px;"></td>
                </tr>
            </table>
            <div class="header-line"></div>
        </div>

        <main>
            @yield('content')
        </main>

        <div class="footer">
            <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
            <p>UPTD RSUD Haji Darlan Ismail - Sistem Informasi Manajemen Kepegawaian</p>
        </div>
    </div>
</body>
</html>
