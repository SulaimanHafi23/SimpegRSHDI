{{-- filepath: resources/views/auth/forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lupa Kata Sandi - {{ config('app.name') }}</title>
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
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(ellipse at 50% 40%, #1e7a3e44 0%, transparent 65%),
                        linear-gradient(160deg, var(--green-dark) 0%, #0d2b17 100%);
            overflow-x: hidden;
        }

        #particles-canvas {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .forgot-wrap {
            position: relative;
            z-index: 1;
        }

        .forgot-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(151, 199, 171, 0.35);
            box-shadow: 0 20px 44px rgba(26, 87, 57, 0.16);
            position: relative;
            overflow: hidden;
        }

        .forgot-card::before {
            content: '';
            position: absolute;
            inset: -30% auto auto -20%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(245, 166, 35, 0.34) 0%, rgba(245, 166, 35, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        .forgot-card > * {
            position: relative;
            z-index: 1;
        }

        .forgot-gold-glow {
            box-shadow:
                0 24px 56px rgba(26, 87, 57, 0.2),
                0 0 0 1px rgba(245, 166, 35, 0.18),
                0 0 38px rgba(245, 166, 35, 0.24);
        }

        .forgot-input {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(134, 181, 153, 0.4);
            color: #1f3d2c;
        }

        .forgot-input:focus {
            border-color: var(--green-light);
            box-shadow: 0 0 0 3px rgba(40, 160, 79, 0.2);
            outline: none;
        }

        .forgot-submit {
            background: linear-gradient(135deg, var(--gold), #d97706);
            color: #1a1a1a;
        }

        .forgot-submit:hover {
            filter: brightness(1.03);
            box-shadow: 0 12px 28px rgba(245, 166, 35, 0.35);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <canvas id="particles-canvas"></canvas>

    <div class="min-h-screen flex items-center justify-center forgot-wrap relative overflow-hidden">
        {{-- Background Decorations --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-25">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#ffd166] rounded-full mix-blend-overlay filter blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#6ee7b7] rounded-full mix-blend-overlay filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="w-full max-w-md relative z-10 px-4">
            {{-- Logo --}}
            <div class="text-center mb-6">
                <div class="inline-flex h-16 w-16 bg-linear-to-br from-[#155a2e] to-[#0a3d1f] rounded-xl items-center justify-center shadow-lg mb-4 overflow-hidden">
                    <img src="{{ asset('images/logo-rs.png') }}" alt="RSUD Logo" class="h-full w-full object-cover"
                         onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-hospital text-white text-2xl\'></i>';">
                </div>
                <h1 class="text-xl font-bold text-white">SIDIA</h1>
                <p class="text-sm text-[#d1fae5]">Sistem Informasi Darlan Ismail dan Absensi</p>
            </div>

            <div class="p-6 sm:p-8 shadow-2xl rounded-xl border-t-4 border-[#28a04f] backdrop-blur-lg forgot-card forgot-gold-glow">
                {{-- Header Icon --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center h-16 w-16 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-key text-green-600 text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Lupa Kata Sandi?</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Masukkan email Anda dan kami akan mengirimkan link untuk reset kata sandi.
                    </p>
                </div>

                {{-- Success Message --}}
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <strong class="font-semibold text-green-800">Berhasil!</strong>
                                <p class="text-sm text-green-700 mt-1">{{ session('status') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <strong class="font-semibold text-red-800">Error!</strong>
                                <p class="text-sm text-red-700 mt-1">{{ $errors->first() }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.email') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
                    @csrf

                    {{-- Email Input --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-green-600 mr-1"></i>
                            Alamat Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="contoh@email.com"
                            required
                            autofocus
                            class="w-full px-4 py-3 rounded-lg transition duration-200 forgot-input @error('email') border-red-500 @enderror"
                        >
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-3 text-base font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-[#f5a623] focus:ring-offset-2 focus:ring-offset-white transition-all duration-300 active:scale-95 disabled:opacity-50 forgot-submit"
                    >
                        <span x-show="!loading" class="flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Kirim Link Reset
                        </span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Mengirim...
                        </span>
                    </button>
                </form>

                {{-- Back to Login --}}
                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-[#2f6a4b] hover:text-[#1f5c3b] font-medium transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Login
                    </a>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-6 text-sm text-[#a7f3d0]">
                <p>&copy; {{ date('Y') }} Muhammad Sulaiman Hafi &amp; Muhammad Hafidl Badali x RSUD HDI. All rights reserved.</p>
                <div class="mt-3 flex items-center justify-center gap-4 text-xs text-[#d1fae5]">
                    <a href="{{ route('public.privacy') }}" class="hover:text-[#ffd166] transition duration-200">Privacy</a>
                    <span>•</span>
                    <a href="{{ route('public.terms') }}" class="hover:text-[#ffd166] transition duration-200">Terms</a>
                    <span>•</span>
                    <a href="{{ route('public.help') }}" class="hover:text-[#ffd166] transition duration-200">Help</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* ── Particle canvas (same as landing page) ── */
        (function() {
            const canvas = document.getElementById('particles-canvas');
            const ctx    = canvas.getContext('2d');
            let W, H, particles = [];

            function resize() {
                W = canvas.width  = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }
            window.addEventListener('resize', resize);
            resize();

            function rand(min, max) { return Math.random() * (max - min) + min; }

            function Particle() {
                this.reset();
            }
            Particle.prototype.reset = function() {
                this.x    = rand(0, W);
                this.y    = rand(0, H);
                this.r    = rand(1, 2.8);
                this.vx   = rand(-0.25, 0.25);
                this.vy   = rand(-0.4, -0.08);
                this.alpha = rand(0.08, 0.28);
                this.hue  = rand(140, 170);
            };
            Particle.prototype.update = function() {
                this.x += this.vx;
                this.y += this.vy;
                if (this.y < -10 || this.x < -10 || this.x > W + 10) this.reset();
            };
            Particle.prototype.draw = function() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                ctx.fillStyle = `hsla(${this.hue},70%,65%,${this.alpha})`;
                ctx.fill();
            };

            const COUNT = Math.min(120, Math.floor(W * H / 12000));
            for (let i = 0; i < COUNT; i++) particles.push(new Particle());

            function drawLines() {
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < 100) {
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.strokeStyle = `rgba(52,211,153,${0.06 * (1 - dist / 100)})`;
                            ctx.lineWidth = .5;
                            ctx.stroke();
                        }
                    }
                }
            }

            function loop() {
                ctx.clearRect(0, 0, W, H);
                particles.forEach(p => { p.update(); p.draw(); });
                drawLines();
                requestAnimationFrame(loop);
            }
            loop();
        })();
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
