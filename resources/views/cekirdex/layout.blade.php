<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Çekirdex — Restoranlar için yeni nesil QR sipariş ve ödeme platformu')</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @else
        <meta name="description" content="Çekirdex; QR menü, masadan sipariş, garson çağırma, hesap paylaşma, ödeme ve sadakat sistemini tek çatıda toplayan restoran platformudur. Aylık ücret yok.">
    @endif
    <meta name="theme-color" content="#f26a3d">
    <link rel="icon" type="image/svg+xml" href="{{ asset('cekirdex/favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('cekirdex/manifest.webmanifest') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Çekirdex">
    <meta property="og:title" content="@yield('title', 'Çekirdex — Restoranlar için yeni nesil QR sipariş ve ödeme platformu')">
    <meta property="og:description" content="@yield('description', 'QR menü, masadan sipariş, garson çağırma, hesap bölme, ödeme ve sadakat — tek platformda. Aylık ücret yok.')">
    <meta property="og:image" content="{{ asset('cekirdex/og-image.svg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="tr_TR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Çekirdex')">
    <meta name="twitter:description" content="@yield('description', 'QR menü, sipariş ve ödeme — tek platformda.')">
    <meta name="twitter:image" content="{{ asset('cekirdex/og-image.svg') }}">
    @stack('meta')
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --c-bg:        #ffffff;
            --c-bg-2:      #fbfaff;
            --c-bg-warm:   #fffaf3;
            --c-card:      #ffffff;
            --c-text:      #1c1933;
            --c-text-soft: #4a4566;
            --c-muted:     #7d7995;
            --c-accent:    #f26a3d;
            --c-accent-d:  #d9531c;
            --c-accent2:   #ffb347;        /* amber */
            --c-deep:      #2a1f4d;        /* derin lacivert (vurgular için) */
            --c-mint:      #10b981;
            --c-rose:      #f472b6;
            --c-line:      #efece6;
            --c-line-2:    #e5e0d4;
            --c-shadow:    0 6px 22px -8px rgba(28, 25, 51, .12);
            --c-shadow-lg: 0 24px 60px -16px rgba(255, 107, 53, .22);
            --c-max: 1180px;
            --c-max-narrow: 820px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: var(--c-bg);
            color: var(--c-text);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        ::selection { background: #ffd9c2; color: #6b2400; }
        a { color: var(--c-accent-d); text-decoration: none; }
        a:hover { text-decoration: underline; }
        img { max-width: 100%; display: block; }
        h1, h2, h3, h4 { color: var(--c-text); letter-spacing: -0.02em; line-height: 1.2; }
        p { color: var(--c-text-soft); }

        /* === NAV === */
        .c-nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.85);
            backdrop-filter: saturate(160%) blur(14px);
            -webkit-backdrop-filter: saturate(160%) blur(14px);
            border-bottom: 1px solid var(--c-line);
        }
        .c-nav-inner {
            max-width: var(--c-max);
            margin: 0 auto;
            padding: 12px 22px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px 16px;
        }
        .c-nav-start {
            display: flex;
            align-items: center;
            flex: 0 0 auto;
        }
        .c-nav-main {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: clamp(14px, 2.2vw, 26px);
            font-size: .92rem;
            flex: 1 1 auto;
            min-width: min(100%, 280px);
        }
        .c-nav-main a {
            color: var(--c-text-soft);
            font-weight: 600;
            white-space: nowrap;
        }
        .c-nav-main a:hover { color: var(--c-accent-d); text-decoration: none; }
        .c-nav-main a.is-active { color: var(--c-accent-d); }
        .c-nav-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 10px 12px;
            flex: 0 0 auto;
            margin-left: auto;
        }
        .c-brand {
            font-weight: 800; font-size: 1.25rem; letter-spacing: -0.02em;
            color: var(--c-text); display: inline-flex; align-items: center; gap: 10px;
        }
        .c-brand:hover { text-decoration: none; color: var(--c-accent-d); }
        .c-brand-img {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 4px 14px -4px rgba(0, 0, 0, 0.35);
        }
        .c-nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #fff !important;
            background: linear-gradient(135deg, #f28a5c, #f26a3d);
            padding: 10px 18px;
            border-radius: 12px;
            box-shadow: 0 8px 22px -8px rgba(242, 106, 61, 0.45);
            font-weight: 700;
            font-size: .88rem;
        }
        .c-nav-cta-icon { font-size: .85em; opacity: .95; }
        .c-nav-cta:hover { filter: brightness(1.06); text-decoration: none !important; }
        .c-nav-secondary {
            color: var(--c-text) !important;
            border: 1px solid var(--c-line-2);
            padding: 8px 16px; border-radius: 10px;
            font-weight: 600;
        }
        .c-nav-secondary:hover { background: var(--c-bg-2); text-decoration: none; }

        /* === BUTTONS === */
        .btn-c {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px 26px; border-radius: 14px;
            font-weight: 700; font-size: .98rem;
            background: linear-gradient(135deg, #f28a5c, #f26a3d);
            color: #fff !important; text-decoration: none !important;
            box-shadow: 0 14px 32px -10px rgba(242, 106, 61, 0.45);
            transition: all .25s; border: 0; cursor: pointer;
        }
        .btn-c:hover { filter: brightness(1.05); transform: translateY(-2px); text-decoration: none !important; }
        .btn-c-ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 24px; border-radius: 14px;
            font-weight: 700; font-size: .95rem;
            background: #fff; color: var(--c-deep) !important;
            border: 1.5px solid var(--c-line-2);
            text-decoration: none !important;
            transition: all .2s;
        }
        .btn-c-ghost:hover { background: var(--c-bg-2); border-color: var(--c-accent); color: var(--c-accent-d) !important; transform: translateY(-1px); }

        /* === LAYOUT === */
        .c-container { max-width: var(--c-max); margin: 0 auto; padding: 0 22px; }
        .c-narrow    { max-width: var(--c-max-narrow); margin: 0 auto; padding: 28px 18px 80px; }
        section { position: relative; }

        /* === FOOTER === */
        .c-foot {
            margin-top: 80px;
            background: linear-gradient(180deg, #fbfaff 0%, #ffffff 100%);
            border-top: 1px solid var(--c-line);
        }
        .c-foot-inner {
            max-width: var(--c-max);
            margin: 0 auto;
            padding: 56px 22px 32px;
            display: grid;
            grid-template-columns: 1.75fr repeat(4, minmax(0, 1fr));
            gap: 36px;
        }
        .c-foot h4 { font-size: .92rem; font-weight: 700; margin-bottom: 14px; color: var(--c-text); }
        .c-foot ul { list-style: none; padding: 0; }
        .c-foot li { padding: 4px 0; }
        .c-foot a { color: var(--c-text-soft); font-size: .9rem; }
        .c-foot a:hover { color: var(--c-accent-d); text-decoration: none; }
        .c-foot-bottom {
            border-top: 1px solid var(--c-line);
            padding: 18px 22px;
            text-align: center; font-size: .82rem; color: var(--c-muted);
        }
        .c-foot-social {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }
        .c-foot-social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid var(--c-line-2);
            background: #fff;
            color: var(--c-text-soft);
            transition: color .2s, border-color .2s, background .2s;
        }
        .c-foot-social-link:hover {
            color: var(--c-accent-d);
            border-color: rgba(255, 107, 53, 0.35);
            background: #fffaf6;
            text-decoration: none !important;
        }
        @media (max-width: 1024px) {
            .c-foot-inner { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 520px) {
            .c-foot-inner { grid-template-columns: 1fr; }
        }

        @media (max-width: 860px) {
            .c-nav-inner {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: center;
            }
            .c-nav-start { grid-column: 1; grid-row: 1; }
            .c-nav-actions {
                grid-column: 2;
                grid-row: 1;
                margin-left: 0;
                justify-self: end;
            }
            .c-nav-main {
                grid-column: 1 / -1;
                grid-row: 2;
                justify-content: center;
                padding-top: 12px;
                margin-top: 4px;
                border-top: 1px solid rgba(28, 25, 51, .08);
            }
        }

        /* Genel utility */
        .c-eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .75rem; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--c-accent-d);
            background: #fff3eb;
            border: 1px solid #ffd9c2;
            padding: 6px 12px; border-radius: 999px;
            margin-bottom: 14px;
        }
        .alert-c {
            background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d;
            padding: 14px 18px; border-radius: 12px; margin-bottom: 18px;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .alert-c.error { background: #fef2f2; border-color: #fecaca; color: #7f1d1d; }
        .alert-c i { margin-top: 2px; }
    </style>
    @stack('styles')
</head>
<body @class(['ck-landing-active' => request()->routeIs('cekirdex.landing')])>
    @include('cekirdex.partials.nav')

    @hasSection('outer')
        @yield('outer')
    @endif

    @hasSection('content')
        <main class="c-narrow">
            @if(session('success'))<div class="alert-c"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>@endif
            @if(session('error'))  <div class="alert-c error"><i class="fas fa-circle-exclamation"></i><span>{{ session('error') }}</span></div>@endif
            @yield('content')
        </main>
    @endif

    @include('cekirdex.partials.footer')

    @stack('scripts')
</body>
</html>
