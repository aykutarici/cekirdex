<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <title>{{ $restaurant->name }} · {{ $table->name }} · Çekirdex</title>
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="{{ $restaurant->primary_color ?: '#ff6b35' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/svg+xml" href="{{ asset('cekirdex/favicon.svg') }}">
    <link rel="manifest" href="{{ asset('cekirdex/manifest.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --p: {{ $restaurant->primary_color ?: '#ff6b35' }};
            --p-d: {{ $restaurant->secondary_color ?: '#d9531c' }};
            --bg: #fffaf3;
            --card: #ffffff;
            --text: #1c1933;
            --soft: #4a4566;
            --muted: #7d7995;
            --line: #efece6;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: var(--bg); color: var(--text); font-family: "Inter", system-ui, sans-serif; min-height: 100vh; -webkit-font-smoothing: antialiased; }
        body { padding-bottom: 100px; }
        a { color: var(--p-d); text-decoration: none; }
        img { max-width: 100%; display: block; }

        .top {
            background: linear-gradient(135deg, var(--p) 0%, var(--p-d) 100%);
            padding: 24px 18px 26px; color: #fff;
            border-radius: 0 0 20px 20px;
            position: relative;
        }
        .top .name { font-size: 1.3rem; font-weight: 800; }
        .top .meta { font-size: .85rem; opacity: 0.9; margin-top: 4px; }
        .top .table-pill { display: inline-block; margin-top: 8px; background: rgba(255,255,255,.18); padding: 4px 12px; border-radius: 99px; font-size: .82rem; font-weight: 600; }
        .top .auth-pill {
            position:absolute; top:18px; right:16px;
            display:inline-flex; align-items:center; gap:6px;
            background: rgba(255,255,255,.18); color:#fff;
            padding: 6px 12px; border-radius: 99px;
            font-size: .8rem; font-weight: 600;
            cursor: pointer; border:0;
        }
        .top .auth-pill .av { width:22px; height:22px; border-radius:50%; background:rgba(255,255,255,.3); display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; }

        .container { padding: 0 16px; }

        .quick-actions {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 8px; margin: 16px 0 4px;
        }
        .quick-actions button {
            background: #fff; border: 1px solid var(--line); border-radius: 12px;
            padding: 10px 6px; font-size: .76rem; font-weight: 600;
            color: var(--text); display: flex; flex-direction: column; align-items: center; gap: 4px;
            cursor: pointer; transition: all .2s; min-height: 64px; line-height: 1.15;
        }
        .quick-actions button:hover { transform: translateY(-2px); border-color: var(--p); }
        .quick-actions button:active { transform: translateY(0); }
        .quick-actions button i { font-size: 1.15rem; color: var(--p-d); }
        .quick-actions button.is-primary {
            background: linear-gradient(135deg, var(--p), var(--p-d));
            color: #fff; border-color: transparent;
            box-shadow: 0 6px 14px -4px rgba(0,0,0,.18);
        }
        .quick-actions button.is-primary i { color: #fff; }
        .quick-actions button.is-bill {
            background: #ecfdf5; border-color: #a7f3d0; color: #065f46;
        }
        .quick-actions button.is-bill i { color: #047857; }

        .cat-tabs {
            display: flex; gap: 8px; overflow-x: auto;
            padding: 14px 0;
            position: sticky; top: 0; z-index: 10; background: var(--bg);
            scrollbar-width: none;
        }
        .cat-tabs::-webkit-scrollbar { display: none; }
        .cat-tabs button {
            background: #fff; border: 1px solid var(--line); border-radius: 99px;
            padding: 8px 16px; font-size: .85rem; font-weight: 600;
            color: var(--soft); white-space: nowrap; cursor: pointer; transition: all .15s;
        }
        .cat-tabs button.is-active {
            background: linear-gradient(135deg, var(--p), var(--p-d));
            color: #fff; border-color: transparent;
            box-shadow: 0 6px 14px -4px rgba(0,0,0,.2);
        }

        .cat-section { padding: 14px 0 4px; }
        .cat-title { font-size: 1.05rem; font-weight: 800; margin-bottom: 10px; }

        .product {
            background: var(--card); border: 1px solid var(--line); border-radius: 14px;
            padding: 14px; margin-bottom: 10px;
            display: grid; grid-template-columns: 70px 1fr auto; gap: 14px; align-items: center;
            box-shadow: 0 4px 12px -6px rgba(0,0,0,.08);
        }
        .product .img {
            width: 70px; height: 70px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, color-mix(in srgb, var(--p) 25%, #fff), color-mix(in srgb, var(--p) 12%, #fff));
            color: var(--p-d); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; overflow: hidden;
        }
        .product .img img { width: 100%; height: 100%; object-fit: cover; }
        .product .name { font-weight: 700; font-size: .95rem; }
        .product .desc { color: var(--muted); font-size: .8rem; margin-top: 2px; line-height: 1.4; }
        .product .badges { margin-top: 4px; display: flex; gap: 4px; flex-wrap: wrap; }
        .product .badges span { font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: .04em; }
        .product .badges .pop { background: #fff3eb; color: #d9531c; }
        .product .badges .new { background: #dcfce7; color: #166534; }
        .product .price { font-weight: 800; color: var(--p-d); font-size: 1rem; white-space: nowrap; text-align: right; }
        .product .price small { display: block; color: var(--muted); text-decoration: line-through; font-size: .78rem; font-weight: 500; }
        .product .reactions { display:inline-flex; gap:8px; margin-top:4px; align-items:center; }
        .product .reactions .r { display:inline-flex; align-items:center; gap:3px; font-size:.74rem; color:var(--muted); font-weight:600; }
        .product .reactions .r.is-mine i { color:#ef4444; }
        .product .reactions .r.fav.is-mine i { color:#f59e0b; }
        .product .heart-btn { background:transparent; border:0; padding:0; color:#cbd5e1; cursor:pointer; font-size:1.05rem; transition:color .2s, transform .15s; }
        .product .heart-btn.is-mine { color:#ef4444; transform:scale(1.05); }
        .product .heart-btn:active { transform:scale(.85); }
        .product .add {
            margin-top: 6px;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff; border: 0;
            padding: 8px 0; width: 70px; border-radius: 8px; font-size: .82rem; font-weight: 700;
            cursor: pointer; box-shadow: 0 6px 14px -4px rgba(0,0,0,.2);
        }
        .product .add:disabled { opacity: 0.5; }
        .qty-stepper { display: inline-flex; align-items: center; background: #fff8f1; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; }
        .qty-stepper button { width: 28px; height: 28px; background: transparent; border: 0; font-weight: 700; cursor: pointer; color: var(--p-d); }
        .qty-stepper span { min-width: 22px; text-align: center; font-weight: 700; font-size: .9rem; }

        /* Sticky cart */
        .cart-bar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff;
            padding: 14px 18px;
            box-shadow: 0 -10px 28px -8px rgba(0,0,0,.25);
            display: none;
        }
        .cart-bar.is-visible { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
        .cart-bar .info { font-size: .9rem; }
        .cart-bar .info strong { display: block; font-size: 1rem; }
        .cart-bar button {
            background: #fff; color: var(--p-d); font-weight: 700; padding: 10px 18px;
            border: 0; border-radius: 11px; cursor: pointer; font-size: .92rem;
            display: inline-flex; align-items: center; gap: 6px;
        }

        /* Modal */
        .modal { display: none; position: fixed; inset: 0; background: rgba(28,25,51,.55); z-index: 100; align-items: flex-end; justify-content: center; }
        .modal.is-open { display: flex; }
        .modal-box {
            background: #fff; width: 100%; max-width: 500px;
            border-radius: 22px 22px 0 0;
            padding: 22px 20px 24px;
            max-height: 90vh; overflow-y: auto;
        }
        .modal-box h3 { font-size: 1.1rem; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; }
        .modal-box h3 button { background: transparent; border: 0; font-size: 1.4rem; cursor: pointer; color: var(--muted); }

        .cart-item {
            display: grid; grid-template-columns: 1fr auto; gap: 8px;
            padding: 10px 0; border-bottom: 1px solid var(--line);
        }
        .cart-item .nm { font-weight: 700; font-size: .92rem; }
        .cart-item .qty { color: var(--muted); font-size: .8rem; }
        .cart-item .pr { font-weight: 700; color: var(--p-d); }
        .totals { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--line); }
        .totals .row { display: flex; justify-content: space-between; padding: 4px 0; font-size: .92rem; }
        .totals .row.total { font-size: 1.1rem; font-weight: 800; padding-top: 10px; border-top: 1px solid var(--line); margin-top: 8px; }
        .totals .row.total span:last-child { color: var(--p-d); }

        .btn-go {
            width: 100%; margin-top: 16px; padding: 14px;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff;
            font-weight: 800; font-size: 1rem; border: 0; border-radius: 14px;
            cursor: pointer; box-shadow: 0 14px 32px -10px rgba(0,0,0,.3);
        }
        .btn-go[disabled] { opacity: .55; }

        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: 4px; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 10px 12px; font-size: .92rem; border: 1.5px solid var(--line); border-radius: 10px; background: #fff;
            font-family: inherit;
        }

        .ok-screen, .err-screen { padding: 30px 20px; text-align: center; }
        .ok-screen i { font-size: 3rem; color: #10b981; margin-bottom: 12px; }
        .err-screen i { font-size: 3rem; color: #ef4444; margin-bottom: 12px; }

        /* Aktif sipariş takibi (sticky widget) */
        .track-bar {
            position: fixed; left: 12px; right: 12px; bottom: 86px; z-index: 45;
            background: #fff; border: 1px solid var(--line); border-radius: 14px;
            padding: 12px 14px; box-shadow: 0 18px 40px -14px rgba(28,25,51,.18);
            display: none; gap: 12px; align-items: center;
            cursor: pointer;
        }
        .track-bar.is-visible { display: flex; }
        .track-bar.no-cart { bottom: 14px; }
        .track-bar .ic {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .track-bar .tx { flex: 1; }
        .track-bar .tx .tt { font-weight: 700; font-size: .92rem; color: var(--text); }
        .track-bar .tx .ts { color: var(--muted); font-size: .78rem; }
        .track-bar .pill { font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 99px; }
        .track-bar .pill.s-new       { background:#fff3eb; color:#d9531c; }
        .track-bar .pill.s-confirmed { background:#fef3c7; color:#92400e; }
        .track-bar .pill.s-preparing { background:#e0e7ff; color:#3730a3; }
        .track-bar .pill.s-ready     { background:#dcfce7; color:#166534; }
        .track-bar .pill.s-served    { background:#e0f2fe; color:#075985; }

        body.has-track { padding-bottom: 168px; }
        body.has-track-no-cart { padding-bottom: 96px; }

        /* Sipariş geçmişi modal */
        .order-list { display: flex; flex-direction: column; gap: 10px; max-height: 60vh; overflow-y: auto; }
        .order-card { border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; background: #fff; }
        .order-card .h { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .order-card .h strong { font-size: .92rem; }
        .order-card ul { list-style: none; padding: 0; margin: 0 0 8px; font-size: .85rem; color: var(--soft); }
        .order-card ul li { padding: 2px 0; display: flex; justify-content: space-between; }
        .order-card .ft { display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px dashed var(--line); font-weight: 700; }

        /* Hesap modalı */
        .bill-meter {
            background: linear-gradient(135deg, #fff7ed, #fef9c3);
            border: 1px solid #fde68a; border-radius: 14px;
            padding: 14px 16px; margin-bottom: 14px;
        }
        .bill-meter .bm-row { display: flex; justify-content: space-between; font-size: .92rem; margin: 4px 0; color: var(--soft); }
        .bill-meter .bm-row.total { font-weight: 800; font-size: 1.1rem; color: var(--text); padding-top: 8px; margin-top: 6px; border-top: 1px dashed rgba(0,0,0,.08); }
        .bill-meter .bm-row.paid { color: #047857; font-weight: 700; }
        .bill-meter .bm-row.remaining { color: #b91c1c; font-weight: 800; font-size: 1.05rem; }
        .bill-progress { height: 8px; background: #fff; border-radius: 99px; overflow: hidden; margin: 10px 0 4px; border: 1px solid rgba(0,0,0,.05); }
        .bill-progress span { display: block; height: 100%; background: linear-gradient(90deg, #10b981, #059669); transition: width .35s; }

        .pay-tabs { display: flex; gap: 6px; margin: 0 0 14px; padding: 4px; background: #f5f3ee; border-radius: 12px; }
        .pay-tabs button { flex: 1; padding: 9px 6px; font-size: .82rem; font-weight: 600; background: transparent; border: 0; color: var(--soft); border-radius: 9px; cursor: pointer; }
        .pay-tabs button.is-active { background: #fff; color: var(--text); box-shadow: 0 4px 10px -4px rgba(0,0,0,.1); }

        .pay-pane { display: none; }
        .pay-pane.is-active { display: block; }

        .bill-items { display: flex; flex-direction: column; gap: 8px; max-height: 36vh; overflow-y: auto; }
        .bill-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            background: #fff; border: 1px solid var(--line); border-radius: 12px;
        }
        .bill-item.is-paid { opacity: .55; }
        .bill-item .ck { width: 22px; height: 22px; flex-shrink: 0; accent-color: var(--p); cursor: pointer; }
        .bill-item .nm { flex: 1; font-size: .9rem; }
        .bill-item .nm small { color: var(--muted); display: block; }
        .bill-item .pr { font-weight: 700; color: var(--text); font-size: .9rem; white-space: nowrap; }
        .bill-item .qx { display: inline-flex; align-items: center; gap: 6px; }
        .bill-item .qx button { width: 26px; height: 26px; border-radius: 8px; background: var(--bg); border: 1px solid var(--line); font-weight: 700; color: var(--text); cursor: pointer; }
        .bill-item .qx span { min-width: 28px; text-align: center; font-weight: 700; }

        .pay-methods { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 12px 0; }
        .pay-methods label {
            border: 1.5px solid var(--line); border-radius: 12px; padding: 10px 12px;
            display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .85rem; font-weight: 600;
            background: #fff;
        }
        .pay-methods label.is-on { border-color: var(--p); background: #fff7f1; color: var(--p-d); }
        .pay-methods input { display: none; }

        .bill-summary {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; font-weight: 700;
        }
        .bill-summary span:last-child { color: var(--p-d); font-size: 1.1rem; font-weight: 800; }

        /* Ürün detay modalı */
        .pd-img { width: 100%; height: 180px; border-radius: 14px; overflow: hidden; margin-bottom: 14px;
                  background: linear-gradient(135deg, color-mix(in srgb, var(--p) 25%, #fff), color-mix(in srgb, var(--p) 12%, #fff));
                  display: flex; align-items: center; justify-content: center; color: var(--p-d); font-size: 3rem; }
        .pd-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .pd-name { font-size: 1.2rem; font-weight: 800; margin-bottom: 4px; }
        .pd-desc { color: var(--soft); font-size: .92rem; margin-bottom: 14px; }
        .pd-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 16px; }
        .pd-price { font-weight: 800; font-size: 1.3rem; color: var(--p-d); }

        @media (min-width: 768px) {
            body { max-width: 540px; margin: 0 auto; box-shadow: 0 0 60px rgba(0,0,0,.06); min-height: 100vh; }
        }
    </style>
</head>
<body>
    <div class="top">
        <button id="auth-pill" class="auth-pill" onclick="openAuthMenu()">
            @if($customer ?? false)
                <span class="av">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                <span id="auth-name">{{ $customer->name }}</span>
            @else
                <i class="fas fa-user"></i><span>Giriş yap</span>
            @endif
        </button>
        <div class="name">{{ $restaurant->name }}</div>
        <div class="meta">{{ $restaurant->address }}</div>
        <div class="table-pill"><i class="fas fa-chair"></i> {{ $table->name }}</div>
    </div>

    <div class="container">
        <div class="quick-actions">
            <button class="is-primary" onclick="callWaiter('waiter')"><i class="fas fa-bell-concierge"></i> Garsonu Çağır</button>
            <button class="is-bill" onclick="openBill()"><i class="fas fa-file-invoice-dollar"></i> Hesap</button>
            <button onclick="callWaiter('water')"><i class="fas fa-glass-water"></i> Su</button>
            <button onclick="callWaiter('napkin')"><i class="fas fa-tissue"></i> Peçete</button>
            <button onclick="callWaiter('ketchup')"><i class="fas fa-bottle-droplet"></i> Ketçap</button>
            <button onclick="callWaiter('mayo')"><i class="fas fa-bottle-droplet"></i> Mayonez</button>
            <button onclick="callWaiter('spice')"><i class="fas fa-pepper-hot"></i> Baharat</button>
            <button onclick="askExtra()"><i class="fas fa-circle-plus"></i> Diğer</button>
        </div>

        <div class="cat-tabs">
            <button class="is-active" data-cat="all">Tümü</button>
            @if(!empty($myFavs))
                <button data-cat="favs" style="background:#fff7ed;color:#d9531c;border-color:#fed7aa"><i class="fas fa-bookmark"></i> Favorilerim</button>
            @endif
            @foreach($categories as $c)
                @if(($products[$c->id] ?? collect())->isNotEmpty())
                    <button data-cat="cat-{{ $c->id }}">{{ $c->name }}</button>
                @endif
            @endforeach
        </div>

        <div class="cat-tabs" style="margin-top:6px">
            <button class="is-active diet-btn" data-diet="" style="font-size:.78rem">Diyet filtresi yok</button>
            <button class="diet-btn" data-diet="vegan" style="font-size:.78rem">🌿 Vegan</button>
            <button class="diet-btn" data-diet="vegetarian" style="font-size:.78rem">🥗 Vejetaryen</button>
            <button class="diet-btn" data-diet="halal" style="font-size:.78rem">☪ Helal</button>
            <button class="diet-btn" data-diet="!gluten" style="font-size:.78rem">🌾 Glutensiz</button>
            <button class="diet-btn" data-diet="!lactose" style="font-size:.78rem">🥛 Laktozsuz</button>
            <button class="diet-btn" data-diet="!nuts" style="font-size:.78rem">🥜 Fıstıksız</button>
        </div>

        @foreach($categories as $c)
            @php $list = $products[$c->id] ?? collect(); @endphp
            @if($list->isNotEmpty())
            <section class="cat-section" data-cat="cat-{{ $c->id }}">
                <h2 class="cat-title">{{ $c->name }}</h2>
                @foreach($list as $p)
                @php
                    $price = (float) $p->price;
                    $disc  = $p->discount_price ? (float) $p->discount_price : null;
                    $effective = $disc ?: $price;
                @endphp
                @php
                    $available = $p->isAvailable();
                    $allergens = is_array($p->allergens) ? $p->allergens : [];
                    $rating = $ratingAverages[$p->id] ?? null;
                    $varJson = $p->variants->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'adj' => (float) $v->price_adjust])->values();
                @endphp
                <div class="product" data-pid="{{ $p->id }}"
                     data-pname="{{ $p->name }}"
                     data-pdesc="{{ $p->description ?? '' }}"
                     data-pprice="{{ $effective }}"
                     data-pimg="{{ $p->image_url ?? '' }}"
                     data-fav="{{ in_array($p->id, $myFavs ?? []) ? '1' : '0' }}"
                     data-tags="{{ implode(',', $allergens) }}"
                     data-has-variants="{{ $varJson->isNotEmpty() ? '1' : '0' }}"
                     data-variants="{{ e($varJson->toJson(JSON_UNESCAPED_UNICODE)) }}"
                     style="{{ $available ? '' : 'opacity:.55;' }}">
                    <div class="img" onclick="openProduct({{ $p->id }})" style="cursor:pointer;position:relative">
                        @if($p->image_url)
                            <img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy">
                        @else
                            <i class="fas fa-utensils"></i>
                        @endif
                        @if(!$available)
                            <span style="position:absolute;top:6px;left:6px;background:#991b1b;color:#fff;font-size:.66rem;padding:3px 8px;border-radius:99px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Tükendi</span>
                        @endif
                    </div>
                    <div onclick="openProduct({{ $p->id }})" style="cursor:pointer">
                        <div class="name">{{ $p->name }}</div>
                        @if($p->description)<div class="desc">{{ Str::limit($p->description, 80) }}</div>@endif
                        @if($p->is_popular || $p->is_new || $rating)
                            <div class="badges">
                                @if($rating)
                                    <span class="pop" style="background:#fef3c7;color:#92400e"><i class="fas fa-star"></i> {{ number_format($rating['avg'], 1, ',', '.') }} ({{ $rating['count'] }})</span>
                                @endif
                                @if($p->is_popular)<span class="pop"><i class="fas fa-fire"></i> Popüler</span>@endif
                                @if($p->is_new)<span class="new">Yeni</span>@endif
                            </div>
                        @endif
                        @if(!empty($allergens))
                            <div style="margin-top:4px;display:flex;gap:3px;flex-wrap:wrap">
                                @foreach($allergens as $a)
                                    @php $info = \App\Cekirdex\Models\CekirdexProduct::ALLERGENS[$a] ?? null; @endphp
                                    @if($info)
                                        <span style="font-size:.7rem;padding:1px 7px;border-radius:99px;background:#fff3eb;color:#7c2d12">{{ $info[1] }} {{ $info[0] }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        @php
                            $lc = (int)($likeCounts[$p->id] ?? 0);
                            $rc = (int)($reviewCounts[$p->id] ?? 0);
                            $myL = in_array($p->id, $myLikes ?? []);
                            $myF = in_array($p->id, $myFavs ?? []);
                        @endphp
                        @if($lc > 0 || $rc > 0 || $myF || $myL)
                            <div class="reactions">
                                @if($lc > 0)<span class="r {{ $myL ? 'is-mine' : '' }}"><i class="fas fa-heart"></i> {{ $lc }}</span>@endif
                                @if($rc > 0)<span class="r"><i class="fas fa-comment"></i> {{ $rc }}</span>@endif
                                @if($myF)<span class="r fav is-mine"><i class="fas fa-bookmark"></i></span>@endif
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="price">
                            {{ number_format($effective, 2, ',', '.') }} ₺
                            @if($disc)<small>{{ number_format($price, 2, ',', '.') }} ₺</small>@endif
                        </div>
                        <div class="qctrl" data-pid="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $effective }}">
                            @if($available)
                                @if($varJson->isNotEmpty())
                                    <button type="button" class="add" onclick="openProduct({{ $p->id }})"><i class="fas fa-sliders"></i> Seçenek</button>
                                @else
                                    <button type="button" class="add" onclick="addItem(this)">+ Ekle</button>
                                @endif
                            @else
                                <button type="button" class="add" disabled style="opacity:.5;cursor:not-allowed">Tükendi</button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </section>
            @endif
        @endforeach

        @if($categories->isEmpty() || collect($products)->flatten()->isEmpty())
            <div style="text-align:center;padding:60px 20px;color:var(--muted)">
                <i class="fas fa-utensils" style="font-size:2rem;color:#fde9d6"></i>
                <p style="margin-top:10px">Bu restoran henüz menüsünü hazırlıyor.</p>
            </div>
        @endif
    </div>

    {{-- Cart bar --}}
    <div class="cart-bar" id="cart-bar">
        <div class="info">
            <strong id="cart-count">0 ürün</strong>
            <span id="cart-total">0,00 ₺</span>
        </div>
        <button onclick="openCart()"><i class="fas fa-cart-shopping"></i> Sepeti Gör</button>
    </div>

    {{-- Cart modal --}}
    <div class="modal" id="cart-modal">
        <div class="modal-box">
            <h3>Sepetiniz <button onclick="closeCart()"><i class="fas fa-times"></i></button></h3>
            <div id="cart-items"></div>
            <div class="totals">
                <div class="row"><span>Ara toplam</span><span id="t-subtotal">0,00 ₺</span></div>
                @if((float) $restaurant->tax_rate > 0)<div class="row"><span>KDV ({{ rtrim(rtrim(number_format((float)$restaurant->tax_rate, 2), '0'), '.') }}%)</span><span id="t-tax">0,00 ₺</span></div>@endif
                @if((float) $restaurant->service_charge_rate > 0)<div class="row"><span>Servis ({{ rtrim(rtrim(number_format((float)$restaurant->service_charge_rate, 2), '0'), '.') }}%)</span><span id="t-svc">0,00 ₺</span></div>@endif
                <div class="row total"><span>Toplam</span><span id="t-total">0,00 ₺</span></div>
            </div>

            <div class="form-group" style="margin-top:14px"><label>İsim (opsiyonel)</label><input type="text" id="g-name" maxlength="120"></div>
            <div class="form-group"><label>Sipariş notu (opsiyonel)</label><textarea id="g-note" rows="2" maxlength="1000"></textarea></div>

            <button class="btn-go" id="btn-go" onclick="placeOrder()"><i class="fas fa-paper-plane"></i> Siparişi Onayla</button>
        </div>
    </div>

    {{-- Order success modal --}}
    <div class="modal" id="ok-modal">
        <div class="modal-box">
            <div class="ok-screen">
                <i class="fas fa-circle-check"></i>
                <h3 style="display:block;text-align:center;margin-bottom:8px">Siparişiniz alındı!</h3>
                <p style="color:var(--soft)">Mutfağa iletildi. Sipariş No: <strong id="ok-no"></strong></p>
                <button class="btn-go" onclick="closeOk()" style="margin-top:18px">Tamam</button>
            </div>
        </div>
    </div>

    {{-- Aktif sipariş takip widget --}}
    <div class="track-bar" id="track-bar" onclick="openTrack()">
        <div class="ic"><i class="fas fa-receipt"></i></div>
        <div class="tx">
            <div class="tt"><span id="track-title">Siparişiniz hazırlanıyor</span></div>
            <div class="ts" id="track-sub">—</div>
        </div>
        <span class="pill" id="track-pill">—</span>
    </div>

    {{-- Sipariş geçmişi modal --}}
    <div class="modal" id="track-modal">
        <div class="modal-box">
            <h3>Aktif Siparişlerim <button onclick="closeTrack()"><i class="fas fa-times"></i></button></h3>
            <div class="order-list" id="order-list"><div style="text-align:center;color:var(--muted);padding:24px">Yükleniyor…</div></div>
        </div>
    </div>

    {{-- Ürün detay modal --}}
    <div class="modal" id="pd-modal">
        <div class="modal-box">
            <h3 style="margin-bottom:0"><span id="pd-title">Ürün</span> <button onclick="closePd()"><i class="fas fa-times"></i></button></h3>
            <div id="pd-body"></div>
        </div>
    </div>

    {{-- Müşteri Auth modal (Giriş / Kayıt) --}}
    <div class="modal" id="auth-modal">
        <div class="modal-box">
            <h3>Hesabım <button onclick="closeAuth()"><i class="fas fa-times"></i></button></h3>
            <div id="auth-tabs" style="display:flex;gap:6px;margin:8px 0 14px;background:#f5f3ee;padding:4px;border-radius:12px">
                <button id="auth-tab-login" class="is-active" onclick="setAuthTab('login')" style="flex:1;padding:9px;font-weight:600;border:0;border-radius:9px;background:#fff;cursor:pointer">Giriş Yap</button>
                <button id="auth-tab-register" onclick="setAuthTab('register')" style="flex:1;padding:9px;font-weight:600;border:0;border-radius:9px;background:transparent;cursor:pointer;color:var(--soft)">Kayıt Ol</button>
            </div>
            <div id="auth-error" style="display:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:.85rem"></div>

            <div id="auth-pane-login">
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" id="lo-phone" maxlength="24" placeholder="05XX XXX XX XX" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" id="lo-pass" maxlength="120" placeholder="Şifre" autocomplete="current-password">
                </div>
                <button class="btn-go" onclick="submitLogin()"><i class="fas fa-arrow-right-to-bracket"></i> Giriş Yap</button>
                <p style="text-align:center;color:var(--muted);font-size:.78rem;margin-top:10px">Hesabın yok mu? <a href="#" onclick="setAuthTab('register');return false">Kayıt ol</a></p>
            </div>

            <div id="auth-pane-register" style="display:none">
                <div class="form-group">
                    <label>Adınız</label>
                    <input type="text" id="rg-name" maxlength="120" placeholder="Adınız soyadınız" autocomplete="name">
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" id="rg-phone" maxlength="24" placeholder="05XX XXX XX XX" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label>E-posta (opsiyonel)</label>
                    <input type="email" id="rg-email" maxlength="160" placeholder="ornek@mail.com" autocomplete="email">
                </div>
                <div class="form-group">
                    <label>Şifre (en az 6 karakter)</label>
                    <input type="password" id="rg-pass" maxlength="120" placeholder="••••••" autocomplete="new-password">
                </div>
                <button class="btn-go" onclick="submitRegister()"><i class="fas fa-user-plus"></i> Kayıt Ol</button>
                <p style="text-align:center;color:var(--muted);font-size:.78rem;margin-top:10px">Zaten hesabın var mı? <a href="#" onclick="setAuthTab('login');return false">Giriş yap</a></p>
            </div>
        </div>
    </div>

    {{-- Profil menüsü modal --}}
    <div class="modal" id="profile-modal">
        <div class="modal-box">
            <h3>Hesabım <button onclick="closeProfile()"><i class="fas fa-times"></i></button></h3>
            <div style="background:linear-gradient(135deg,#fff7ed,#fefce8);border:1px solid #fde68a;border-radius:14px;padding:16px;text-align:center;margin-bottom:14px">
                <div style="width:54px;height:54px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--p-d));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;margin:0 auto 8px" id="prof-av">?</div>
                <div style="font-weight:800;font-size:1.05rem" id="prof-name">—</div>
                <div style="color:var(--muted);font-size:.85rem" id="prof-phone">—</div>
            </div>
            <button class="btn-go" style="background:#fff;color:var(--p-d);border:1.5px solid var(--p-d);box-shadow:none" onclick="submitLogout()"><i class="fas fa-right-from-bracket"></i> Çıkış yap</button>
        </div>
    </div>

    {{-- Hesap (Bill) modal --}}
    <div class="modal" id="bill-modal">
        <div class="modal-box">
            <h3>Hesap <button onclick="closeBill()"><i class="fas fa-times"></i></button></h3>
            <div id="bill-body">
                <div style="text-align:center;color:var(--muted);padding:24px"><i class="fas fa-spinner fa-spin"></i> Hesap yükleniyor…</div>
            </div>
        </div>
    </div>

<script>
const TAX_RATE = {{ (float) $restaurant->tax_rate }};
const SVC_RATE = {{ (float) $restaurant->service_charge_rate }};
const QR_TOKEN = @json($table->qr_token);
const RESTAURANT_ID = {{ (int) $restaurant->id }};
let CURRENT_USER = @json($customer ? ['id' => $customer->id, 'name' => $customer->name, 'phone' => $customer->phone] : null);
let HAS_ORDERED_HERE = @json((bool) ($hasOrderedHere ?? false));
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const cart = new Map(); // key: "pid_variantId" (variant 0 = yok)

function cartLineKey(pid, variantId) {
    const v = variantId && parseInt(variantId, 10) > 0 ? parseInt(variantId, 10) : 0;
    return pid + '_' + v;
}

function fmtTL(v) { return v.toFixed(2).replace('.', ',') + ' ₺'; }

function addItem(btn) {
    const wrap = btn.parentElement;
    const card = wrap.closest('.product');
    if (card && card.dataset.hasVariants === '1') {
        return openProduct(parseInt(wrap.dataset.pid, 10));
    }
    const pid  = parseInt(wrap.dataset.pid, 10);
    const nm   = wrap.dataset.name;
    const pr   = parseFloat(wrap.dataset.price);
    const key  = cartLineKey(pid, 0);
    const cur  = cart.get(key) || { key, productId: pid, variantId: null, name: nm, price: pr, qty: 0, note: '' };
    cur.qty += 1;
    cart.set(key, cur);
    renderStepper(wrap, key, cur.qty);
    refreshCart();
}
function changeQty(key, delta) {
    const cur = cart.get(key);
    if (!cur) return;
    cur.qty += delta;
    if (cur.qty <= 0) {
        cart.delete(key);
        const wrap = document.querySelector('.qctrl[data-pid="'+cur.productId+'"]');
        if (wrap && !wrap.closest('.product')?.dataset.hasVariants) {
            wrap.innerHTML = '<button type="button" class="add" onclick="addItem(this)">+ Ekle</button>';
        }
    } else {
        const wrap = document.querySelector('.qctrl[data-pid="'+cur.productId+'"]');
        if (wrap && !wrap.closest('.product')?.dataset.hasVariants) renderStepper(wrap, key, cur.qty);
    }
    refreshCart();
}
function renderStepper(wrap, key, q) {
    wrap.innerHTML = `
        <div class="qty-stepper">
            <button type="button" onclick="changeQty('${key}', -1)">−</button>
            <span>${q}</span>
            <button type="button" onclick="changeQty('${key}', +1)">+</button>
        </div>`;
}
function refreshCart() {
    let count = 0, sub = 0;
    cart.forEach(it => { count += it.qty; sub += it.qty * it.price; });
    const bar = document.getElementById('cart-bar');
    if (count > 0) {
        bar.classList.add('is-visible');
        document.getElementById('cart-count').textContent = count + ' ürün';
        document.getElementById('cart-total').textContent = fmtTL(sub);
    } else {
        bar.classList.remove('is-visible');
    }
}

function openCart() {
    if (cart.size === 0) return;
    const wrap = document.getElementById('cart-items');
    wrap.innerHTML = '';
    let sub = 0;
    cart.forEach(it => {
        sub += it.qty * it.price;
        wrap.innerHTML += `
            <div class="cart-item">
                <div>
                    <div class="nm">${escapeHtml(it.name)}</div>
                    <div class="qty">${it.qty} × ${fmtTL(it.price)}</div>
                </div>
                <div class="pr">${fmtTL(it.qty * it.price)}</div>
            </div>`;
    });
    const tax = sub * TAX_RATE / 100;
    const svc = sub * SVC_RATE / 100;
    document.getElementById('t-subtotal').textContent = fmtTL(sub);
    if (document.getElementById('t-tax')) document.getElementById('t-tax').textContent = fmtTL(tax);
    if (document.getElementById('t-svc')) document.getElementById('t-svc').textContent = fmtTL(svc);
    document.getElementById('t-total').textContent = fmtTL(sub + tax + svc);
    document.getElementById('cart-modal').classList.add('is-open');
}
function closeCart() { document.getElementById('cart-modal').classList.remove('is-open'); }
function closeOk()   { document.getElementById('ok-modal').classList.remove('is-open'); }

async function placeOrder() {
    const btn = document.getElementById('btn-go');
    btn.disabled = true; btn.textContent = 'Gönderiliyor…';

    const items = [];
    cart.forEach(it => {
        const payload = { product_id: it.productId, quantity: it.qty };
        if (it.variantId) payload.variant_id = it.variantId;
        if (it.note) payload.note = it.note;
        items.push(payload);
    });

    try {
        const res = await fetch('/m/' + QR_TOKEN + '/order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                items,
                guest_name: document.getElementById('g-name').value || null,
                note: document.getElementById('g-note').value || null,
            }),
        });
        const data = await res.json();
        if (!data.ok) {
            alert(data.message || 'Bir hata oluştu.'); btn.disabled = false; btn.textContent = '✓ Siparişi Onayla'; return;
        }
        cart.clear();
        document.querySelectorAll('.qctrl').forEach(w => {
            const card = w.closest('.product');
            if (card && card.dataset.hasVariants === '1') {
                w.innerHTML = '<button type="button" class="add" onclick="openProduct('+parseInt(card.dataset.pid,10)+')"><i class="fas fa-sliders"></i> Seçenek</button>';
            } else {
                w.innerHTML = '<button type="button" class="add" onclick="addItem(this)">+ Ekle</button>';
            }
        });
        refreshCart();
        closeCart();
        document.getElementById('ok-no').textContent = data.order_number;
        document.getElementById('ok-modal').classList.add('is-open');

        if (data.order_id) saveOrderId(data.order_id);
        pollOrders(true);
    } catch (e) {
        alert('Bağlantı hatası. Tekrar deneyin.');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Siparişi Onayla';
    }
}

/* ====== Sipariş takibi ====== */
const STORE_KEY = 'cekirdex.orders.' + QR_TOKEN;
function getOrderIds() {
    try { return JSON.parse(localStorage.getItem(STORE_KEY) || '[]'); } catch (e) { return []; }
}
function saveOrderId(id) {
    const list = getOrderIds();
    if (!list.includes(id)) list.unshift(id);
    localStorage.setItem(STORE_KEY, JSON.stringify(list.slice(0, 20)));
}
function statusLabel(s) {
    return ({new:'Sipariş alındı', confirmed:'Onaylandı', preparing:'Hazırlanıyor', ready:'Hazır', served:'Servis edildi', closed:'Kapandı', cancelled:'İptal'})[s] || s;
}
function statusClass(s) { return 's-' + s; }
let lastOrders = [];
async function pollOrders(force) {
    const ids = getOrderIds();
    if (ids.length === 0) { hideTrack(); return; }
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/my-orders', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const d = await res.json();
        const orders = d.orders || [];
        lastOrders = orders;

        const active = orders.filter(o => !['served','closed','cancelled'].includes(o.status));
        if (active.length === 0) { hideTrack(); return; }

        // Takip bar — en yeni aktif siparişi göster
        const o = active[0];
        document.getElementById('track-title').textContent = '#' + o.order_number;
        document.getElementById('track-sub').textContent = (active.length>1?active.length+' aktif sipariş · ':'') + o.minutes_ago + ' dk önce · ' + (o.total.toFixed(2).replace('.',',')) + ' ₺';
        const pill = document.getElementById('track-pill');
        pill.textContent = statusLabel(o.status);
        pill.className = 'pill ' + statusClass(o.status);
        showTrack();
    } catch (e) {}
}
function showTrack() {
    const b = document.getElementById('track-bar');
    b.classList.add('is-visible');
    document.body.classList.add('has-track');
    if (!document.getElementById('cart-bar').classList.contains('is-visible')) {
        b.classList.add('no-cart');
        document.body.classList.add('has-track-no-cart');
    } else {
        b.classList.remove('no-cart');
        document.body.classList.remove('has-track-no-cart');
    }
}
function hideTrack() {
    document.getElementById('track-bar').classList.remove('is-visible');
    document.body.classList.remove('has-track','has-track-no-cart');
}
function openTrack() {
    const wrap = document.getElementById('order-list');
    if (lastOrders.length === 0) {
        wrap.innerHTML = '<div style="text-align:center;color:var(--muted);padding:24px">Aktif sipariş yok.</div>';
    } else {
        wrap.innerHTML = lastOrders.map(o => {
            const items = (o.items || []).map(it => '<li><span>'+it.quantity+' × '+escapeHtml(it.name)+'</span><span>'+(Number(it.subtotal).toFixed(2).replace('.',','))+' ₺</span></li>').join('');
            return `
            <div class="order-card">
                <div class="h"><strong>#${o.order_number}</strong><span class="pill ${statusClass(o.status)}">${statusLabel(o.status)}</span></div>
                <ul>${items}</ul>
                <div class="ft"><span>Toplam</span><span>${o.total.toFixed(2).replace('.',',')} ₺</span></div>
            </div>`;
        }).join('');
    }
    document.getElementById('track-modal').classList.add('is-open');
}
function closeTrack() { document.getElementById('track-modal').classList.remove('is-open'); }
function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

/* ====== Ürün detay modal ====== */
function pdUpdateUnitPrice() {
    const base = window.__pdBasePrice || 0;
    const sel = document.getElementById('pd-variant');
    let adj = 0;
    if (sel && sel.value) {
        const v = (window.__pdVariants || []).find(x => String(x.id) === String(sel.value));
        if (v) adj = parseFloat(v.adj) || 0;
    }
    const unit = Math.round((base + adj) * 100) / 100;
    const el = document.getElementById('pd-price');
    if (el) el.textContent = unit.toFixed(2).replace('.',',') + ' ₺';
    return unit;
}
function openProduct(pid) {
    const card = document.querySelector('.product[data-pid="'+pid+'"]');
    if (!card) return;
    const name  = card.dataset.pname;
    const desc  = card.dataset.pdesc || '';
    const price = parseFloat(card.dataset.pprice);
    const img   = card.dataset.pimg;
    let variants = [];
    try { variants = JSON.parse(card.dataset.variants || '[]'); } catch (e) { variants = []; }
    window.__pdPid = pid;
    window.__pdBasePrice = price;
    window.__pdVariants = variants;
    let variantHtml = '';
    if (variants.length > 0) {
        variantHtml = `<div class="form-group" style="margin-bottom:12px">
            <label>Seçenek</label>
            <select id="pd-variant" onchange="pdUpdateUnitPrice()" style="width:100%;padding:10px;border:1.5px solid var(--line);border-radius:10px;font-size:.94rem">
                ${variants.map(v => `<option value="${v.id}">${escapeHtml(v.name)}${v.adj > 0 ? ' (+'+v.adj.toFixed(2).replace('.',',')+' ₺)' : (v.adj < 0 ? ' ('+v.adj.toFixed(2).replace('.',',')+' ₺)' : '')}</option>`).join('')}
            </select>
        </div>`;
    }
    document.getElementById('pd-title').textContent = name;
    document.getElementById('pd-body').innerHTML = `
        <div class="pd-img">${img ? `<img src="${img}" alt="">` : '<i class="fas fa-utensils"></i>'}</div>
        ${desc ? `<div class="pd-desc">${escapeHtml(desc)}</div>` : ''}
        ${variantHtml}
        <div class="form-group">
            <label>Not (örn. baharatsız, ekstra peynir…)</label>
            <textarea id="pd-note" rows="2" maxlength="200"></textarea>
        </div>
        <div class="pd-row">
            <div class="qty-stepper" style="background:#fff;border:1.5px solid var(--line)">
                <button type="button" onclick="pdQty(-1)" style="width:38px;height:38px;font-size:1.1rem">−</button>
                <span id="pd-qty" style="min-width:32px;font-size:1rem">1</span>
                <button type="button" onclick="pdQty(+1)" style="width:38px;height:38px;font-size:1.1rem">+</button>
            </div>
            <div class="pd-price" id="pd-price">${(price).toFixed(2).replace('.',',')} ₺</div>
        </div>
        <button type="button" class="btn-go" onclick="pdAdd(${pid})"><i class="fas fa-plus"></i> Sepete Ekle</button>
        <div id="pd-engagement"><div style="text-align:center;color:var(--muted);padding:14px;font-size:.82rem"><i class="fas fa-spinner fa-spin"></i> Yorumlar yükleniyor…</div></div>
    `;
    document.getElementById('pd-modal').classList.add('is-open');
    if (variants.length > 0) pdUpdateUnitPrice();
    pdLoadEngagement(pid);
}
function closePd() { document.getElementById('pd-modal').classList.remove('is-open'); }
let pdQ = 1;
function pdQty(delta) {
    pdQ = Math.max(1, pdQ + delta);
    document.getElementById('pd-qty').textContent = pdQ;
    pdUpdateUnitPrice();
}
function pdAdd(pid) {
    window.__pdPid = pid;
    const card = document.querySelector('.product[data-pid="'+pid+'"]');
    const baseName = card.dataset.pname;
    const sel = document.getElementById('pd-variant');
    const variantId = sel && sel.value ? parseInt(sel.value, 10) : null;
    let dispName = baseName;
    if (variantId && window.__pdVariants) {
        const v = window.__pdVariants.find(x => x.id === variantId);
        if (v) dispName = baseName + ' · ' + v.name;
    }
    const unitPrice = pdUpdateUnitPrice();
    const note = (document.getElementById('pd-note')?.value || '').trim();
    const key = cartLineKey(pid, variantId);
    const cur = cart.get(key) || { key, productId: pid, variantId: variantId || null, name: dispName, price: unitPrice, qty: 0, note: '' };
    cur.price = unitPrice;
    cur.name = dispName;
    cur.qty += pdQ;
    if (note) cur.note = note;
    cart.set(key, cur);
    const wrap = document.querySelector('.qctrl[data-pid="'+pid+'"]');
    if (wrap && card.dataset.hasVariants !== '1') renderStepper(wrap, key, cur.qty);
    pdQ = 1;
    refreshCart();
    closePd();
}

async function callWaiter(type, message) {
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/call', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ call_type: type, message: message || null }),
        });
        const data = await res.json();
        showToast(data.ok ? (data.message || 'İletildi.') : (data.message || 'Çağrı gönderilemedi.'), data.ok);
    } catch (e) { showToast('Bağlantı hatası.', false); }
}
function askExtra() {
    const txt = prompt('Garsona iletmek istediğin notu yaz:');
    if (txt && txt.trim()) callWaiter('custom', txt.trim());
}
function showToast(msg, ok) {
    let t = document.getElementById('toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast';
        t.style.cssText = 'position:fixed;left:50%;top:24px;transform:translateX(-50%);z-index:200;background:#1c1933;color:#fff;padding:12px 20px;border-radius:99px;font-weight:600;font-size:.92rem;box-shadow:0 14px 32px -10px rgba(0,0,0,.45);transition:opacity .2s;opacity:0;max-width:90%;text-align:center';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = ok === false ? '#991b1b' : '#1c1933';
    t.style.opacity = '1';
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => { t.style.opacity = '0'; }, 2400);
}

// Kategori + diyet tab'leri (iki bağımsız grup)
let activeCat = 'all';
let activeDiet = '';

function applyFilters() {
    document.querySelectorAll('.product').forEach(p => {
        const tags = (p.dataset.tags || '').split(',').filter(Boolean);
        const isFav = p.dataset.fav === '1';
        let show = true;
        if (activeCat === 'favs')      show = isFav;
        else if (activeCat !== 'all')  show = (p.closest('.cat-section')?.dataset.cat === activeCat);

        if (show && activeDiet) {
            if (activeDiet.startsWith('!')) {
                show = !tags.includes(activeDiet.slice(1));
            } else {
                show = tags.includes(activeDiet);
            }
        }
        p.style.display = show ? '' : 'none';
    });
    document.querySelectorAll('.cat-section').forEach(s => {
        const visible = s.querySelectorAll('.product:not([style*="display: none"])').length;
        s.style.display = visible > 0 ? '' : 'none';
    });
}

document.querySelectorAll('.cat-tabs button:not(.diet-btn)').forEach(b => {
    b.addEventListener('click', () => {
        document.querySelectorAll('.cat-tabs button:not(.diet-btn)').forEach(x => x.classList.remove('is-active'));
        b.classList.add('is-active');
        activeCat = b.dataset.cat || 'all';
        applyFilters();
    });
});
document.querySelectorAll('.cat-tabs button.diet-btn').forEach(b => {
    b.addEventListener('click', () => {
        document.querySelectorAll('.cat-tabs button.diet-btn').forEach(x => x.classList.remove('is-active'));
        b.classList.add('is-active');
        activeDiet = b.dataset.diet || '';
        applyFilters();
    });
});

// ─────────────────────────────────────────────────────────────────
// HESAP (BILL)
// ─────────────────────────────────────────────────────────────────
let billState = {
    bill: null,
    tab: 'full',          // full | items | equal | amount
    selectedItems: {},    // {order_item_id: qty}
    parts: 2,
    partIndex: 1,
    customAmount: 0,
    method: 'online_card',
    payerName: '',
    pollTimer: null,
};

function openBill() {
    document.getElementById('bill-modal').classList.add('is-open');
    fetchBill(true);
    if (billState.pollTimer) clearInterval(billState.pollTimer);
    billState.pollTimer = setInterval(() => fetchBill(false), 8000);
}
function closeBill() {
    document.getElementById('bill-modal').classList.remove('is-open');
    if (billState.pollTimer) { clearInterval(billState.pollTimer); billState.pollTimer = null; }
}

async function fetchBill(reset) {
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/bill', { headers: { 'Accept': 'application/json' }});
        const data = await res.json();
        if (!data.ok) throw new Error('failed');
        billState.bill = data.bill;
        if (reset) {
            billState.tab = 'full';
            billState.selectedItems = {};
            billState.customAmount = 0;
            billState.method = 'online_card';
            billState.parts = 2;
            billState.partIndex = 1;
        }
        renderBill();
    } catch (e) {
        document.getElementById('bill-body').innerHTML =
            '<div style="text-align:center;color:#b91c1c;padding:24px"><i class="fas fa-circle-exclamation"></i> Hesap yüklenemedi.</div>';
    }
}

function renderBill() {
    const b = billState.bill;
    if (!b) return;
    const root = document.getElementById('bill-body');
    if (!b.has_open_orders || b.total <= 0) {
        root.innerHTML = `
            <div style="text-align:center;padding:30px 12px">
                <i class="fas fa-mug-saucer" style="font-size:2.4rem;color:var(--p-d);margin-bottom:10px"></i>
                <div style="font-weight:700;font-size:1.05rem">Bu masada açık adisyon yok.</div>
                <div style="color:var(--muted);margin-top:6px">Sipariş verdikten sonra hesabınız buradan görüntülenebilir.</div>
            </div>`;
        return;
    }
    const remaining = b.remaining;
    const paidPct = b.total > 0 ? Math.min(100, (b.paid / b.total) * 100) : 0;
    const isFullyPaid = remaining < 0.01;

    root.innerHTML = `
        <div class="bill-meter">
            <div class="bm-row"><span>Ara toplam</span><span>${fmtTL(b.subtotal)}</span></div>
            ${b.tax > 0 ? `<div class="bm-row"><span>KDV (%${b.tax_rate})</span><span>${fmtTL(b.tax)}</span></div>` : ''}
            ${b.service_charge > 0 ? `<div class="bm-row"><span>Servis (%${b.service_charge_rate})</span><span>${fmtTL(b.service_charge)}</span></div>` : ''}
            <div class="bm-row total"><span>Toplam</span><span>${fmtTL(b.total)}</span></div>
            <div class="bill-progress"><span style="width:${paidPct}%"></span></div>
            <div class="bm-row paid"><span><i class="fas fa-check"></i> Ödenen</span><span>${fmtTL(b.paid)}</span></div>
            <div class="bm-row remaining"><span><i class="fas fa-clock"></i> Kalan</span><span>${fmtTL(remaining)}</span></div>
        </div>

        ${isFullyPaid ? `
            <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:14px;border-radius:12px;text-align:center;font-weight:700">
                <i class="fas fa-circle-check"></i> Hesap tamamen ödendi.<br>
                <small style="font-weight:500;color:#15803d">Garson masayı kapattığında onay alacaksınız.</small>
            </div>
        ` : `
            <div style="display:flex;gap:8px;margin-bottom:12px">
                <button class="btn-go" style="flex:1;background:#fff;color:var(--p-d);border:1.5px solid var(--p-d);box-shadow:none;font-size:.92rem" onclick="callWaiter('check'); closeBill();">
                    <i class="fas fa-bell-concierge"></i> Ödemek için garson çağır
                </button>
            </div>
            <div style="text-align:center;color:var(--muted);font-size:.82rem;margin-bottom:12px">— veya sistemden ödeyin —</div>

            <div class="pay-tabs">
                <button class="${billState.tab==='full'?'is-active':''}" onclick="setBillTab('full')">Tümü</button>
                <button class="${billState.tab==='items'?'is-active':''}" onclick="setBillTab('items')">Ürün Seç</button>
                <button class="${billState.tab==='equal'?'is-active':''}" onclick="setBillTab('equal')">Eşit Böl</button>
                <button class="${billState.tab==='amount'?'is-active':''}" onclick="setBillTab('amount')">Tutar Gir</button>
            </div>

            <div class="pay-pane ${billState.tab==='full'?'is-active':''}" id="pp-full">
                <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:14px;text-align:center">
                    <div style="font-size:.85rem;color:var(--soft)">Şimdi ödemek istediğiniz tutar:</div>
                    <div style="font-size:1.6rem;font-weight:800;color:var(--p-d);margin-top:6px">${fmtTL(remaining)}</div>
                </div>
            </div>

            <div class="pay-pane ${billState.tab==='items'?'is-active':''}" id="pp-items">
                <div style="font-size:.85rem;color:var(--soft);margin-bottom:8px">Ödemek istediğiniz ürünleri seçin:</div>
                <div class="bill-items">${renderBillItems(b)}</div>
            </div>

            <div class="pay-pane ${billState.tab==='equal'?'is-active':''}" id="pp-equal">
                <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:14px">
                    <div style="font-size:.85rem;color:var(--soft);margin-bottom:8px">Hesabı kaç kişi paylaşıyor?</div>
                    <div style="display:flex;gap:8px;align-items:center;justify-content:center;margin:8px 0">
                        <button onclick="changeParts(-1)" style="width:38px;height:38px;border-radius:10px;border:1.5px solid var(--line);background:#fff;font-weight:700">−</button>
                        <span id="bm-parts" style="font-size:1.4rem;font-weight:800;min-width:40px;text-align:center">${billState.parts}</span>
                        <button onclick="changeParts(+1)" style="width:38px;height:38px;border-radius:10px;border:1.5px solid var(--line);background:#fff;font-weight:700">+</button>
                    </div>
                    <div style="font-size:.85rem;color:var(--soft);margin-top:10px">Pay başına:</div>
                    <div style="font-size:1.5rem;font-weight:800;color:var(--p-d);text-align:center">${fmtTL(b.total / billState.parts)}</div>
                    <div style="font-size:.78rem;color:var(--muted);text-align:center;margin-top:6px">Sizin payınız: ${billState.partIndex}/${billState.parts}</div>
                </div>
            </div>

            <div class="pay-pane ${billState.tab==='amount'?'is-active':''}" id="pp-amount">
                <div class="form-group">
                    <label>Ödemek istediğiniz tutar (₺)</label>
                    <input type="number" id="bm-amount" min="0.01" step="0.01" max="${remaining}" value="${(billState.customAmount || remaining).toFixed(2)}" oninput="onCustomAmount(this.value)" style="font-size:1.2rem;font-weight:700">
                    <div style="font-size:.78rem;color:var(--muted);margin-top:4px">En fazla ${fmtTL(remaining)} ödeyebilirsiniz.</div>
                </div>
            </div>

            <div class="form-group" style="margin-top:8px">
                <label>İsim (isteğe bağlı, kalan ödeyenler görsün diye)</label>
                <input type="text" id="bm-name" maxlength="120" placeholder="Adınız" oninput="billState.payerName=this.value">
            </div>

            <div style="font-size:.85rem;color:var(--soft);margin:14px 0 6px;font-weight:600">Ödeme yöntemi</div>
            <div class="pay-methods">
                <label class="${billState.method==='online_card'?'is-on':''}">
                    <input type="radio" name="bm-method" value="online_card" onchange="billState.method='online_card';renderBill()">
                    <i class="fas fa-credit-card"></i> Kredi Kartı
                </label>
                <label class="${billState.method==='online_apple_pay'?'is-on':''}">
                    <input type="radio" name="bm-method" value="online_apple_pay" onchange="billState.method='online_apple_pay';renderBill()">
                    <i class="fab fa-apple-pay"></i> Apple Pay
                </label>
                <label class="${billState.method==='online_google_pay'?'is-on':''}">
                    <input type="radio" name="bm-method" value="online_google_pay" onchange="billState.method='online_google_pay';renderBill()">
                    <i class="fab fa-google-pay"></i> Google Pay
                </label>
                <label class="${billState.method==='qr'?'is-on':''}">
                    <input type="radio" name="bm-method" value="qr" onchange="billState.method='qr';renderBill()">
                    <i class="fas fa-qrcode"></i> TR Karekod
                </label>
            </div>

            <div class="bill-summary">
                <span>Şu an ödeyeceksiniz</span>
                <span id="bm-amount-display">${fmtTL(currentBillAmount())}</span>
            </div>
            <button class="btn-go" id="bm-pay-btn" onclick="payBill()">
                <i class="fas fa-lock"></i> ${fmtTL(currentBillAmount())} Öde
            </button>
            <div style="font-size:.72rem;color:var(--muted);text-align:center;margin-top:8px">
                Ödeme demo modundadır. Gerçek banka entegrasyonu yakında.
            </div>
        `}
    `;
}

function renderBillItems(b) {
    return b.items.map(it => {
        const remainingQty = it.quantity - it.paid_quantity;
        const sel = billState.selectedItems[it.id] || 0;
        if (remainingQty <= 0) {
            return `<div class="bill-item is-paid">
                <i class="fas fa-check" style="color:#10b981;width:22px;text-align:center"></i>
                <div class="nm">${escapeHtml(it.name)} × ${it.quantity}<small>Tamamen ödendi</small></div>
                <div class="pr">${fmtTL(it.subtotal)}</div>
            </div>`;
        }
        return `<div class="bill-item">
            <input type="checkbox" class="ck" ${sel>0?'checked':''} onchange="toggleItem(${it.id}, ${remainingQty})">
            <div class="nm">${escapeHtml(it.name)}
                <small>${it.note ? escapeHtml(it.note) + ' • ' : ''}Toplam ${it.quantity} adet${it.paid_quantity > 0 ? ` (${it.paid_quantity} ödendi)` : ''}</small>
            </div>
            ${sel > 0 ? `<div class="qx">
                <button onclick="changeItemQty(${it.id}, -1, ${remainingQty})">−</button>
                <span>${sel}</span>
                <button onclick="changeItemQty(${it.id}, +1, ${remainingQty})">+</button>
            </div>` : ''}
            <div class="pr">${fmtTL(it.unit_price)}</div>
        </div>`;
    }).join('');
}

function setBillTab(tab) { billState.tab = tab; renderBill(); }

function toggleItem(id, maxQty) {
    if (billState.selectedItems[id]) delete billState.selectedItems[id];
    else billState.selectedItems[id] = 1;
    renderBill();
}
function changeItemQty(id, delta, maxQty) {
    const cur = billState.selectedItems[id] || 0;
    const next = Math.max(0, Math.min(maxQty, cur + delta));
    if (next === 0) delete billState.selectedItems[id];
    else billState.selectedItems[id] = next;
    renderBill();
}
function changeParts(delta) {
    billState.parts = Math.max(2, Math.min(20, billState.parts + delta));
    if (billState.partIndex > billState.parts) billState.partIndex = billState.parts;
    renderBill();
}
function onCustomAmount(v) {
    billState.customAmount = parseFloat(v) || 0;
    document.getElementById('bm-amount-display').textContent = fmtTL(currentBillAmount());
    const btn = document.getElementById('bm-pay-btn');
    if (btn) btn.innerHTML = '<i class="fas fa-lock"></i> ' + fmtTL(currentBillAmount()) + ' Öde';
}

function currentBillAmount() {
    const b = billState.bill; if (!b) return 0;
    if (billState.tab === 'full') return b.remaining;
    if (billState.tab === 'amount') return Math.min(b.remaining, billState.customAmount || 0);
    if (billState.tab === 'equal') return Math.min(b.remaining, b.total / billState.parts);
    if (billState.tab === 'items') {
        const subtotalRatio = b.subtotal > 0 ? (b.total / b.subtotal) : 1;
        let net = 0;
        for (const it of b.items) {
            const q = billState.selectedItems[it.id] || 0;
            net += q * it.unit_price;
        }
        return Math.min(b.remaining, net * subtotalRatio);
    }
    return 0;
}

async function payBill() {
    const b = billState.bill; if (!b) return;
    const btn = document.getElementById('bm-pay-btn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ödeme yapılıyor...'; }

    const payload = {
        mode: billState.tab,
        method: billState.method,
        payer_name: billState.payerName || null,
    };
    if (billState.tab === 'amount') payload.amount = billState.customAmount;
    if (billState.tab === 'equal')  { payload.split_parts = billState.parts; payload.split_index = billState.partIndex; }
    if (billState.tab === 'items')  {
        payload.items = Object.entries(billState.selectedItems).map(([id, qty]) => ({ order_item_id: parseInt(id), quantity: qty }));
        if (payload.items.length === 0) {
            showToast('En az bir ürün seçin.', false);
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Öde'; }
            return;
        }
    }
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/bill/pay', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.message || 'Ödeme başarısız');
        showToast(data.message || 'Ödeme alındı!');
        billState.bill = data.bill;
        billState.selectedItems = {};
        billState.customAmount = 0;
        renderBill();
    } catch (e) {
        showToast(e.message || 'Ödeme yapılamadı.', false);
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Öde'; }
    }
}

// Sipariş takibi başlat
pollOrders(true);
setInterval(pollOrders, 12000);
document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') pollOrders(true); });

// ─────────────────────────────────────────────────────────────────
// MÜŞTERİ AUTH (Giriş / Kayıt / Profil)
// ─────────────────────────────────────────────────────────────────
function openAuthMenu() {
    if (CURRENT_USER) openProfile();
    else openAuth('login');
}
function openAuth(tab) {
    setAuthTab(tab || 'login');
    document.getElementById('auth-error').style.display = 'none';
    document.getElementById('auth-modal').classList.add('is-open');
}
function closeAuth() { document.getElementById('auth-modal').classList.remove('is-open'); }
function setAuthTab(tab) {
    document.getElementById('auth-tab-login').style.background    = tab === 'login'    ? '#fff' : 'transparent';
    document.getElementById('auth-tab-login').style.color         = tab === 'login'    ? 'var(--text)' : 'var(--soft)';
    document.getElementById('auth-tab-register').style.background = tab === 'register' ? '#fff' : 'transparent';
    document.getElementById('auth-tab-register').style.color      = tab === 'register' ? 'var(--text)' : 'var(--soft)';
    document.getElementById('auth-pane-login').style.display    = tab === 'login'    ? '' : 'none';
    document.getElementById('auth-pane-register').style.display = tab === 'register' ? '' : 'none';
    document.getElementById('auth-error').style.display = 'none';
}
function showAuthError(msg) {
    const e = document.getElementById('auth-error');
    e.textContent = msg; e.style.display = '';
}
async function submitLogin() {
    const phone = document.getElementById('lo-phone').value.trim();
    const pass  = document.getElementById('lo-pass').value;
    if (!phone || !pass) return showAuthError('Telefon ve şifre gerekli.');
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ phone, password: pass }),
        });
        const data = await res.json();
        if (!data.ok) return showAuthError(data.message || 'Giriş başarısız.');
        CURRENT_USER = data.user;
        closeAuth();
        showToast('Hoş geldin, '+data.user.name+'!');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showAuthError('Bağlantı hatası.'); }
}
async function submitRegister() {
    const name  = document.getElementById('rg-name').value.trim();
    const phone = document.getElementById('rg-phone').value.trim();
    const email = document.getElementById('rg-email').value.trim();
    const pass  = document.getElementById('rg-pass').value;
    if (!name || !phone || !pass) return showAuthError('İsim, telefon ve şifre gerekli.');
    if (pass.length < 6) return showAuthError('Şifre en az 6 karakter olmalı.');
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/auth/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, phone, email: email || null, password: pass }),
        });
        const data = await res.json();
        if (!data.ok) return showAuthError(data.message || 'Kayıt başarısız.');
        CURRENT_USER = data.user;
        closeAuth();
        showToast('Hoş geldin, '+data.user.name+'!');
        setTimeout(() => location.reload(), 600);
    } catch (e) { showAuthError('Bağlantı hatası.'); }
}
function openProfile() {
    if (!CURRENT_USER) return openAuth('login');
    document.getElementById('prof-name').textContent  = CURRENT_USER.name;
    document.getElementById('prof-phone').textContent = CURRENT_USER.phone;
    document.getElementById('prof-av').textContent    = (CURRENT_USER.name||'?').charAt(0).toUpperCase();
    document.getElementById('profile-modal').classList.add('is-open');
}
function closeProfile() { document.getElementById('profile-modal').classList.remove('is-open'); }
async function submitLogout() {
    try {
        await fetch('/m/' + QR_TOKEN + '/auth/logout', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
    } catch (e) {}
    CURRENT_USER = null;
    closeProfile();
    showToast('Çıkış yapıldı.');
    setTimeout(() => location.reload(), 500);
}

// ─────────────────────────────────────────────────────────────────
// ÜRÜN BEĞEN / FAVORİ / YORUM
// ─────────────────────────────────────────────────────────────────
async function pdLoadEngagement(pid) {
    try {
        const [sumRes, revRes] = await Promise.all([
            fetch('/m/' + QR_TOKEN + '/products/' + pid + '/summary', { headers: { 'Accept': 'application/json' } }),
            fetch('/m/' + QR_TOKEN + '/products/' + pid + '/reviews', { headers: { 'Accept': 'application/json' } }),
        ]);
        const sum = await sumRes.json();
        const rev = await revRes.json();
        renderEngagement(pid, sum, rev);
    } catch (e) {}
}
function renderEngagement(pid, sum, rev) {
    const wrap = document.getElementById('pd-engagement');
    if (!wrap) return;
    const liked = sum.user?.liked;
    const fav   = sum.user?.favorited;
    const eligible = sum.user?.eligible;

    let actions = '';
    if (!CURRENT_USER) {
        actions = `<div style="padding:12px;background:#f5f3ee;border-radius:10px;text-align:center;font-size:.85rem">
            Beğenmek, favoriye eklemek veya yorum yapmak için <a href="#" onclick="closePd();openAuth('login');return false" style="font-weight:700">giriş yap</a>.
        </div>`;
    } else if (!eligible) {
        actions = `<div style="padding:12px;background:#fff3eb;border:1px solid #ffd089;border-radius:10px;text-align:center;font-size:.85rem;color:#92400e">
            <i class="fas fa-info-circle"></i> Beğeni ve yorum için bu restoranda en az bir sipariş vermelisin. Favoriye ekleyebilirsin.
            <div style="margin-top:8px">
              <button onclick="toggleFav(${pid})" style="background:${fav?'#fff7ed':'transparent'};color:${fav?'#d9531c':'var(--soft)'};border:1.5px solid ${fav?'#fed7aa':'var(--line)'};padding:8px 14px;border-radius:8px;font-weight:600;cursor:pointer">
                <i class="fa${fav?'s':'r'} fa-bookmark"></i> ${fav ? 'Favoriden çıkar' : 'Favoriye ekle'}
              </button>
            </div>
        </div>`;
    } else {
        actions = `<div style="display:flex;gap:8px">
            <button onclick="toggleLike(${pid})" style="flex:1;padding:10px;background:${liked?'#fef2f2':'#fff'};color:${liked?'#dc2626':'var(--soft)'};border:1.5px solid ${liked?'#fecaca':'var(--line)'};border-radius:10px;font-weight:600;cursor:pointer">
                <i class="fa${liked?'s':'r'} fa-heart"></i> ${liked ? 'Beğenildi' : 'Beğen'} (<span id="like-count-${pid}">${sum.like_count}</span>)
            </button>
            <button onclick="toggleFav(${pid})" style="flex:1;padding:10px;background:${fav?'#fff7ed':'#fff'};color:${fav?'#d9531c':'var(--soft)'};border:1.5px solid ${fav?'#fed7aa':'var(--line)'};border-radius:10px;font-weight:600;cursor:pointer">
                <i class="fa${fav?'s':'r'} fa-bookmark"></i> ${fav ? 'Favorilerimde' : 'Favoriye ekle'}
            </button>
        </div>`;
    }
    let reviewsHtml = '';
    const reviews = rev.reviews || [];
    if (reviews.length > 0) {
        reviewsHtml = `<div style="margin-top:14px"><div style="font-weight:700;font-size:.92rem;margin-bottom:8px">Yorumlar (${reviews.length})</div>
            ${reviews.map(r => `
                <div style="background:#fafafa;border-radius:10px;padding:10px 12px;margin-bottom:6px;font-size:.85rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                        <div><strong>${escapeHtml(r.name)}</strong> ${r.rating ? renderStars(r.rating) : ''}</div>
                        <small style="color:var(--muted)">${escapeHtml(r.created_at)} ${r.is_mine ? `<a href="#" onclick="deleteReview(${r.id},${pid});return false" style="margin-left:6px;color:#ef4444">sil</a>` : ''}</small>
                    </div>
                    <div style="color:var(--soft);line-height:1.5">${escapeHtml(r.content)}</div>
                </div>`).join('')}
        </div>`;
    } else {
        reviewsHtml = `<div style="margin-top:12px;text-align:center;color:var(--muted);font-size:.82rem">Henüz yorum yok. İlk yorumu sen yap!</div>`;
    }

    let formHtml = '';
    if (CURRENT_USER && rev.can_review) {
        formHtml = `<div style="margin-top:14px;border-top:1px dashed var(--line);padding-top:12px">
            <div style="font-weight:600;font-size:.85rem;margin-bottom:6px">Puan ver (zorunlu):</div>
            <div id="rev-stars-${pid}" data-rating="5" style="display:flex;gap:2px;margin-bottom:8px">
                ${[1,2,3,4,5].map(n => `<button type="button" onclick="setRating(${pid},${n})" style="background:transparent;border:0;cursor:pointer;font-size:1.4rem;color:#cbd5e1;padding:0 2px" data-star="${n}">★</button>`).join('')}
                <span id="rev-stars-label-${pid}" style="font-size:.78rem;color:var(--muted);margin-left:6px;align-self:center"></span>
            </div>
            <div style="font-weight:600;font-size:.85rem;margin-bottom:6px">Yorumun</div>
            <textarea id="rev-text-${pid}" rows="2" maxlength="1000" placeholder="Bu ürün hakkında ne düşünüyorsun?" style="width:100%;padding:10px;border:1.5px solid var(--line);border-radius:10px;font-family:inherit;font-size:.88rem;resize:vertical"></textarea>
            <button onclick="submitReview(${pid})" style="margin-top:6px;width:100%;padding:10px;background:linear-gradient(135deg,var(--p),var(--p-d));color:#fff;border:0;border-radius:10px;font-weight:700;cursor:pointer">
                <i class="fas fa-paper-plane"></i> Yorumu Yayınla
            </button>
        </div>`;
    }
    wrap.innerHTML = `<div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--line)">
        ${actions}
        ${reviewsHtml}
        ${formHtml}
    </div>`;
    if (CURRENT_USER && rev.can_review) setRating(pid, 5);
}
async function toggleLike(pid) {
    if (!CURRENT_USER) { closePd(); return openAuth('login'); }
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/products/' + pid + '/like', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!data.ok) return showToast(data.message || 'Beğeni eklenemedi.', false);
        showToast(data.liked ? 'Beğenildi!' : 'Beğeni geri alındı.');
        pdLoadEngagement(pid);
    } catch (e) { showToast('Hata.', false); }
}
async function toggleFav(pid) {
    if (!CURRENT_USER) { closePd(); return openAuth('login'); }
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/products/' + pid + '/favorite', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!data.ok) return showToast(data.message || 'İşlem başarısız.', false);
        showToast(data.favorited ? 'Favorilere eklendi!' : 'Favoriden çıkarıldı.');
        // Kart üstünde fav ikonunu güncelle
        const card = document.querySelector('.product[data-pid="'+pid+'"]');
        if (card) card.dataset.fav = data.favorited ? '1' : '0';
        pdLoadEngagement(pid);
    } catch (e) { showToast('Hata.', false); }
}
async function submitReview(pid) {
    const ta = document.getElementById('rev-text-'+pid);
    const stars = document.getElementById('rev-stars-'+pid);
    const content = (ta?.value || '').trim();
    const rating = parseInt(stars?.dataset?.rating || '0', 10);
    if (content.length < 3) return showToast('Yorum en az 3 karakter olmalı.', false);
    if (rating < 1 || rating > 5) return showToast('Lütfen 1–5 yıldız seç.', false);
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/products/' + pid + '/reviews', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ content, rating }),
        });
        const data = await res.json();
        if (!data.ok) return showToast(data.message || 'Yorum gönderilemedi.', false);
        showToast('Yorumun yayınlandı!');
        pdLoadEngagement(pid);
    } catch (e) { showToast('Hata.', false); }
}

function setRating(pid, n) {
    const wrap = document.getElementById('rev-stars-'+pid);
    if (!wrap) return;
    wrap.dataset.rating = String(n);
    wrap.querySelectorAll('[data-star]').forEach(s => {
        s.style.color = (parseInt(s.dataset.star, 10) <= n) ? '#f59e0b' : '#cbd5e1';
    });
    const lbl = document.getElementById('rev-stars-label-'+pid);
    if (lbl) lbl.textContent = ['','Kötü','İdare eder','İyi','Çok iyi','Mükemmel'][n] || '';
}

function renderStars(n) {
    n = parseInt(n, 10) || 0;
    if (n <= 0) return '';
    let html = '<span style="color:#f59e0b;margin-left:6px;font-size:.84rem">';
    for (let i = 1; i <= 5; i++) html += i <= n ? '★' : '<span style="color:#cbd5e1">★</span>';
    html += '</span>';
    return html;
}
async function deleteReview(reviewId, pid) {
    if (!confirm('Bu yorumu silmek istediğine emin misin?')) return;
    try {
        const res = await fetch('/m/' + QR_TOKEN + '/reviews/' + reviewId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.ok) { showToast('Yorum silindi.'); pdLoadEngagement(pid); }
    } catch (e) {}
}
</script>
</body>
</html>
