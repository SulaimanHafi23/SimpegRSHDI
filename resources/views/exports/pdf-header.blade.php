<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page {
            margin: 100px 50px 50px 50px;
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
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
            border-bottom: 3px solid #047857;
            padding-bottom: 10px;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .logo {
            width: 60px;
            height: 60px;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            font-size: 18px;
            font-weight: bold;
            color: #047857;
            margin-bottom: 2px;
        }
        .header-text h2 {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 2px;
        }
        .header-text p {
            font-size: 9px;
            color: #666;
        }
        footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .page-number:before {
            content: "Halaman " counter(page);
        }
        main {
            margin-top: 20px;
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
        table tbody tr:hover {
            background-color: #f3f4f6;
        }
        .info-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="{{ public_path('images/logo-rs.png') }}" alt="Logo RSUD" class="logo">
            <div class="header-text">
                <h1>RUMAH SAKIT UMUM DAERAH</h1>
                <h2>HAJI DARLAN ISMAIL</h2>
                <p>Jl. Rumah Sakit No. 1, Kotabaru, Kalimantan Selatan 72113</p>
                <p>Telp: (0518) 21234 | Email: info@rsudhdi.go.id</p>
            </div>
        </div>
    </header>

    <footer>
        <p class="page-number"></p>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>
