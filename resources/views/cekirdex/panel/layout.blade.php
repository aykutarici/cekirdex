<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel') · Çekirdex</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('cekirdex/favicon.svg') }}">
    <meta name="theme-color" content="#ff6b35">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --c-bg: #f7f6fb; --c-card: #fff; --c-text: #1c1933; --c-text-soft: #4a4566;
            --c-muted: #7d7995; --c-accent: #ff6b35; --c-accent-d: #d9531c;
            --c-line: #ece9f4; --c-line-2: #d9d3e7;
            --c-shadow: 0 4px 14px -6px rgba(28,25,51,.10);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Inter", system-ui, sans-serif;
            background: var(--c-bg); color: var(--c-text);
            -webkit-font-smoothing: antialiased; min-height: 100vh;
        }
        a { color: var(--c-accent-d); text-decoration: none; }
        h1, h2, h3, h4 { color: var(--c-text); letter-spacing: -0.02em; line-height: 1.2; }
        img { max-width: 100%; display: block; }

        .pp-shell { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        @media (max-width: 900px) { .pp-shell { grid-template-columns: 1fr; } }

        /* SIDEBAR */
        .pp-side {
            background: #fff; border-right: 1px solid var(--c-line);
            padding: 22px 0; position: sticky; top: 0; height: 100vh; overflow-y: auto;
        }
        @media (max-width: 900px) { .pp-side { position: static; height: auto; } }
        .pp-brand { padding: 0 22px 18px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--c-line); }
        .pp-brand .mark {
            width: 34px; height: 34px; border-radius: 11px;
            background: linear-gradient(135deg,#ff8a4c,#ff6b35);
            display: flex; align-items: center; justify-content: center; color: #fff;
        }
        .pp-brand strong { font-weight: 800; font-size: 1.1rem; letter-spacing: -0.02em; color: var(--c-text); }
        .pp-rest {
            padding: 12px 22px; font-size: .82rem; color: var(--c-muted);
            border-bottom: 1px solid var(--c-line);
        }
        .pp-rest .name { color: var(--c-text); font-weight: 700; font-size: .92rem; }
        .pp-nav { padding: 10px 12px; }
        .pp-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px;
            color: var(--c-text-soft); font-weight: 600; font-size: .92rem;
            margin-bottom: 4px; transition: all .15s;
        }
        .pp-nav a:hover { background: #fff8f1; color: var(--c-accent-d); }
        .pp-nav a.is-active { background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff !important; box-shadow: 0 6px 14px -4px rgba(255,107,53,.45); }
        .pp-nav a i { width: 20px; text-align: center; }
        .pp-nav .group-label { padding: 16px 14px 6px; font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: var(--c-muted); font-weight: 700; }
        .pp-foot { padding: 14px 22px; font-size: .8rem; color: var(--c-muted); border-top: 1px solid var(--c-line); }
        .pp-foot .logout { color: var(--c-accent-d); font-weight: 600; cursor: pointer; background: transparent; border: 0; padding: 0; }

        /* MAIN */
        .pp-main { padding: 26px 32px 60px; }
        @media (max-width: 700px) { .pp-main { padding: 20px 18px 50px; } }
        .pp-head { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .pp-head h1 { font-size: 1.5rem; }
        .pp-head .sub { color: var(--c-text-soft); font-size: .92rem; }

        /* KPI CARDS */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
        @media (max-width: 1000px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 540px) { .kpi-grid { grid-template-columns: 1fr; } }
        .kpi {
            background: #fff; border: 1px solid var(--c-line); border-radius: 16px;
            padding: 20px 22px; box-shadow: var(--c-shadow);
        }
        .kpi .label { font-size: .82rem; color: var(--c-muted); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }
        .kpi .v { font-size: 1.85rem; font-weight: 800; margin-top: 8px; color: var(--c-text); }
        .kpi .ic { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; margin-bottom: 8px; }

        /* CARDS */
        .card { background: #fff; border: 1px solid var(--c-line); border-radius: 18px; padding: 22px 24px; margin-bottom: 18px; box-shadow: var(--c-shadow); }
        .card h2 { font-size: 1.05rem; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .card h2 i { color: var(--c-accent-d); }
        .card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media (max-width: 900px) { .card-grid { grid-template-columns: 1fr; } }

        /* TABLES */
        .pp-table { width: 100%; border-collapse: collapse; }
        .pp-table th { text-align: left; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; color: var(--c-muted); padding: 10px 12px; border-bottom: 1px solid var(--c-line); font-weight: 700; }
        .pp-table td { padding: 12px; border-bottom: 1px solid var(--c-line); font-size: .92rem; color: var(--c-text); vertical-align: top; }
        .pp-table tr:last-child td { border-bottom: 0; }
        .pp-table .muted { color: var(--c-muted); font-size: .85rem; }

        /* BADGES */
        .badge { display: inline-block; font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 99px; }
        .badge-new       { background: #fff3eb; color: #d9531c; }
        .badge-confirmed { background: #fef3c7; color: #92400e; }
        .badge-preparing { background: #fee9c2; color: #c2410c; }
        .badge-ready     { background: #dcfce7; color: #166534; }
        .badge-served    { background: #e0f2fe; color: #075985; }
        .badge-closed    { background: #ede9fe; color: #5b21b6; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-responded { background: #dcfce7; color: #166534; }

        /* FORMS */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
        .form-row label, .form-block label { display: block; font-size: .82rem; font-weight: 600; color: var(--c-text); margin-bottom: 4px; }
        .form-block { margin-bottom: 12px; }
        .form-row input, .form-row select, .form-row textarea,
        .form-block input, .form-block select, .form-block textarea {
            width: 100%; padding: 10px 12px; font-size: .92rem;
            border: 1.5px solid var(--c-line-2); border-radius: 10px;
            background: #fff; color: var(--c-text); font-family: inherit;
        }
        .form-row input:focus, .form-row select:focus, .form-row textarea:focus,
        .form-block input:focus, .form-block select:focus, .form-block textarea:focus {
            outline: none; border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(255,107,53,.12);
        }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 16px; border-radius: 10px;
            font-weight: 700; font-size: .88rem;
            background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff !important;
            border: 0; cursor: pointer; text-decoration: none !important;
            box-shadow: 0 8px 22px -8px rgba(255,107,53,.55);
        }
        .btn:hover { filter: brightness(1.06); }
        .btn-sm { padding: 6px 12px; font-size: .82rem; border-radius: 8px; }
        .btn-ghost { background: #fff; color: var(--c-text) !important; border: 1px solid var(--c-line-2); box-shadow: none; }
        .btn-ghost:hover { background: var(--c-bg); border-color: var(--c-accent); }
        .btn-danger { background: #fee2e2; color: #991b1b !important; box-shadow: none; }
        .btn-danger:hover { background: #fecaca; }

        .alert {
            padding: 12px 16px; border-radius: 12px; margin-bottom: 18px;
            background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d;
            display: flex; gap: 10px; align-items: flex-start; font-size: .92rem;
        }
        .alert.error { background: #fef2f2; border-color: #fecaca; color: #7f1d1d; }
    </style>
    @stack('styles')
</head>
<body>
@php
    $u = Auth::guard('cekirdex')->user();
    $rest = $u?->restaurant;
@endphp
<div class="pp-shell">
    <aside class="pp-side">
        <a class="pp-brand" href="{{ route('cekirdex.panel.dashboard') }}">
            <span class="mark"><i class="fas fa-utensils"></i></span>
            <strong>Çekirdex</strong>
            <button id="snd-toggle" type="button" onclick="event.preventDefault(); cekirdexToggleSound();" title="Bildirim sesi"
                style="margin-left:auto;background:transparent;border:0;color:#7d7995;cursor:pointer;font-size:1rem;padding:6px;border-radius:8px">
                <i class="fas fa-volume-high"></i>
            </button>
        </a>

        @if($rest)
            <div class="pp-rest">
                <div class="name">{{ $rest->name }}</div>
                <div>{{ $u->name }} · {{ ucfirst($u->role) }}</div>
            </div>
        @endif

        @php
            $role = $u->role ?? 'owner';
            $isOwnerLike  = in_array($role, ['owner','manager','super_admin'], true);
            $isOwner      = in_array($role, ['owner','super_admin'], true);
            $isKitchen    = $role === 'kitchen';
            $isWaiter     = $role === 'waiter';
        @endphp

        <div class="pp-nav">
            @unless($isKitchen)
                <a href="{{ route('cekirdex.panel.dashboard') }}" class="@if(request()->routeIs('cekirdex.panel.dashboard')) is-active @endif">
                    <i class="fas fa-gauge-high"></i> Anasayfa
                </a>
            @endunless
            @if($isKitchen)
                <a href="{{ route('cekirdex.panel.kds.index') }}" class="@if(request()->routeIs('cekirdex.panel.kds.*')) is-active @endif">
                    <i class="fas fa-fire"></i> Mutfak (KDS)
                </a>
            @elseif($isWaiter)
                <a href="{{ route('cekirdex.panel.service.index') }}" class="@if(request()->routeIs('cekirdex.panel.service.*')) is-active @endif">
                    <i class="fas fa-hand-holding"></i> Servis ekranı
                </a>
            @else
                <a href="{{ route('cekirdex.panel.kds.index') }}" class="@if(request()->routeIs('cekirdex.panel.kds.*')) is-active @endif">
                    <i class="fas fa-fire"></i> Mutfak (KDS)
                </a>
                <a href="{{ route('cekirdex.panel.service.index') }}" class="@if(request()->routeIs('cekirdex.panel.service.*')) is-active @endif">
                    <i class="fas fa-hand-holding"></i> Servis ekranı
                </a>
            @endif
            @unless($isKitchen)
                <a href="{{ route('cekirdex.panel.bills.index') }}" class="@if(request()->routeIs('cekirdex.panel.bills.*')) is-active @endif">
                    <i class="fas fa-file-invoice-dollar"></i> Hesaplar
                </a>
                <a href="{{ route('cekirdex.panel.orders.index') }}" class="@if(request()->routeIs('cekirdex.panel.orders.*')) is-active @endif">
                    <i class="fas fa-receipt"></i> Siparişler
                </a>
                <a href="{{ route('cekirdex.panel.takeaway.index') }}" class="@if(request()->routeIs('cekirdex.panel.takeaway.*')) is-active @endif">
                    <i class="fas fa-bag-shopping"></i> Paket Siparişler
                </a>
                <a href="{{ route('cekirdex.panel.reservations.index') }}" class="@if(request()->routeIs('cekirdex.panel.reservations.*')) is-active @endif">
                    <i class="fas fa-calendar-check"></i> Rezervasyonlar
                </a>
                <a href="{{ route('cekirdex.panel.calls.index') }}" class="@if(request()->routeIs('cekirdex.panel.calls.*')) is-active @endif">
                    <i class="fas fa-bell-concierge"></i> Çağrılar
                </a>
            @endunless

            @if($isOwnerLike)
                <div class="group-label">Yönetim</div>
                <a href="{{ route('cekirdex.panel.menu.index') }}" class="@if(request()->routeIs('cekirdex.panel.menu.*')) is-active @endif">
                    <i class="fas fa-utensils"></i> Menü
                </a>
                <a href="{{ route('cekirdex.panel.tables.index') }}" class="@if(request()->routeIs('cekirdex.panel.tables.*')) is-active @endif">
                    <i class="fas fa-qrcode"></i> Masalar & QR
                </a>
                <a href="{{ route('cekirdex.panel.reviews.index') }}" class="@if(request()->routeIs('cekirdex.panel.reviews.*')) is-active @endif">
                    <i class="fas fa-comments"></i> Yorumlar
                </a>
                <a href="{{ route('cekirdex.panel.settings.general') }}" class="@if(request()->routeIs('cekirdex.panel.settings.*')) is-active @endif">
                    <i class="fas fa-sliders"></i> Ayarlar
                </a>
            @endif
            @if($isOwner)
                <a href="{{ route('cekirdex.panel.staff.index') }}" class="@if(request()->routeIs('cekirdex.panel.staff.*')) is-active @endif">
                    <i class="fas fa-users-gear"></i> Personel
                </a>
            @endif

            <div class="group-label">Hesap</div>
            <a href="{{ route('cekirdex.panel.profile') }}" class="@if(request()->routeIs('cekirdex.panel.profile')) is-active @endif">
                <i class="fas fa-user"></i> Profilim
            </a>
            <a href="{{ route('cekirdex.landing') }}" target="_blank">
                <i class="fas fa-globe"></i> Çekirdex Sitesi
            </a>
        </div>

        <div class="pp-foot">
            <form action="{{ route('cekirdex.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout"><i class="fas fa-arrow-right-from-bracket"></i> Çıkış</button>
            </form>
        </div>
    </aside>

    <main class="pp-main">
        @if(session('success'))<div class="alert"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>@endif
        @if(session('info'))<div class="alert"><i class="fas fa-circle-info"></i><span>{{ session('info') }}</span></div>@endif
        @if($errors->any())<div class="alert error"><i class="fas fa-circle-exclamation"></i><span>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</span></div>@endif

        @yield('content')
    </main>
</div>

@auth('cekirdex')
<script>
/* Çekirdex Panel — global yeni-sipariş/çağrı bildirimi */
(function () {
    if (window.__cekirdexNotifierLoaded) return;
    window.__cekirdexNotifierLoaded = true;

    // KDS kendi içinde polling yaptığı için orada çift kalmamak adına burada da çalışsın (uyumlu)
    const FEED  = @json(route('cekirdex.panel.dashboard.feed'));
    const STORE = 'cekirdex.notifier.lastOrderId';
    const SOUND_KEY = 'cekirdex.notifier.sound';

    let lastSeen   = parseInt(localStorage.getItem(STORE) || '0', 10) || 0;
    let soundOn    = localStorage.getItem(SOUND_KEY) !== '0';
    let firstFetch = true;

    function ping() {
        if (!soundOn) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const o = ctx.createOscillator(); const g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.frequency.value = 940;
            g.gain.setValueAtTime(0.0001, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.35, ctx.currentTime + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.4);
            o.start(); o.stop(ctx.currentTime + 0.45);
        } catch (e) {}
    }

    function flashTitle(text) {
        const orig = document.title;
        let n = 0;
        const id = setInterval(() => {
            document.title = (n % 2 === 0) ? text : orig;
            n++;
            if (n > 8) { clearInterval(id); document.title = orig; }
        }, 700);
    }

    async function tick() {
        try {
            const r = await fetch(FEED, { headers: { Accept: 'application/json' }});
            if (!r.ok) return;
            const d = await r.json();
            const last = d.last_order_id || 0;
            if (!firstFetch && last > lastSeen) {
                ping();
                flashTitle('🔔 Yeni sipariş!');
            }
            firstFetch = false;
            lastSeen   = Math.max(lastSeen, last);
            localStorage.setItem(STORE, String(lastSeen));

            const badge = document.getElementById('notifier-active');
            if (badge) badge.textContent = (d.active_orders || 0);
        } catch (e) {}
    }

    window.cekirdexToggleSound = function () {
        soundOn = !soundOn;
        localStorage.setItem(SOUND_KEY, soundOn ? '1' : '0');
        const b = document.getElementById('snd-toggle');
        if (b) b.innerHTML = soundOn ? '<i class="fas fa-volume-high"></i>' : '<i class="fas fa-volume-xmark"></i>';
        if (soundOn) ping();
    };

    setTimeout(tick, 1500);
    setInterval(tick, 8000);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') tick(); });
})();
</script>
@endauth
@stack('scripts')
</body>
</html>
