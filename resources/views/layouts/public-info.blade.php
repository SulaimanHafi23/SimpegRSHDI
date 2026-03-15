<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'SIDIA') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --green-dark: #0a3d1f;
            --green-main: #155a2e;
            --green-mid: #1e7a3e;
            --green-light: #28a04f;
            --gold: #f5a623;
            --gold-light: #ffd166;
            --cream: #f8f6ef;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(245, 166, 35, 0.22), transparent 28%),
                radial-gradient(circle at bottom right, rgba(40, 160, 79, 0.18), transparent 32%),
                linear-gradient(135deg, var(--green-dark) 0%, #0f4d27 52%, #12351d 100%);
            min-height: 100vh;
            color: #ecfdf5;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 209, 102, 0.18);
            box-shadow: 0 24px 56px rgba(0, 0, 0, 0.22), 0 0 40px rgba(245, 166, 35, 0.08);
            backdrop-filter: blur(18px);
        }

        .section-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 1.25rem;
        }

        .gold-chip {
            background: rgba(245, 166, 35, 0.14);
            border: 1px solid rgba(245, 166, 35, 0.28);
            color: var(--gold-light);
        }

        .nav-link {
            color: rgba(236, 253, 245, 0.74);
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .nav-link:hover,
        .nav-link:focus {
            color: #fff7db;
            background: rgba(245, 166, 35, 0.12);
        }

        .primary-button {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            color: #16331f;
            box-shadow: 0 16px 30px rgba(245, 166, 35, 0.22);
        }
    </style>
</head>
<body>
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-24 left-8 h-56 w-56 rounded-full bg-[rgba(245,166,35,0.18)] blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-[rgba(40,160,79,0.18)] blur-3xl"></div>
        </div>

        <div class="relative mx-auto min-h-screen max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <header class="glass-card mb-8 rounded-3xl px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[rgba(245,166,35,0.26)] bg-[rgba(255,255,255,0.08)]">
                            <i class="fa-solid fa-hospital text-xl text-[#ffd166]"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#ffd166]">SIDIA</p>
                            <h1 class="text-xl font-extrabold text-white sm:text-2xl">@yield('page_heading')</h1>
                            <p class="text-sm text-emerald-100/70">Sistem Informasi Darlan Ismail dan Absensi</p>
                        </div>
                    </div>
                    <nav class="flex flex-wrap items-center gap-2 text-sm font-medium">
                        <a href="{{ route('home') }}" class="nav-link rounded-full px-4 py-2">Beranda</a>
                        <a href="{{ route('login') }}" class="nav-link rounded-full px-4 py-2">Masuk</a>
                        <a href="{{ route('public.privacy') }}" class="nav-link rounded-full px-4 py-2">Privacy</a>
                        <a href="{{ route('public.terms') }}" class="nav-link rounded-full px-4 py-2">Terms</a>
                        <a href="{{ route('public.help') }}" class="nav-link rounded-full px-4 py-2">Help</a>
                    </nav>
                </div>
            </header>

            <main class="grid gap-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(320px,0.45fr)]">
                <section class="glass-card rounded-4xl px-6 py-7 sm:px-8 sm:py-9">
                    <div class="mb-6 flex flex-wrap items-center gap-3">
                        <span class="gold-chip rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em]">Portal Informasi SIDIA</span>
                        <span class="text-sm text-emerald-100/70">Diperbarui untuk kebutuhan operasional RSUD HDI</span>
                    </div>
                    <div class="max-w-3xl">
                        <h2 class="text-3xl font-extrabold leading-tight text-white sm:text-4xl">@yield('hero_title')</h2>
                        <p class="mt-4 text-base leading-7 text-emerald-50/78 sm:text-lg">@yield('hero_description')</p>
                    </div>
                    <div class="mt-8 space-y-4">
                        @yield('content')
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="glass-card rounded-4xl px-6 py-6">
                        <h3 class="text-lg font-bold text-white">Ringkasan SIDIA</h3>
                        <div class="mt-4 space-y-3 text-sm leading-6 text-emerald-50/80">
                            <div class="section-card px-4 py-3">
                                <p class="font-semibold text-white">Fokus Sistem</p>
                                <p>Absensi, cuti, lembur, perjalanan dinas, dokumen pegawai, persetujuan atasan, dan notifikasi internal.</p>
                            </div>
                            <div class="section-card px-4 py-3">
                                <p class="font-semibold text-white">Pengguna Utama</p>
                                <p>Super Admin, HR, Manager, dan Employee sesuai role serta hak akses masing-masing.</p>
                            </div>
                            <div class="section-card px-4 py-3">
                                <p class="font-semibold text-white">Konsistensi Desain</p>
                                <p>Halaman ini mengikuti palet hijau tua dan aksen emas yang sama dengan landing page dan halaman autentikasi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-4xl px-6 py-6">
                        <h3 class="text-lg font-bold text-white">Butuh Akses Cepat?</h3>
                        <p class="mt-3 text-sm leading-6 text-emerald-50/78">Gunakan halaman ini untuk memahami aturan penggunaan sistem, perlindungan data, dan alur bantuan ketika pengguna mengalami kendala.</p>
                        <div class="mt-5 flex flex-col gap-3">
                            <a href="{{ route('login') }}" class="primary-button rounded-2xl px-4 py-3 text-center text-sm font-bold transition hover:brightness-105">Masuk ke SIDIA</a>
                            <a href="{{ route('password.request') }}" class="rounded-2xl border border-white/15 px-4 py-3 text-center text-sm font-semibold text-emerald-50/85 transition hover:border-[#ffd166]/40 hover:text-white">Lupa Password</a>
                        </div>
                    </div>
                </aside>
            </main>

            <footer class="mt-8 border-t border-white/10 px-2 pt-6 text-sm text-emerald-100/65">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} Muhammad Sulaiman Hafi &amp; Muhammad Hafidl Badali x RSUD HDI. All rights reserved.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('public.privacy') }}" class="hover:text-[#ffd166]">Privacy</a>
                        <a href="{{ route('public.terms') }}" class="hover:text-[#ffd166]">Terms</a>
                        <a href="{{ route('public.help') }}" class="hover:text-[#ffd166]">Help</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
