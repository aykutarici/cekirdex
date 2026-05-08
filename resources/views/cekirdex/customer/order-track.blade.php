<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş #{{ $order->order_number }} · {{ $order->restaurant->name }}</title>
    <meta name="theme-color" content="{{ $order->restaurant->primary_color ?: '#ff6b35' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('cekirdex/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --p: {{ $order->restaurant->primary_color ?: '#ff6b35' }};
            --p-d: {{ $order->restaurant->secondary_color ?: '#d9531c' }};
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Inter", system-ui, sans-serif; background: #fffaf3; color: #1c1933; min-height: 100vh; padding: 20px 16px; }
        .wrap { max-width: 540px; margin: 0 auto; }

        .head { background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff; padding: 22px; border-radius: 18px; box-shadow: 0 12px 24px -10px rgba(255,107,53,.4); }
        .head .num { font-size: .82rem; opacity: .85; font-weight: 600; }
        .head h1 { font-size: 1.4rem; margin: 4px 0 8px; letter-spacing: -0.02em; }
        .head .meta { font-size: .88rem; opacity: .92; }

        .stepper { background: #fff; padding: 22px; border-radius: 18px; margin-top: 14px; box-shadow: 0 4px 14px -6px rgba(28,25,51,.10); }
        .step { display: flex; gap: 14px; align-items: flex-start; padding-bottom: 16px; position: relative; }
        .step::before { content: ''; position: absolute; left: 17px; top: 36px; bottom: 0; width: 2px; background: #efece6; }
        .step:last-child { padding-bottom: 0; }
        .step:last-child::before { display: none; }
        .step .ic { width: 36px; height: 36px; border-radius: 50%; background: #efece6; color: #7d7995; display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1; }
        .step.done .ic { background: #dcfce7; color: #166534; }
        .step.active .ic { background: var(--p); color: #fff; box-shadow: 0 0 0 4px rgba(255,107,53,.18); animation: pulse 1.6s ease-in-out infinite; }
        @keyframes pulse { 0%,100% { box-shadow: 0 0 0 4px rgba(255,107,53,.18); } 50% { box-shadow: 0 0 0 8px rgba(255,107,53,.05); } }
        .step .body .ttl { font-weight: 700; font-size: 1rem; }
        .step .body .ds { font-size: .82rem; color: #7d7995; margin-top: 2px; }
        .step.cancelled .ic { background: #fee2e2; color: #991b1b; }

        .card { background: #fff; padding: 18px 20px; border-radius: 16px; margin-top: 14px; box-shadow: 0 4px 14px -6px rgba(28,25,51,.10); }
        .card h3 { font-size: 1rem; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: .92rem; border-bottom: 1px solid #efece6; }
        .row:last-child { border-bottom: 0; }
        .row.tot { font-weight: 800; padding-top: 10px; border-top: 2px solid #efece6; border-bottom: 0; margin-top: 6px; }
        .item-line { display: flex; justify-content: space-between; padding: 4px 0; font-size: .9rem; }
        .item-line .nm { flex: 1; }
        .item-line .qty { color: #7d7995; margin: 0 8px; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="head">
        <div class="num">SİPARİŞ #{{ $order->order_number }}</div>
        <h1>{{ $order->restaurant->name }}</h1>
        <div class="meta">
            <i class="fas fa-{{ $order->order_type === 'delivery' ? 'motorcycle' : 'bag-shopping' }}"></i>
            {{ $order->type_label }}
            @if($order->eta_minutes) · Tahmini hazırlık: {{ $order->eta_minutes }} dk @endif
        </div>
    </div>

    <div class="stepper" id="stepper">
        @php
            $steps = [
                ['key' => 'new',       'ttl' => 'Sipariş alındı',       'ic' => 'check', 'ds' => 'Restoran sipariş bilgisini aldı.'],
                ['key' => 'confirmed', 'ttl' => 'Onaylandı',            'ic' => 'thumbs-up', 'ds' => 'Restoran siparişinizi onayladı.'],
                ['key' => 'preparing', 'ttl' => 'Hazırlanıyor',         'ic' => 'fire', 'ds' => 'Mutfak siparişinizi hazırlıyor.'],
                ['key' => 'ready',     'ttl' => $order->order_type === 'delivery' ? 'Yola çıktı' : 'Hazır', 'ic' => 'box', 'ds' => $order->order_type === 'delivery' ? 'Kuryeye verildi.' : 'Restoranımıza gelebilirsiniz.'],
                ['key' => 'delivered', 'ttl' => 'Teslim edildi',        'ic' => 'flag-checkered', 'ds' => 'Afiyet olsun!'],
            ];
            $statusOrder = ['new' => 0, 'confirmed' => 1, 'preparing' => 2, 'ready' => 3, 'delivered' => 4, 'served' => 4, 'closed' => 4, 'cancelled' => -1];
            $cur = $statusOrder[$order->status] ?? 0;
        @endphp
        @if($order->status === 'cancelled')
            <div class="step cancelled">
                <div class="ic"><i class="fas fa-circle-xmark"></i></div>
                <div class="body">
                    <div class="ttl">Sipariş iptal edildi</div>
                    <div class="ds">Üzgünüz, siparişiniz iptal edilmiştir.</div>
                </div>
            </div>
        @else
            @foreach($steps as $i => $s)
                <div class="step {{ $cur > $i ? 'done' : ($cur === $i ? 'active' : '') }}">
                    <div class="ic"><i class="fas fa-{{ $s['ic'] }}"></i></div>
                    <div class="body">
                        <div class="ttl">{{ $s['ttl'] }}</div>
                        <div class="ds">{{ $s['ds'] }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card">
        <h3><i class="fas fa-receipt"></i> Sipariş Detayı</h3>
        @foreach($order->items as $it)
            <div class="item-line">
                <span class="nm">{{ $it->name }}</span>
                <span class="qty">× {{ $it->quantity }}</span>
                <span>{{ number_format((float) $it->subtotal, 2, ',', '.') }} ₺</span>
            </div>
        @endforeach
        <div class="row" style="margin-top: 10px;"><span>Ara toplam</span><span>{{ number_format((float) $order->subtotal, 2, ',', '.') }} ₺</span></div>
        @if((float) $order->tax > 0)<div class="row"><span>KDV</span><span>{{ number_format((float) $order->tax, 2, ',', '.') }} ₺</span></div>@endif
        @if((float) $order->service_charge > 0)<div class="row"><span>Servis</span><span>{{ number_format((float) $order->service_charge, 2, ',', '.') }} ₺</span></div>@endif
        @if((float) $order->delivery_fee > 0)<div class="row"><span>Teslim ücreti</span><span>{{ number_format((float) $order->delivery_fee, 2, ',', '.') }} ₺</span></div>@endif
        <div class="row tot"><span>Toplam</span><span>{{ number_format((float) $order->total, 2, ',', '.') }} ₺</span></div>
    </div>

    <div class="card" style="text-align: center; color: #7d7995; font-size: .85rem;">
        Sayfa otomatik güncelleniyor · <strong>{{ $order->contact_phone }}</strong>
    </div>
</div>

<script>
const FEED = @json(route('cekirdex.public.order.feed', $order->public_code));
let lastStatus = @json($order->status);
async function tick() {
    try {
        const r = await fetch(FEED);
        if (!r.ok) return;
        const d = await r.json();
        if (d.status !== lastStatus) {
            window.location.reload();
        }
    } catch (e) {}
}
setInterval(tick, 12000);
</script>
</body>
</html>
