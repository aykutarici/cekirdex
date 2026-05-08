<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $restaurant->name }} · Çekirdex</title>
    <meta name="description" content="{{ $restaurant->description ?: $restaurant->name.' menüsü, paket sipariş ve rezervasyon.' }}">
    <meta name="theme-color" content="{{ $restaurant->primary_color ?: '#ff6b35' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta property="og:title" content="{{ $restaurant->name }} · Çekirdex">
    <meta property="og:description" content="{{ $restaurant->description ?: 'Menü · Paket sipariş · Rezervasyon' }}">
    <meta property="og:type" content="restaurant">
    @if($restaurant->cover_image_url)
        <meta property="og:image" content="{{ $restaurant->cover_image_url }}">
    @endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('cekirdex/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --p: {{ $restaurant->primary_color ?: '#ff6b35' }};
            --p-d: {{ $restaurant->secondary_color ?: '#d9531c' }};
            --bg: #fffaf3; --card: #ffffff; --text: #1c1933;
            --soft: #4a4566; --muted: #7d7995; --line: #efece6;
            --shadow: 0 4px 14px -6px rgba(28,25,51,.10);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: var(--bg); color: var(--text); font-family: "Inter", system-ui, sans-serif; min-height: 100vh; -webkit-font-smoothing: antialiased; }
        body { padding-bottom: 100px; }
        a { color: var(--p-d); text-decoration: none; }
        img { max-width: 100%; display: block; }

        .hero { position: relative; }
        .hero .cover {
            height: 220px;
            background: linear-gradient(135deg, var(--p), var(--p-d));
            background-size: cover; background-position: center;
            @if($restaurant->cover_image_url) background-image: url('{{ $restaurant->cover_image_url }}'); @endif
        }
        .hero .info {
            background: var(--card); margin: -36px 16px 0; padding: 22px 20px 18px;
            border-radius: 18px; box-shadow: var(--shadow); position: relative;
        }
        .hero h1 { font-size: 1.45rem; letter-spacing: -0.02em; margin-bottom: 4px; }
        .hero .meta { color: var(--muted); font-size: .88rem; line-height: 1.6; }
        .hero .meta i { width: 16px; color: var(--p); }
        .hero .badges { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 6px; }
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 99px; font-size: .76rem; font-weight: 700;
        }
        .badge-on { background: #dcfce7; color: #166534; }
        .badge-off { background: #fee2e2; color: #991b1b; }
        .badge-soft { background: #fff3eb; color: var(--p-d); }

        .actions {
            margin: 14px 16px 0;
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
        }
        .actions button, .actions a {
            border: 0; cursor: pointer; padding: 14px 12px; border-radius: 14px;
            background: var(--card); color: var(--text); font-weight: 700; font-size: .94rem;
            box-shadow: var(--shadow); display: flex; flex-direction: column; align-items: center; gap: 6px;
            text-decoration: none; transition: transform .15s;
        }
        .actions button:active, .actions a:active { transform: scale(.97); }
        .actions .ic {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, var(--p), var(--p-d));
            color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
        }
        .actions .lbl-sub { font-size: .72rem; color: var(--muted); font-weight: 500; }

        .section-title {
            margin: 26px 18px 10px; font-size: 1.05rem; letter-spacing: -0.02em;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title i { color: var(--p); }

        .cats { display: flex; gap: 8px; padding: 0 16px; overflow-x: auto; padding-bottom: 6px; }
        .cats::-webkit-scrollbar { display: none; }
        .cats button {
            white-space: nowrap; padding: 8px 14px; border: 1px solid var(--line);
            border-radius: 99px; background: var(--card); color: var(--soft);
            font-weight: 600; font-size: .86rem; cursor: pointer;
        }
        .cats button.is-on { background: var(--p); color: #fff; border-color: var(--p); }

        .menu { padding: 8px 16px 0; display: grid; grid-template-columns: 1fr; gap: 10px; }
        @media (min-width: 700px) { .menu { grid-template-columns: 1fr 1fr; } }
        .item {
            background: var(--card); border-radius: 16px; padding: 14px;
            display: flex; gap: 12px; align-items: flex-start; box-shadow: var(--shadow);
        }
        .item.is-oos { opacity: .58; }
        .item .ph { width: 78px; height: 78px; border-radius: 12px; background: #f3eee5; flex-shrink: 0; object-fit: cover; }
        .item .body { flex: 1; min-width: 0; }
        .item .nm { font-weight: 700; font-size: .96rem; }
        .item .ds { font-size: .82rem; color: var(--muted); margin-top: 2px; line-height: 1.4; }
        .item .ft { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; }
        .item .pr { font-weight: 800; color: var(--p-d); font-size: 1rem; }
        .item button.add {
            border: 0; background: var(--p); color: #fff; border-radius: 10px;
            padding: 7px 12px; font-weight: 700; font-size: .82rem; cursor: pointer;
        }
        .item button.add[disabled] { opacity: .4; cursor: not-allowed; }
        .item .stats { font-size: .74rem; color: var(--muted); margin-top: 4px; display: flex; gap: 10px; }

        /* CART (sticky bottom) */
        .cart-bar {
            position: fixed; bottom: 14px; left: 16px; right: 16px;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff;
            border-radius: 16px; padding: 12px 16px; box-shadow: 0 12px 28px -8px rgba(255,107,53,.55);
            display: flex; align-items: center; gap: 12px; cursor: pointer;
            transform: translateY(140%); transition: transform .25s;
        }
        .cart-bar.show { transform: translateY(0); }
        .cart-bar .count { background: #fff; color: var(--p-d); width: 30px; height: 30px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .cart-bar .lbl { font-weight: 700; flex: 1; }
        .cart-bar .tt { font-weight: 800; }

        /* MODALS */
        .modal { position: fixed; inset: 0; background: rgba(28,25,51,.55); display: none; align-items: flex-end; justify-content: center; z-index: 60; }
        .modal.show { display: flex; }
        .modal .panel {
            background: var(--card); width: 100%; max-width: 540px; border-radius: 20px 20px 0 0;
            padding: 22px 22px 26px; max-height: 90vh; overflow-y: auto;
            animation: slide .25s ease-out;
        }
        @keyframes slide { from { transform: translateY(40px); opacity: .5; } to { transform: translateY(0); opacity: 1; } }
        .modal .x { position: absolute; right: 26px; top: 26px; background: var(--bg); border: 0; width: 36px; height: 36px; border-radius: 12px; cursor: pointer; }
        .modal .pick-var select {
            width: 100%; padding: 10px 12px; border: 1.5px solid var(--line); border-radius: 10px;
            font-size: .94rem; font-family: inherit; background: var(--card); margin-top: 8px;
        }

        .form-row label { font-size: .84rem; font-weight: 600; display: block; margin-bottom: 4px; }
        .form-row input, .form-row textarea, .form-row select {
            width: 100%; padding: 10px 12px; border: 1.5px solid var(--line); border-radius: 10px;
            font-size: .94rem; font-family: inherit; background: var(--card);
        }
        .form-row input:focus, .form-row textarea:focus, .form-row select:focus { outline: none; border-color: var(--p); box-shadow: 0 0 0 3px rgba(255,107,53,.12); }
        .form-row { margin-bottom: 12px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 16px; border-radius: 12px; font-weight: 800; font-size: .92rem;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff !important;
            border: 0; cursor: pointer; box-shadow: 0 8px 22px -8px rgba(255,107,53,.55); width: 100%;
        }
        .btn-ghost { background: var(--card); color: var(--text) !important; border: 1.5px solid var(--line); box-shadow: none; }

        .seg { display: grid; grid-template-columns: 1fr 1fr; background: var(--bg); padding: 4px; border-radius: 12px; gap: 4px; margin-bottom: 14px; }
        .seg button { background: transparent; border: 0; padding: 10px; border-radius: 10px; font-weight: 700; font-size: .88rem; cursor: pointer; color: var(--soft); }
        .seg button.is-on { background: var(--card); color: var(--text); box-shadow: var(--shadow); }

        .lines { background: var(--bg); border-radius: 12px; padding: 10px 14px; font-size: .92rem; }
        .lines .line { display: flex; justify-content: space-between; padding: 4px 0; }
        .lines .line.tot { font-weight: 800; padding-top: 8px; border-top: 1px solid var(--line); margin-top: 6px; }

        .cart-list .li { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--line); }
        .cart-list .li:last-child { border-bottom: 0; }
        .cart-list .li .nm { flex: 1; font-weight: 600; font-size: .9rem; }
        .cart-list .li .qty { display: flex; align-items: center; gap: 6px; }
        .cart-list .li .qty button { background: var(--bg); border: 0; width: 28px; height: 28px; border-radius: 8px; cursor: pointer; font-weight: 800; }
        .cart-list .li .pr { font-weight: 700; min-width: 60px; text-align: right; }

        .closed-banner {
            margin: 14px 16px; padding: 14px; background: #fef2f2; color: #991b1b;
            border: 1px solid #fecaca; border-radius: 12px; font-weight: 600; font-size: .9rem;
            display: flex; gap: 10px; align-items: center;
        }
        .toast {
            position: fixed; bottom: 90px; left: 50%; transform: translateX(-50%);
            background: var(--text); color: #fff; padding: 10px 18px; border-radius: 99px;
            font-size: .88rem; z-index: 80; opacity: 0; transition: opacity .25s;
            box-shadow: 0 12px 28px -8px rgba(28,25,51,.45);
        }
        .toast.show { opacity: 1; }
    </style>
</head>
<body>

<header class="hero">
    <div class="cover"></div>
    <div class="info">
        <h1>{{ $restaurant->name }}</h1>
        <div class="meta">
            @if($restaurant->description)<div>{{ \Illuminate\Support\Str::limit($restaurant->description, 140) }}</div>@endif
            @if($restaurant->address)<div><i class="fas fa-location-dot"></i> {{ $restaurant->address }}{{ $restaurant->city ? ', '.$restaurant->city : '' }}</div>@endif
            @if($restaurant->phone)<div><i class="fas fa-phone"></i> {{ $restaurant->phone }}</div>@endif
        </div>
        <div class="badges">
            <span class="badge {{ $isOpen ? 'badge-on' : 'badge-off' }}">
                <i class="fas fa-{{ $isOpen ? 'door-open' : 'door-closed' }}"></i>
                {{ $isOpen ? 'Şu an açık' : 'Şu an kapalı' }}
            </span>
            @if($restaurant->accepts_takeaway)<span class="badge badge-soft"><i class="fas fa-bag-shopping"></i> Gel-al</span>@endif
            @if($restaurant->accepts_delivery)<span class="badge badge-soft"><i class="fas fa-motorcycle"></i> Adrese teslim</span>@endif
            @if($restaurant->accepts_reservations)<span class="badge badge-soft"><i class="fas fa-calendar-check"></i> Rezervasyon</span>@endif
        </div>
    </div>
</header>

@if(!$isOpen)
    <div class="closed-banner">
        <i class="fas fa-circle-info"></i>
        <div>Restoran şu anda kapalı. Sipariş açıldığında alınacaktır.</div>
    </div>
@endif

<div class="actions" id="rezervasyon">
    @if($restaurant->accepts_takeaway || $restaurant->accepts_delivery)
        <button onclick="openOrderModal()" {{ $isOpen ? '' : 'disabled' }}>
            <span class="ic"><i class="fas fa-bag-shopping"></i></span>
            <span>Paket Sipariş</span>
            <span class="lbl-sub">Gel-al / adrese teslim</span>
        </button>
    @else
        <button disabled>
            <span class="ic" style="opacity:.5"><i class="fas fa-bag-shopping"></i></span>
            <span style="color:var(--muted)">Paket Sipariş</span>
            <span class="lbl-sub">Aktif değil</span>
        </button>
    @endif
    @if($restaurant->accepts_reservations)
        <button onclick="openReservationModal()">
            <span class="ic"><i class="fas fa-calendar-check"></i></span>
            <span>Rezervasyon</span>
            <span class="lbl-sub">Masa ayırt</span>
        </button>
    @else
        <button disabled>
            <span class="ic" style="opacity:.5"><i class="fas fa-calendar-check"></i></span>
            <span style="color:var(--muted)">Rezervasyon</span>
            <span class="lbl-sub">Aktif değil</span>
        </button>
    @endif
</div>

<div class="section-title"><i class="fas fa-utensils"></i> Menü</div>

<div class="cats">
    <button class="is-on" data-cat="all" onclick="setCat(this,'all')">Tümü</button>
    @foreach($categories as $cat)
        <button data-cat="{{ $cat->id }}" onclick="setCat(this,'{{ $cat->id }}')">{{ $cat->name }}</button>
    @endforeach
</div>

<div class="menu" id="menu-grid">
    @foreach($categories as $cat)
        @foreach(($products[$cat->id] ?? collect()) as $p)
            @php
                $ok = $p->isAvailable();
                $base = (float) ($p->discount_price ?: $p->price);
                $varJson = $p->variants->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'adj' => (float) $v->price_adjust])->values();
                $ra = $ratingAverages[$p->id] ?? null;
            @endphp
            <div class="item {{ $ok ? '' : 'is-oos' }}" data-cat="{{ $cat->id }}"
                 data-pid="{{ $p->id }}"
                 data-pname="{{ e($p->name) }}"
                 data-base="{{ $base }}"
                 data-available="{{ $ok ? '1' : '0' }}"
                 data-variants="{{ e($varJson->toJson(JSON_UNESCAPED_UNICODE)) }}">
                <img class="ph" src="{{ $p->image_url ?: asset('cekirdex/placeholder.svg') }}" alt="">
                <div class="body">
                    <div class="nm">{{ $p->name }}</div>
                    @if($p->description)<div class="ds">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</div>@endif
                    <div class="stats">
                        @if($ra)<span title="{{ $ra['count'] }} değerlendirme"><i class="fas fa-star" style="color:#f59e0b"></i> {{ $ra['avg'] }}</span>@endif
                        @if(($likeCounts[$p->id] ?? 0) > 0)<span><i class="fas fa-heart" style="color:#ef4444"></i> {{ $likeCounts[$p->id] }}</span>@endif
                        @if(($reviewCounts[$p->id] ?? 0) > 0)<span><i class="fas fa-comment"></i> {{ $reviewCounts[$p->id] }}</span>@endif
                    </div>
                    <div class="ft">
                        <div class="pr">
                            {{ number_format($base, 2, ',', '.') }} ₺@if($p->variants->isNotEmpty())<span style="font-size:.85rem;color:var(--muted)">+</span>@endif
                        </div>
                        <button class="add" type="button"
                            onclick="onMenuAdd(this)"
                            {{ ($isOpen && ($restaurant->accepts_takeaway || $restaurant->accepts_delivery) && $ok) ? '' : 'disabled' }}>
                            @if(!$ok)
                                Tükendi
                            @elseif($p->variants->isNotEmpty())
                                <i class="fas fa-sliders"></i> Seçenek
                            @else
                                <i class="fas fa-plus"></i> Ekle
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
</div>

<div class="cart-bar" id="cart-bar" onclick="openOrderModal()">
    <div class="count" id="cart-count">0</div>
    <div class="lbl">Sepetimi gör</div>
    <div class="tt" id="cart-total">0 ₺</div>
</div>

<!-- ORDER MODAL -->
<div class="modal" id="order-modal">
    <div class="panel" style="position: relative;">
        <button class="x" onclick="closeOrder()"><i class="fas fa-xmark"></i></button>
        <h3>Paket Sipariş</h3>

        @if($restaurant->accepts_takeaway && $restaurant->accepts_delivery)
            <div class="seg">
                <button class="is-on" id="seg-takeaway" onclick="setOrderType('takeaway')"><i class="fas fa-bag-shopping"></i> Gel-al</button>
                <button id="seg-delivery" onclick="setOrderType('delivery')"><i class="fas fa-motorcycle"></i> Adrese teslim</button>
            </div>
        @endif

        <div class="cart-list" id="cart-list" style="margin-bottom: 14px;"></div>

        <div class="lines" id="cart-summary"></div>

        <div style="margin-top: 14px;">
            <div class="form-grid">
                <div class="form-row">
                    <label>Ad Soyad</label>
                    <input id="ord-name" maxlength="120" autocomplete="name">
                </div>
                <div class="form-row">
                    <label>Telefon</label>
                    <input id="ord-phone" maxlength="24" autocomplete="tel" placeholder="05XX...">
                </div>
            </div>
            <div class="form-row">
                <label>E-posta (opsiyonel)</label>
                <input id="ord-email" type="email" maxlength="160">
            </div>
            <div class="form-row" id="ord-addr-wrap" style="display:none;">
                <label>Teslim Adresi</label>
                <textarea id="ord-address" rows="2" maxlength="500"></textarea>
            </div>
            <div class="form-row">
                <label>Not (opsiyonel)</label>
                <textarea id="ord-note" rows="2" maxlength="1000" placeholder="Az şekerli, soğan yok, vb."></textarea>
            </div>
        </div>

        <button class="btn" id="ord-submit" onclick="submitOrder()"><i class="fas fa-check"></i> Siparişi Gönder</button>
    </div>
</div>

<!-- RESERVATION MODAL -->
@php
    $rsvAdvance = max(1, (int) ($restaurant->reservation_advance_days ?? 30));
    $rsvMaxParty = $restaurant->maxReservationPartySize();
@endphp
<div class="modal" id="resv-modal">
    <div class="panel" style="position: relative;">
        <button class="x" onclick="closeResv()"><i class="fas fa-xmark"></i></button>
        <h3>Rezervasyon</h3>

        <div class="form-grid">
            <div class="form-row">
                <label>Tarih</label>
                <input id="rsv-date" type="date" min="{{ now()->toDateString() }}" max="{{ now()->addDays($rsvAdvance)->toDateString() }}">
            </div>
            <div class="form-row">
                <label>Kişi sayısı</label>
                <input id="rsv-party" type="number" min="1" max="{{ $rsvMaxParty }}" value="2">
            </div>
        </div>

        <div class="form-row">
            <label>Saat</label>
            <div id="rsv-slots" style="display: flex; flex-wrap: wrap; gap: 6px;">
                <span style="color: var(--muted); font-size: .85rem;">Tarih seçin</span>
            </div>
            <input type="hidden" id="rsv-time">
        </div>

        <div class="form-grid">
            <div class="form-row">
                <label>Ad Soyad</label>
                <input id="rsv-name" maxlength="120" autocomplete="name">
            </div>
            <div class="form-row">
                <label>Telefon</label>
                <input id="rsv-phone" maxlength="24" autocomplete="tel">
            </div>
        </div>
        <div class="form-row">
            <label>E-posta (opsiyonel)</label>
            <input id="rsv-email" type="email" maxlength="160">
        </div>
        <div class="form-row">
            <label>Not (opsiyonel)</label>
            <textarea id="rsv-note" rows="2" maxlength="2000" placeholder="Doğum günü, alerji, özel istek..."></textarea>
        </div>

        <button class="btn" id="rsv-submit" onclick="submitResv()"><i class="fas fa-check"></i> Rezervasyon Talebi Gönder</button>
    </div>
</div>

<!-- VARYANT SEÇİMİ -->
<div class="modal" id="var-modal">
    <div class="panel pick-var" style="position:relative">
        <button class="x" type="button" onclick="closeVarPick()"><i class="fas fa-xmark"></i></button>
        <h3 id="var-m-title">Seçenek</h3>
        <label style="font-size:.84rem;font-weight:600">Boyut / seçenek</label>
        <select id="var-m-select"></select>
        <div style="font-size:.85rem;color:var(--muted);margin-top:10px">Birim: <strong id="var-m-unit">—</strong></div>
        <button type="button" class="btn" style="margin-top:16px" onclick="confirmVarPick()"><i class="fas fa-plus"></i> Sepete ekle</button>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const SLUG    = @json($restaurant->slug);
const CSRF    = document.querySelector('meta[name=csrf-token]').content;
const TAX_R   = {{ (float) $restaurant->tax_rate }};
const SVC_R   = {{ (float) $restaurant->service_charge_rate }};
const DELIV_F = {{ (float) $restaurant->delivery_fee }};
const MIN_AMT = {{ (float) $restaurant->delivery_min_amount }};
const ACCEPT  = {
    takeaway: {{ $restaurant->accepts_takeaway ? 'true' : 'false' }},
    delivery: {{ $restaurant->accepts_delivery ? 'true' : 'false' }},
};
const URL_ORDER = @json(route('cekirdex.public.takeaway.place', $restaurant->slug));
const URL_AVAIL = @json(route('cekirdex.public.reservation.availability', $restaurant->slug));
const URL_RSV   = @json(route('cekirdex.public.reservation.store', $restaurant->slug));

let cart = new Map();
let orderType = ACCEPT.takeaway ? 'takeaway' : 'delivery';
let __pick = { pid: 0, pname: '', base: 0, vars: [] };

function lineKey(pid, vid) { return pid + '_' + (vid || 0); }

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2200);
}

// CATEGORY FILTER
function setCat(btn, cat) {
    document.querySelectorAll('.cats button').forEach(b => b.classList.remove('is-on'));
    btn.classList.add('is-on');
    document.querySelectorAll('.menu .item').forEach(it => {
        it.style.display = (cat === 'all' || it.dataset.cat === cat) ? '' : 'none';
    });
}

// CART (Map: key = pid_vid)
function fmtCartTL(v) { return v.toFixed(2).replace('.', ',') + ' ₺'; }

function onMenuAdd(btn) {
    const el = btn.closest('.item');
    if (!el || el.dataset.available !== '1') return;
    const pid = parseInt(el.dataset.pid, 10);
    const pname = el.dataset.pname;
    const base = parseFloat(el.dataset.base || '0');
    let vars = [];
    try { vars = JSON.parse(el.dataset.variants || '[]'); } catch (e) { vars = []; }
    if (!vars.length) {
        bumpLine(pid, null, '', pname, base);
        return;
    }
    __pick = { pid, pname, base, vars };
    document.getElementById('var-m-title').textContent = pname;
    const sel = document.getElementById('var-m-select');
    sel.innerHTML = vars.map(v => {
        const adj = parseFloat(v.adj) || 0;
        const unit = base + adj;
        const tag = adj === 0 ? '' : ' (+' + fmtCartTL(adj) + ' → ' + fmtCartTL(unit) + ')';
        return '<option value="' + v.id + '">' + String(v.name).replace(/</g,'&lt;') + tag + '</option>';
    }).join('');
    sel.onchange = updateVarPickUnit;
    updateVarPickUnit();
    document.getElementById('var-modal').classList.add('show');
}
function updateVarPickUnit() {
    const sel = document.getElementById('var-m-select');
    const vid = parseInt(sel.value, 10);
    const vdef = __pick.vars.find(x => parseInt(x.id, 10) === vid);
    const adj = vdef ? (parseFloat(vdef.adj) || 0) : 0;
    document.getElementById('var-m-unit').textContent = fmtCartTL(__pick.base + adj);
}
function closeVarPick() { document.getElementById('var-modal').classList.remove('show'); }
function confirmVarPick() {
    const sel = document.getElementById('var-m-select');
    const vid = parseInt(sel.value, 10);
    const vdef = __pick.vars.find(x => parseInt(x.id, 10) === vid);
    const vname = vdef ? String(vdef.name) : '';
    const adj = vdef ? (parseFloat(vdef.adj) || 0) : 0;
    bumpLine(__pick.pid, vid, vname, __pick.pname + ' · ' + vname, __pick.base + adj);
    closeVarPick();
}

function bumpLine(pid, variantId, variantLabel, displayName, price) {
    const k = lineKey(pid, variantId);
    const cur = cart.get(k) || {
        key: k, id: pid, variant_id: variantId || null, variant_label: variantLabel || '',
        name: displayName, price: price, qty: 0
    };
    cur.qty += 1;
    cart.set(k, cur);
    refreshCart();
    showToast(displayName + ' sepete eklendi');
}
function changeQty(key, delta) {
    const ex = cart.get(key);
    if (!ex) return;
    ex.qty += delta;
    if (ex.qty <= 0) cart.delete(key);
    refreshCart();
}
function refreshCart() {
    const arr = [...cart.values()];
    const count = arr.reduce((s, c) => s + c.qty, 0);
    const sub = arr.reduce((s, c) => s + c.qty * c.price, 0);
    document.getElementById('cart-count').textContent = count;
    document.getElementById('cart-total').textContent = sub.toFixed(2).replace('.', ',') + ' ₺';
    document.getElementById('cart-bar').classList.toggle('show', count > 0);

    // Modal listesi & özet
    const list = document.getElementById('cart-list');
    if (arr.length === 0) {
        list.innerHTML = '<div style="color:var(--muted);text-align:center;padding:18px 0;">Sepet boş — menüden ürün ekleyin</div>';
    } else {
        list.innerHTML = arr.map(c => {
            const kEsc = String(c.key).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
            return `
            <div class="li">
                <div class="nm">${escapeCartHtml(c.name)}</div>
                <div class="qty">
                    <button type="button" onclick="changeQty('${kEsc}',-1)">−</button>
                    <span>${c.qty}</span>
                    <button type="button" onclick="changeQty('${kEsc}', 1)">+</button>
                </div>
                <div class="pr">${(c.qty * c.price).toFixed(2).replace('.', ',')} ₺</div>
            </div>`;
        }).join('');
    }

    const tax = sub * TAX_R / 100;
    const svc = sub * SVC_R / 100;
    const fee = orderType === 'delivery' ? DELIV_F : 0;
    const tot = sub + tax + svc + fee;
    document.getElementById('cart-summary').innerHTML = `
        <div class="line"><span>Ara toplam</span><span>${sub.toFixed(2).replace('.', ',')} ₺</span></div>
        ${TAX_R > 0 ? `<div class="line"><span>KDV (%${TAX_R})</span><span>${tax.toFixed(2).replace('.', ',')} ₺</span></div>` : ''}
        ${SVC_R > 0 ? `<div class="line"><span>Servis (%${SVC_R})</span><span>${svc.toFixed(2).replace('.', ',')} ₺</span></div>` : ''}
        ${fee > 0 ? `<div class="line"><span>Teslim ücreti</span><span>${fee.toFixed(2).replace('.', ',')} ₺</span></div>` : ''}
        <div class="line tot"><span>Toplam</span><span>${tot.toFixed(2).replace('.', ',')} ₺</span></div>
    `;
}

function setOrderType(t) {
    orderType = t;
    const a = document.getElementById('seg-takeaway');
    const b = document.getElementById('seg-delivery');
    if (a && b) { a.classList.toggle('is-on', t === 'takeaway'); b.classList.toggle('is-on', t === 'delivery'); }
    document.getElementById('ord-addr-wrap').style.display = (t === 'delivery') ? '' : 'none';
    refreshCart();
}

function openOrderModal() {
    if (cart.size === 0) { showToast('Önce menüden ürün ekleyin'); return; }
    refreshCart();
    document.getElementById('order-modal').classList.add('show');
}
function closeOrder() { document.getElementById('order-modal').classList.remove('show'); }

async function submitOrder() {
    const arr = [...cart.values()];
    if (arr.length === 0) return;
    const name  = document.getElementById('ord-name').value.trim();
    const phone = document.getElementById('ord-phone').value.trim();
    const email = document.getElementById('ord-email').value.trim();
    const note  = document.getElementById('ord-note').value.trim();
    const address = document.getElementById('ord-address').value.trim();
    if (name.length < 2) return showToast('Adınızı yazın');
    if (phone.length < 8) return showToast('Geçerli bir telefon yazın');
    if (orderType === 'delivery' && address.length < 5) return showToast('Teslim adresi yazın');

    const sub = arr.reduce((s, c) => s + c.qty * c.price, 0);
    if (orderType === 'delivery' && MIN_AMT > 0 && sub < MIN_AMT) {
        return showToast('Min. sipariş tutarı: ' + MIN_AMT.toFixed(2).replace('.', ',') + ' ₺');
    }

    const btn = document.getElementById('ord-submit');
    btn.disabled = true; btn.textContent = 'Gönderiliyor...';
    try {
        const r = await fetch(URL_ORDER, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                order_type: orderType,
                contact_name: name, contact_phone: phone, contact_email: email || null,
                delivery_address: orderType === 'delivery' ? address : null,
                note: note || null,
                items: arr.map(c => {
                    const row = { product_id: c.id, quantity: c.qty };
                    if (c.variant_id) row.variant_id = c.variant_id;
                    return row;
                }),
            }),
        });
        const d = await r.json();
        if (!r.ok || !d.ok) { showToast(d.message || 'Sipariş gönderilemedi'); return; }
        cart = new Map(); refreshCart();
        window.location.href = d.tracking_url;
    } catch (e) {
        showToast('Bağlantı hatası');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Siparişi Gönder';
    }
}

// RESERVATION
function openReservationModal() {
    document.getElementById('resv-modal').classList.add('show');
    const d = document.getElementById('rsv-date');
    if (d && !d.value) {
        d.value = new Date().toISOString().slice(0, 10);
        loadSlots(d.value);
    }
}
function closeResv() { document.getElementById('resv-modal').classList.remove('show'); }

document.addEventListener('change', (e) => {
    if (e.target.id === 'rsv-date') loadSlots(e.target.value);
});

function partyReloadSlots() {
    const d = document.getElementById('rsv-date');
    if (d && d.value) loadSlots(d.value);
}
(function bindRsvParty() {
    const el = document.getElementById('rsv-party');
    if (!el) return;
    el.addEventListener('change', partyReloadSlots);
    el.addEventListener('input', function() {
        clearTimeout(window.__rsvPartyT);
        window.__rsvPartyT = setTimeout(partyReloadSlots, 400);
    });
})();

async function loadSlots(date) {
    const wrap = document.getElementById('rsv-slots');
    document.getElementById('rsv-time').value = '';
    wrap.innerHTML = '<span style="color:var(--muted)">Yükleniyor...</span>';
    const partyRaw = document.getElementById('rsv-party');
    const party = Math.max(1, parseInt(partyRaw && partyRaw.value ? partyRaw.value : '2', 10));
    try {
        const r = await fetch(URL_AVAIL + '?date=' + encodeURIComponent(date) + '&party_size=' + encodeURIComponent(party));
        const d = await r.json();
        if (!d.ok) { wrap.innerHTML = '<span style="color:#991b1b">'+(d.message||'Hata')+'</span>'; return; }
        if (!d.slots.length) { wrap.innerHTML = '<span style="color:var(--muted)">Bu güne uygun saat yok</span>'; return; }
        wrap.innerHTML = d.slots.map(s => `
            <button type="button"
                onclick="pickSlot('${s.iso}', this)"
                ${s.available ? '' : 'disabled'}
                style="padding:8px 12px;border:1px solid var(--line);background:${s.available ? 'var(--card)' : '#f3eee5'};border-radius:8px;font-weight:700;cursor:${s.available?'pointer':'not-allowed'};color:${s.available?'var(--text)':'var(--muted)'};font-size:.86rem;">
                ${s.time}
            </button>
        `).join('');
    } catch (e) {
        wrap.innerHTML = '<span style="color:#991b1b">Bağlantı hatası</span>';
    }
}
function pickSlot(iso, btn) {
    document.getElementById('rsv-time').value = iso;
    document.querySelectorAll('#rsv-slots button').forEach(b => {
        b.style.background = b.disabled ? '#f3eee5' : 'var(--card)';
        b.style.color = b.disabled ? 'var(--muted)' : 'var(--text)';
    });
    btn.style.background = 'var(--p)'; btn.style.color = '#fff';
}
async function submitResv() {
    const date  = document.getElementById('rsv-date').value;
    const time  = document.getElementById('rsv-time').value;
    const party = parseInt(document.getElementById('rsv-party').value || '0', 10);
    const name  = document.getElementById('rsv-name').value.trim();
    const phone = document.getElementById('rsv-phone').value.trim();
    const email = document.getElementById('rsv-email').value.trim();
    const note  = document.getElementById('rsv-note').value.trim();
    if (!date)  return showToast('Tarih seçin');
    if (!time)  return showToast('Saat seçin');
    if (!party || party < 1) return showToast('Kişi sayısı girin');
    if (name.length < 2) return showToast('Adınızı yazın');
    if (phone.length < 8) return showToast('Geçerli bir telefon yazın');

    const btn = document.getElementById('rsv-submit');
    btn.disabled = true; btn.textContent = 'Gönderiliyor...';
    try {
        const r = await fetch(URL_RSV, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                contact_name: name, contact_phone: phone, contact_email: email || null,
                reserved_for: time, party_size: party, note: note || null,
            }),
        });
        const d = await r.json();
        if (!r.ok || !d.ok) { showToast(d.message || 'Rezervasyon gönderilemedi'); return; }
        window.location.href = d.tracking_url;
    } catch (e) {
        showToast('Bağlantı hatası');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Rezervasyon Talebi Gönder';
    }
}

// Init
setOrderType(orderType);
function checkHashResv() {
    if (location.hash === '#rezervasyon') openReservationModal();
}
checkHashResv();
window.addEventListener('hashchange', checkHashResv);
function escapeCartHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
</body>
</html>
