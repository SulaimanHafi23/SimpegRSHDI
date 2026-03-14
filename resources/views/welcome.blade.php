{{-- filepath: resources/views/welcome.blade.php --}}
{{-- SIDIA Landing Page - Animated --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SIDIA - Sistem Informasi Darlan Ismail &amp; Absensi</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-rs.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-rs.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --green-dark:  #0a3d1f;
            --green-main:  #155a2e;
            --green-mid:   #1e7a3e;
            --green-light: #28a04f;
            --gold:        #f5a623;
            --gold-light:  #ffd166;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--green-dark);
            color: #fff;
            overflow-x: hidden;
        }

        /* ─── PARTICLES ─────────────────────────────── */
        #particles-canvas {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        /* ─── NAVBAR ─────────────────────────────────── */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background .4s, box-shadow .4s;
        }
        .navbar.scrolled {
            background: rgba(10,61,31,.92);
            backdrop-filter: blur(16px);
            box-shadow: 0 2px 24px rgba(0,0,0,.4);
        }
        .nav-logo { display: flex; align-items: center; gap: 12px; }
        .nav-logo img {
            height: 44px;
            width: 44px;
            border-radius: 10px;
            object-fit: cover;
            animation: logoFloat 4s ease-in-out infinite;
            transform-origin: center;
        }
        .nav-logo-text h1 { font-size: 1.25rem; font-weight: 800; line-height: 1.1; }
        .nav-logo-text p  { font-size: .7rem; color: #a7f3d0; }
        .nav-btn {
            background: linear-gradient(135deg, var(--gold), #e67e00);
            color: #1a1a1a;
            font-weight: 700;
            padding: 10px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-size: .9rem;
            box-shadow: 0 4px 20px rgba(245,166,35,.4);
            transition: transform .2s, box-shadow .2s;
            display: flex; align-items: center; gap: 8px;
            position: relative;
            overflow: hidden;
            animation: pulseGold 3.2s ease-in-out infinite;
        }
        .nav-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -150%;
            width: 55%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,.45), transparent);
            transform: skewX(-20deg);
            animation: ctaShine 4.5s ease-in-out infinite;
        }
        .nav-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(245,166,35,.55); }

        /* ─── HERO ───────────────────────────────────── */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            text-align: left;
            padding: 120px 24px 80px;
            position: relative;
            background: radial-gradient(ellipse at 50% 40%, #1e7a3e44 0%, transparent 65%),
                        linear-gradient(160deg, var(--green-dark) 0%, #0d2b17 100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 50px;
            padding: 6px 18px;
            font-size: .8rem;
            font-weight: 600;
            color: #a7f3d0;
            margin-bottom: 28px;
            animation: fadeDown .8s ease both, badgeFloat 3.6s ease-in-out 1s infinite;
        }
        .ping-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #34d399;
            position: relative;
        }
        .ping-dot::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: #34d399;
            opacity: .4;
            animation: ping 1.5s infinite;
        }

        .hero-title {
            font-size: clamp(2.8rem, 8vw, 6.5rem);
            font-weight: 900;
            line-height: 1.0;
            letter-spacing: -.03em;
            animation: fadeUp .9s .1s ease both;
        }
        .hero-title .brand {
            background: linear-gradient(135deg, #6ee7b7, #34d399, var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 32px rgba(52,211,153,.35));
            background-size: 220% auto;
            animation: brandShimmer 7s linear infinite;
        }

        .hero-sub {
            margin-top: 20px;
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            font-weight: 400;
            color: #a7f3d0;
            max-width: 640px;
            line-height: 1.6;
            animation: fadeUp .9s .25s ease both;
        }
        .hero-sub strong { color: var(--gold-light); font-weight: 700; }

        .hero-portal {
            margin-top: 10px;
            font-size: .95rem;
            color: rgba(255,255,255,.55);
            animation: fadeUp .9s .35s ease both;
        }

        .hero-cta {
            margin-top: 44px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: flex-start;
            animation: fadeUp .9s .45s ease both;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #d97706);
            color: #1a1a1a;
            font-weight: 700;
            padding: 14px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1rem;
            box-shadow: 0 6px 30px rgba(245,166,35,.45);
            transition: all .25s;
            display: flex; align-items: center; gap: 10px;
            position: relative;
            overflow: hidden;
            animation: buttonFloat 3.4s ease-in-out 1.2s infinite;
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -140%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,.45), transparent);
            transform: skewX(-22deg);
            animation: ctaShine 4.2s ease-in-out 1.2s infinite;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 12px 40px rgba(245,166,35,.55); }
        .btn-primary i { animation: rocketBounce 1.8s ease-in-out infinite; }
        .btn-outline {
            color: #fff;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1rem;
            border: 2px solid rgba(255,255,255,.25);
            backdrop-filter: blur(8px);
            background: rgba(255,255,255,.05);
            transition: all .25s;
            display: flex; align-items: center; gap: 10px;
            animation: buttonFloatAlt 3.6s ease-in-out 1.4s infinite;
        }
        .btn-outline:hover { border-color: #34d399; background: rgba(52,211,153,.08); transform: translateY(-3px); }

        /* ─── STATS BAR ──────────────────────────────── */
        .stats-bar {
            margin-top: 70px;
            display: flex;
            gap: 48px;
            justify-content: flex-start;
            flex-wrap: wrap;
            animation: fadeUp .9s .6s ease both;
        }
        .stat-item {
            text-align: center;
            animation: statWave 3.5s ease-in-out infinite;
        }
        .stat-item:nth-child(1) { animation-delay: .1s; }
        .stat-item:nth-child(3) { animation-delay: .35s; }
        .stat-item:nth-child(5) { animation-delay: .6s; }
        .stat-item:nth-child(7) { animation-delay: .85s; }
        .stat-num { font-size: 2rem; font-weight: 800; color: var(--gold-light); }
        .stat-label { font-size: .78rem; color: rgba(255,255,255,.55); margin-top: 2px; }

        .stats-divider { width: 1px; background: rgba(255,255,255,.15); }

        /* ─── SCROLL INDICATOR ───────────────────────── */
        .scroll-indicator {
            position: absolute;
            bottom: 36px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,.4);
            font-size: .7rem;
            animation: fadeIn 1.2s 1.2s ease both;
            cursor: pointer;
        }
        .scroll-indicator:hover { color: rgba(255,255,255,.7); }
        .scroll-indicator .mouse {
            width: 24px; height: 38px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 12px;
            display: flex;
            justify-content: center;
            padding-top: 6px;
        }
        .scroll-indicator .wheel {
            width: 4px; height: 8px;
            background: #34d399;
            border-radius: 2px;
            animation: scrollWheel 1.8s infinite;
        }

        /* ─── SECTION BASE ───────────────────────────── */
        section { position: relative; z-index: 1; }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(52,211,153,.12);
            border: 1px solid rgba(52,211,153,.25);
            color: #6ee7b7;
            padding: 5px 16px;
            border-radius: 50px;
            font-size: .78rem;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            line-height: 1.15;
        }
        .section-desc {
            margin-top: 12px;
            font-size: 1rem;
            color: rgba(255,255,255,.6);
            line-height: 1.7;
            max-width: 540px;
        }

        /* ─── FEATURES GRID ──────────────────────────── */
        .features-section {
            padding: 100px 24px;
            background: linear-gradient(180deg, transparent 0%, rgba(10,40,20,.8) 100%);
        }
        .features-inner { max-width: 1200px; margin: 0 auto; }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 60px;
        }
        .feature-card {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 20px;
            padding: 32px;
            transition: transform .3s, border-color .3s, box-shadow .3s;
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--green-light), #6ee7b7);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .35s;
        }
        .feature-card:hover { transform: translateY(-6px); border-color: rgba(52,211,153,.3); box-shadow: 0 16px 48px rgba(0,0,0,.35); }
        .feature-card:hover::before { transform: scaleX(1); }
        .feature-card.gold::before { background: linear-gradient(90deg, var(--gold), var(--gold-light)); }
        .feature-card.gold:hover { border-color: rgba(245,166,35,.3); }

        .feature-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 20px;
            background: rgba(52,211,153,.12);
            color: #34d399;
        }
        .feature-card.gold .feature-icon { background: rgba(245,166,35,.12); color: var(--gold); }

        .feature-card h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
        .feature-card p  { font-size: .9rem; color: rgba(255,255,255,.56); line-height: 1.65; }

        /* ─── BENTO SHOWCASE ─────────────────────────── */
        .bento-section {
            padding: 100px 24px;
        }
        .bento-inner { max-width: 1200px; margin: 0 auto; }
        .bento-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 20px;
            margin-top: 60px;
        }
        @media (max-width: 768px) { .bento-grid { grid-template-columns: 1fr; } }

        .bento-card {
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            transition: transform .35s, box-shadow .35s;
        }
        .bento-card:hover { transform: translateY(-6px); box-shadow: 0 24px 60px rgba(0,0,0,.4); }

        .bento-card-jadwal {
            background: linear-gradient(135deg, #0f4d25 0%, #0a3d1f 100%);
            border: 1px solid rgba(52,211,153,.18);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 380px;
        }
        .bento-card-absensi {
            background: linear-gradient(135deg, #1a3a5c 0%, #0d2540 100%);
            border: 1px solid rgba(96,165,250,.18);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 380px;
        }
        .bento-card-akses {
            background: linear-gradient(135deg, #2d1b4e 0%, #1a0f35 100%);
            border: 1px solid rgba(167,139,250,.18);
            padding: 40px;
        }
        .bento-card-sosial {
            background: linear-gradient(135deg, #3d1a0a 0%, #2a1005 100%);
            border: 1px solid rgba(251,146,60,.18);
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .bento-tag {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 14px; border-radius: 50px;
            font-size: .75rem; font-weight: 600;
            margin-bottom: 16px;
        }
        .bento-tag.green { background: rgba(52,211,153,.15); color: #6ee7b7; border: 1px solid rgba(52,211,153,.2); }
        .bento-tag.blue  { background: rgba(96,165,250,.15); color: #93c5fd; border: 1px solid rgba(96,165,250,.2); }
        .bento-tag.purple { background: rgba(167,139,250,.15); color: #c4b5fd; border: 1px solid rgba(167,139,250,.2); }
        .bento-tag.orange { background: rgba(251,146,60,.15); color: #fed7aa; border: 1px solid rgba(251,146,60,.2); }

        .bento-card h3 { font-size: 1.3rem; font-weight: 800; margin-bottom: 10px; }
        .bento-card p  { font-size: .88rem; color: rgba(255,255,255,.56); line-height: 1.65; }

        /* Bullet list inside bento */
        .bento-bullets {
            list-style: none;
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .bento-bullets li {
            display: flex; align-items: center; gap: 10px;
            font-size: .88rem; color: rgba(255,255,255,.75);
        }
        .bento-bullets li::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #34d399;
            flex-shrink: 0;
        }
        .bento-card-absensi .bento-bullets li::before  { background: #60a5fa; }
        .bento-card-akses   .bento-bullets li::before  { background: #a78bfa; }
        .bento-card-sosial  .bento-bullets li::before  { background: #fb923c; }

        /* Mock Dashboard inside jadwal card */
        .mock-dashboard {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px;
            padding: 16px;
            margin-top: 20px;
        }
        .mock-row {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: 8px;
            font-size: .78rem;
            cursor: default;
            transition: background .2s;
        }
        .mock-row:hover { background: rgba(52,211,153,.08); }
        .mock-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .65rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .mock-shift-badge {
            margin-left: auto;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: .7rem;
            font-weight: 600;
        }

        /* Check-in card */
        .checkin-card {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(96,165,250,.2);
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
        }
        .checkin-card .emp-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .checkin-card .emp-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .9rem; color: #fff; flex-shrink: 0;
        }
        .checkin-card .emp-name { font-size: .88rem; font-weight: 700; }
        .checkin-card .emp-role { font-size: .72rem; color: rgba(255,255,255,.5); }
        .checkin-card .time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .checkin-card .time-box { background: rgba(255,255,255,.05); border-radius: 10px; padding: 10px 12px; }
        .checkin-card .time-box-label { font-size: .65rem; color: rgba(255,255,255,.45); }
        .checkin-card .time-box-val { font-size: .9rem; font-weight: 700; color: #60a5fa; }

        /* Login mockup */
        .login-mock {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(167,139,250,.2);
            border-radius: 16px;
            padding: 24px;
            margin-top: 20px;
        }
        .login-mock label { font-size: .72rem; color: rgba(255,255,255,.5); display: block; margin-bottom: 4px; margin-top: 12px; }
        .login-mock .field {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: .82rem;
            color: rgba(255,255,255,.6);
        }
        .login-mock .mock-btn {
            margin-top: 18px;
            width: 100%;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            border: none; border-radius: 10px;
            padding: 11px;
            font-size: .88rem; font-weight: 700; color: #fff;
            cursor: default;
            text-align: center;
        }

        /* Photo card */
        .photo-card {
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            height: 220px;
        }
        .photo-card img { width: 100%; height: 100%; object-fit: cover; }
        .photo-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, transparent 40%, rgba(10,30,15,.85));
            display: flex; flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }

        /* ─── HOW IT WORKS ───────────────────────────── */
        .how-section {
            padding: 100px 24px;
            background: linear-gradient(180deg, rgba(10,40,20,.7) 0%, transparent 100%);
        }
        .how-inner { max-width: 900px; margin: 0 auto; }
        .how-steps {
            margin-top: 60px;
            display: flex;
            flex-direction: column;
            gap: 0;
            position: relative;
        }
        .how-steps::before {
            content: '';
            position: absolute;
            left: 28px; top: 0; bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #34d399, #6ee7b7, rgba(52,211,153,.1));
        }
        .how-step {
            display: flex;
            gap: 28px;
            padding: 32px 0;
            position: relative;
        }
        .step-num {
            width: 58px; height: 58px;
            border-radius: 50%;
            background: var(--green-main);
            border: 3px solid #34d399;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.1rem; color: #6ee7b7;
            flex-shrink: 0;
            box-shadow: 0 0 20px rgba(52,211,153,.3);
            position: relative; z-index: 2;
        }
        .step-content { padding-top: 10px; }
        .step-content h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; }
        .step-content p  { font-size: .88rem; color: rgba(255,255,255,.56); line-height: 1.65; max-width: 560px; }

        /* ─── CTA SECTION ────────────────────────────── */
        .cta-section {
            padding: 100px 24px;
            text-align: center;
        }
        .cta-inner { max-width: 700px; margin: 0 auto; }
        .cta-glow {
            background: radial-gradient(ellipse at 50% 50%, rgba(52,211,153,.18) 0%, transparent 70%);
            padding: 80px 40px;
            border-radius: 32px;
            border: 1px solid rgba(52,211,153,.12);
            position: relative;
        }
        .cta-glow h2 { font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; margin-bottom: 16px; }
        .cta-glow p  { color: rgba(255,255,255,.6); font-size: 1.05rem; margin-bottom: 36px; line-height: 1.7; }

        /* ─── FOOTER ─────────────────────────────────── */
        footer {
            background: #050f08;
            border-top: 1px solid rgba(255,255,255,.07);
            padding: 36px 40px;
        }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .footer-brand { display: flex; align-items: center; gap: 10px; }
        .footer-brand img { height: 34px; width: 34px; border-radius: 8px; object-fit: cover; }
        .footer-brand-text h4 { font-size: .9rem; font-weight: 700; }
        .footer-brand-text p  { font-size: .72rem; color: rgba(255,255,255,.4); }
        .footer-copy { font-size: .8rem; color: rgba(255,255,255,.35); text-align: center; }
        .footer-links { display: flex; gap: 24px; }
        .footer-links a { font-size: .82rem; color: rgba(255,255,255,.45); text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: #34d399; }

        /* ─── ANIMATIONS ─────────────────────────────── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes ping {
            75%, 100% { transform: scale(2.2); opacity: 0; }
        }
        @keyframes scrollWheel {
            0%   { transform: translateY(0); opacity: 1; }
            60%  { transform: translateY(10px); opacity: 0; }
            61%  { transform: translateY(0); opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-3px) rotate(-3deg); }
        }
        @keyframes pulseGold {
            0%, 100% { box-shadow: 0 4px 20px rgba(245,166,35,.4); }
            50% { box-shadow: 0 8px 28px rgba(245,166,35,.58); }
        }
        @keyframes ctaShine {
            0%, 25% { left: -150%; }
            55% { left: 140%; }
            100% { left: 140%; }
        }
        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        @keyframes brandShimmer {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }
        @keyframes buttonFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        @keyframes buttonFloatAlt {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        @keyframes rocketBounce {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-2px) rotate(-8deg); }
        }
        @keyframes statWave {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-14px); }
        }
        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(14px); }
        }
        @keyframes shimmer {
            from { background-position: -200% center; }
            to   { background-position:  200% center; }
        }

        .animate-float   { animation: float 4s ease-in-out infinite; }
        .animate-float-r { animation: floatReverse 4.5s ease-in-out infinite; }

        .reveal {
            opacity: 0; transform: translateY(32px);
            transition: opacity .7s ease, transform .7s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ─── BLUR BLOBS ─────────────────────────────── */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        /* ─── RESPONSIVE ─────────────────────────────── */
        @media (max-width: 640px) {
            .navbar { padding: 12px 16px; }
            .nav-btn span { display: none; }
            .stats-divider { display: none; }
            footer { padding: 24px 16px; }
            .footer-copy { order: 3; width: 100%; }
            .btn-primary,
            .btn-outline,
            .stat-item,
            .hero-badge,
            .nav-btn {
                animation-duration: 2.6s;
            }
            .hero {
                align-items: center;
                text-align: center;
            }
            .hero-cta,
            .stats-bar {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     PARTICLE CANVAS
═══════════════════════════════════════════════════════════ -->
<canvas id="particles-canvas"></canvas>

<!-- ═══════════════════════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
    <div class="nav-logo">
        <img src="{{ asset('images/logo-rs.png') }}" alt="Logo RSUD Haji Darlan Ismail">
        <div class="nav-logo-text">
            <h1>SIDIA</h1>
            <p>RSUD Haji Darlan Ismail</p>
        </div>
    </div>
    <a href="{{ route('login') }}" class="nav-btn">
        <i class="fas fa-sign-in-alt"></i>
        <span>Masuk</span>
    </a>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ -->
<section class="hero">
    <!-- Blobs -->
    <div class="blob" style="width:500px;height:500px;background:rgba(52,211,153,.07);top:-180px;right:-120px;animation:float 8s ease-in-out infinite;"></div>
    <div class="blob" style="width:400px;height:400px;background:rgba(245,166,35,.06);bottom:-100px;left:-100px;animation:floatReverse 9s ease-in-out infinite;"></div>

    <div class="hero-content">
        <div class="hero-badge">
            <div class="ping-dot"></div>
            Portal Utama RSUD Haji Darlan Ismail
        </div>

        <h1 class="hero-title">
            Sistem Informasi<br>
            <span class="brand">Darlan Ismail</span><br>
            <span style="font-size:.65em;color:rgba(255,255,255,.7);font-weight:700;">&amp; Absensi</span>
        </h1>

        <p class="hero-sub">
            Platform digital terpadu untuk manajemen pegawai, absensi real-time, dan pengelolaan SDM di
            <strong>RSUD Haji Darlan Ismail</strong> yang modern & efisien.
        </p>

        <div class="hero-cta">
            <a href="{{ route('login') }}" class="btn-primary">
                <i class="fas fa-rocket"></i>
                Mulai Sekarang
            </a>
            <a href="#features" class="btn-outline">
                <i class="fas fa-play-circle"></i>
                Pelajari Lebih
            </a>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-num" id="stat-staff">100+</div>
                <div class="stat-label">Pegawai Aktif</div>
            </div>
            <div class="stats-divider"></div>
            <div class="stat-item">
                <div class="stat-num">99.9%</div>
                <div class="stat-label">Uptime</div>
            </div>
            <div class="stats-divider"></div>
            <div class="stat-item">
                <div class="stat-num">24/7</div>
                <div class="stat-label">Dukungan</div>
            </div>
            <!-- <div class="stats-divider"></div>
            <div class="stat-item">
                <div class="stat-num">v2.0</div>
                <div class="stat-label">Versi Terbaru</div>
            </div> -->
        </div>
    </div>

    <a href="#features" class="scroll-indicator">
        <div class="mouse"><div class="wheel"></div></div>
        <span>scroll</span>
    </a>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FEATURES GRID
═══════════════════════════════════════════════════════════ -->
<section id="features" class="features-section">
    <div class="features-inner">
        <div class="reveal" style="text-align:center;">
            <div class="section-label">
                <i class="fas fa-star"></i> Fitur Unggulan
            </div>
            <h2 class="section-title">Semua Yang Anda Butuhkan<br>Dalam Satu Platform</h2>
            <p class="section-desc" style="margin:0 auto;">Kelola seluruh kegiatan SDM rumah sakit dengan mudah, cepat, dan akurat melalui SIDIA.</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-clock"></i></div>
                <h3>Absensi Real-Time</h3>
                <p>Pencatatan kehadiran digital dengan geofencing, validasi lokasi, dan upload foto check-in yang terintegrasi penuh.</p>
            </div>
            <div class="feature-card gold reveal">
                <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                <h3>Manajemen Jadwal</h3>
                <p>Generate rotasi shift otomatis, pemfilteran per departemen, dan detail jadwal pegawai real-time.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-umbrella-beach"></i></div>
                <h3>Pengajuan Cuti</h3>
                <p>Proses pengajuan dan persetujuan cuti yang streamlined lengkap dengan notifikasi otomatis ke semua pihak terkait.</p>
            </div>
            <div class="feature-card gold reveal">
                <div class="feature-icon"><i class="fas fa-exchange-alt"></i></div>
                <h3>Tukar Shift</h3>
                <p>Fasilitas tukar jadwal antar pegawai dalam departemen yang sama dengan mekanisme persetujuan otomatis.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-plane"></i></div>
                <h3>Perjalanan Dinas</h3>
                <p>Manajemen perjalanan dinas lengkap termasuk full-day & half-day dengan kalkulasi estimasi biaya.</p>
            </div>
            <div class="feature-card gold reveal">
                <div class="feature-icon"><i class="fas fa-file-chart-bar"></i></div>
                <h3>Laporan & Ekspor</h3>
                <p>Ekspor rekap absensi, cuti, dan perjalanan dinas ke Excel/PDF dengan format profesional siap cetak.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     BENTO SHOWCASE
═══════════════════════════════════════════════════════════ -->
<section class="bento-section">
    <div class="bento-inner">
        <div class="reveal" style="text-align:center;margin-bottom:16px;">
            <div class="section-label">
                <i class="fas fa-th-large"></i> Fitur Detail
            </div>
            <h2 class="section-title">Lihat SIDIA Beraksi</h2>
        </div>

        <div class="bento-grid">

            <!-- JADWAL -->
            <div class="bento-card bento-card-jadwal reveal">
                <div>
                    <div class="bento-tag green"><i class="fas fa-calendar-week"></i> Manajemen Jadwal</div>
                    <h3>Jadwal Pegawai<br>Terintegrasi</h3>
                    <ul class="bento-bullets">
                        <li>Generate Rotasi Otomatis</li>
                        <li>Pemfilteran Berdasarkan Departemen/Shift</li>
                        <li>Detail Jadwal Pegawai Real-time</li>
                    </ul>
                </div>
                <div class="mock-dashboard">
                    <div style="font-size:.7rem;color:rgba(255,255,255,.4);margin-bottom:8px;">Manajemen Jadwal Pegawai</div>
                    <div class="mock-row">
                        <div class="mock-avatar" style="background:#16a34a;">DP</div>
                        <div>
                            <div style="font-size:.78rem;font-weight:600;">Dimas Pratama (Dummy)</div>
                            <div style="font-size:.66rem;color:rgba(255,255,255,.4);">Perawat · Unit A</div>
                        </div>
                        <div class="mock-shift-badge" style="background:rgba(52,211,153,.15);color:#6ee7b7;">Shift Pagi</div>
                    </div>
                    <div class="mock-row">
                        <div class="mock-avatar" style="background:#d97706;">SN</div>
                        <div>
                            <div style="font-size:.78rem;font-weight:600;">Sinta Nabila (Dummy)</div>
                            <div style="font-size:.66rem;color:rgba(255,255,255,.4);">Dokter · Poli B</div>
                        </div>
                        <div class="mock-shift-badge" style="background:rgba(245,166,35,.15);color:#fcd34d;">Shift Siang</div>
                    </div>
                    <div class="mock-row">
                        <div class="mock-avatar" style="background:#7c3aed;">RF</div>
                        <div>
                            <div style="font-size:.78rem;font-weight:600;">Rafi Fauzan (Dummy)</div>
                            <div style="font-size:.66rem;color:rgba(255,255,255,.4);">Tenaga Kesehatan</div>
                        </div>
                        <div class="mock-shift-badge" style="background:rgba(124,58,237,.15);color:#c4b5fd;">Shift Malam</div>
                    </div>
                </div>
            </div>

            <!-- ABSENSI -->
            <div class="bento-card bento-card-absensi reveal">
                <div>
                    <div class="bento-tag blue"><i class="fas fa-fingerprint"></i> Absensi</div>
                    <h3>Absensi Mandiri<br>&amp; Akurat</h3>
                    <ul class="bento-bullets">
                        <li>Pencatatan Waktu Digital</li>
                        <li>Geofencing &amp; Map Lokasi Terpilih</li>
                        <li>Unggah Foto Check-In</li>
                    </ul>
                </div>
                <div class="checkin-card animate-float">
                    <div class="emp-row">
                        <div class="emp-avatar">DP</div>
                        <div>
                            <div class="emp-name">Dimas Pratama (Dummy)</div>
                            <div class="emp-role">DUMMY-EMP-001 &bull; Perawat</div>
                        </div>
                    </div>
                    <div class="time-grid">
                        <div class="time-box">
                            <div class="time-box-label">WAKTU SAAT INI</div>
                            <div class="time-box-val" id="live-time">10:25:59</div>
                        </div>
                        <div class="time-box">
                            <div class="time-box-label">STATUS</div>
                            <div class="time-box-val" style="color:#34d399;">Hadir</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AKSES -->
            <div class="bento-card bento-card-akses reveal">
                <div class="bento-tag purple"><i class="fas fa-lock"></i> Akses</div>
                <h3>Akses Cepat<br>&amp; Aman</h3>
                <ul class="bento-bullets">
                    <li>Single Sign-On untuk Semua Role</li>
                    <li>Proteksi dengan Rate Limiting</li>
                    <li>Role-Based Access Control (RBAC)</li>
                </ul>
                <div class="login-mock animate-float-r">
                    <div style="text-align:center;margin-bottom:4px;">
                        <div style="font-size:.78rem;font-weight:700;color:rgba(255,255,255,.8);">Masuk SIDIA</div>
                        <div style="font-size:.66rem;color:rgba(255,255,255,.35);">RSUD Haji Darlan Ismail</div>
                    </div>
                    <label>Email / Username</label>
                    <div class="field">dummy.user@sidia.test</div>
                    <label>Kata Sandi</label>
                    <div class="field">••••••••••</div>
                    <div class="mock-btn">→ Masuk</div>
                </div>
            </div>

            <!-- SOSIAL -->
            <div class="bento-card bento-card-sosial reveal">
                <div>
                    <div class="bento-tag orange"><i class="fas fa-users"></i> Kesejahteraan</div>
                    <h3>Meningkatkan Kesejahteraan<br>Pegawai</h3>
                    <p style="margin-top:8px;">Fitur untuk mengelola kegiatan sosial dan data pegawai.</p>
                    <ul class="bento-bullets" style="margin-top:12px;">
                        <li>Pengelolaan Data Dokumen Pegawai</li>
                        <li>Manajemen Perjalanan Dinas</li>
                        <li>Rekap Cuti &amp; Lembur Terintegrasi</li>
                    </ul>
                </div>
                <div class="photo-card animate-float">
                    <img src="{{ asset('images/hospital.jpg') }}" alt="RSUD Haji Darlan Ismail"
                         onerror="this.style.display='none';this.nextElementSibling.style.background='rgba(251,146,60,.08)';">
                    <div class="photo-overlay">
                        <div style="font-size:.78rem;font-weight:700;color:#fed7aa;">RSUD Haji Darlan Ismail</div>
                        <div style="font-size:.66rem;color:rgba(255,255,255,.5);">Rumah Sakit Daerah Terpercaya</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════════════════════════ -->
<section class="how-section">
    <div class="how-inner">
        <div class="reveal" style="text-align:center;">
            <div class="section-label">
                <i class="fas fa-route"></i> Cara Kerja
            </div>
            <h2 class="section-title">Mulai Dalam<br>3 Langkah Mudah</h2>
        </div>

        <div class="how-steps">
            <div class="how-step reveal">
                <div class="step-num">1</div>
                <div class="step-content">
                    <h3>Hubungi Administrator</h3>
                    <p>Daftarkan akun Anda melalui administrator rumah sakit atau bagian kepegawaian. Data Anda akan diverifikasi dan disetujui sebelum akses diberikan.</p>
                </div>
            </div>
            <div class="how-step reveal">
                <div class="step-num">2</div>
                <div class="step-content">
                    <h3>Masuk ke SIDIA</h3>
                    <p>Gunakan email dan kata sandi yang diberikan untuk masuk ke portal. Sistem akan secara otomatis mengarahkan Anda ke dashboard sesuai peran (Pegawai / Manager / Admin).</p>
                </div>
            </div>
            <div class="how-step reveal">
                <div class="step-num">3</div>
                <div class="step-content">
                    <h3>Kelola Aktivitas Anda</h3>
                    <p>Catat kehadiran, ajukan cuti, lihat jadwal shift, dan kelola dokumen — semua dari satu platform terpadu yang dapat diakses kapan saja, di mana saja.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     CTA
═══════════════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="cta-inner reveal">
        <div class="cta-glow">
            <div class="blob" style="width:300px;height:300px;background:rgba(52,211,153,.1);top:50%;left:50%;transform:translate(-50%,-50%);filter:blur(60px);z-index:0;"></div>
            <div style="position:relative;z-index:1;">
                <div class="section-label" style="margin:0 auto 20px;width:fit-content;">
                    <i class="fas fa-rocket"></i> Siap Memulai?
                </div>
                <h2>Masuk ke SIDIA<br>Sekarang</h2>
                <p>Akses platform manajemen SDM rumah sakit yang<br>modern, aman, dan terintegrasi penuh.</p>
                <a href="{{ route('login') }}" class="btn-primary" style="display:inline-flex;margin:0 auto;">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk ke Portal
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════ -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <img src="{{ asset('images/logo-rs.png') }}" alt="Logo RSUD">
            <div class="footer-brand-text">
                <h4>SIDIA</h4>
                <p>Sistem Informasi Darlan Ismail &amp; Absensi</p>
            </div>
        </div>
        <div class="footer-copy">
            &copy; {{ date('Y') }} RSUD Haji Darlan Ismail. All rights reserved.
        </div>
        <!-- <div class="footer-links">
            <a href="#">Pendaftaran Akun</a>
            <a href="#">Panduan Pengguna</a>
            <a href="#">Hubungi Dukungan</a>
        </div> -->
    </div>
</footer>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
/* ── Navbar scroll ── */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
});

/* ── Scroll reveal ── */
const reveals = document.querySelectorAll('.reveal');
const revealObs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => e.target.classList.add('visible'), i * 80);
            revealObs.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });
reveals.forEach(el => revealObs.observe(el));

/* ── Live clock ── */
function tick() {
    const el = document.getElementById('live-time');
    if (!el) return;
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    el.textContent = `${h}:${m}:${s}`;
}
tick();
setInterval(tick, 1000);

/* ── Particle canvas ── */
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
        this.hue  = rand(140, 170); // green tones
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

    function loop() {
        ctx.clearRect(0, 0, W, H);
        particles.forEach(p => { p.update(); p.draw(); });
        requestAnimationFrame(loop);
    }
    loop();

    // draw faint connection lines
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
    // override loop to add lines
    function loop2() {
        ctx.clearRect(0, 0, W, H);
        particles.forEach(p => { p.update(); p.draw(); });
        drawLines();
        requestAnimationFrame(loop2);
    }
    cancelAnimationFrame(loop);
    loop2();
})();
</script>
</body>
</html>
