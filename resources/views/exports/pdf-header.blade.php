<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page {
            margin: 14px 18px 18px 18px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #1f2937;
        }

        .page {
            width: 100%;
        }

        .header {
            border: 1px solid #d1d5db;
            border-top: 3px solid #0f766e;
            border-radius: 6px;
            padding: 8px 10px 6px 10px;
            margin-bottom: 3px;
            background: #ffffff;
        }

        .header-table {
            width: 100%;
            border: none;
            margin: 0;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 72px;
            text-align: center;
            padding-right: 6px;
        }

        .logo {
            max-width: 56px;
            max-height: 56px;
        }

        .header-text-cell {
            text-align: center;
            padding: 0 6px;
        }

        .header-gov,
        .header-dept {
            font-size: 9px;
            font-weight: 600;
            color: #4b5563;
            line-height: 1.35;
            letter-spacing: 0.2px;
        }

        .header-hospital {
            margin: 2px 0;
            font-size: 14px;
            font-weight: 800;
            color: #0f766e;
            letter-spacing: 0.3px;
        }

        .header-address {
            font-size: 8px;
            color: #6b7280;
            line-height: 1.3;
        }

        .header-line {
            height: 2px;
            background: #0f766e;
            margin-top: 5px;
        }

        main {
            margin-top: 0;
        }

        h3 {
            font-size: 12px;
            font-weight: 700;
            color: #0f766e;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 6px 5px 6px;
            margin: 0 0 5px 0;
            border-bottom: 1.5px solid #99f6e4;
        }

        .meta-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            table-layout: auto;
            margin: 0;
            box-shadow: none;
            border-radius: 0;
            overflow: visible;
        }

        .meta-table td {
            border: none;
            padding: 2px 0;
            font-size: 9px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 3px solid #0f766e;
            border-radius: 6px;
            padding: 7px 9px;
            margin-bottom: 6px;
        }

        .info-box strong {
            color: #0f766e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            table-layout: fixed;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: none;
        }

        table th {
            background: #0f766e;
            color: #ffffff;
            font-weight: 700;
            text-align: left;
            font-size: 8px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            padding: 6px 5px;
            border-right: 1px solid #0b5f59;
        }

        table th:last-child {
            border-right: none;
        }

        table td {
            padding: 5px;
            border-top: 1px solid #e5e7eb;
            border-right: 1px solid #f3f4f6;
            font-size: 9px;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        table td:last-child {
            border-right: none;
        }

        table tbody tr:nth-child(even) {
            background: #fbfbfc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            min-width: 52px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            text-align: center;
            border: 1px solid transparent;
        }

        .badge-success {
            color: #065f46;
            background: #dcfce7;
            border-color: #86efac;
        }

        .badge-warning {
            color: #92400e;
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .badge-danger {
            color: #991b1b;
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .badge-secondary {
            color: #374151;
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .summary-box {
            margin-top: 6px;
            border: 1px solid #d1d5db;
            border-left: 3px solid #0f766e;
            border-radius: 6px;
            background: #fcfcfd;
            padding: 7px 9px;
        }

        .summary-title {
            font-size: 10px;
            font-weight: 700;
            color: #0f766e;
            margin-bottom: 4px;
        }

        .summary-grid {
            width: 100%;
            border: none;
            border-collapse: collapse;
            table-layout: auto;
            margin: 0;
            box-shadow: none;
            border-radius: 0;
            overflow: visible;
        }

        .summary-grid td {
            border: none;
            padding: 2px 0;
            font-size: 9px;
        }

        .nowrap {
            white-space: nowrap;
        }

        .wrap-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wrap-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .empty-state {
            padding: 14px 6px;
            text-align: center;
            color: #6b7280;
            font-style: italic;
            font-weight: 600;
        }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            line-height: 1.35;
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
                    <td style="width: 72px;"></td>
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
