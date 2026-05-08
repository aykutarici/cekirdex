<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervasyon · {{ $reservation->restaurant->name }}</title>
    <meta name="theme-color" content="{{ $reservation->restaurant->primary_color ?: '#ff6b35' }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('cekirdex/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --p: {{ $reservation->restaurant->primary_color ?: '#ff6b35' }};
            --p-d: {{ $reservation->restaurant->secondary_color ?: '#d9531c' }};
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Inter", system-ui, sans-serif; background: #fffaf3; color: #1c1933; min-height: 100vh; padding: 20px 16px; }
        .wrap { max-width: 540px; margin: 0 auto; }
        .head { background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff; padding: 22px; border-radius: 18px; box-shadow: 0 12px 24px -10px rgba(255,107,53,.4); }
        .head .lbl { font-size: .82rem; opacity: .85; font-weight: 600; }
        .head h1 { font-size: 1.4rem; margin: 4px 0 8px; letter-spacing: -0.02em; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 99px; font-weight: 700; font-size: .82rem; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #dcfce7; color: #166534; }
        .badge-seated    { background: #e0f2fe; color: #075985; }
        .badge-completed { background: #ede9fe; color: #5b21b6; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .badge-no_show   { background: #fee2e2; color: #991b1b; }
        .card { background: #fff; padding: 18px 20px; border-radius: 16px; margin-top: 14px; box-shadow: 0 4px 14px -6px rgba(28,25,51,.10); }
        .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: .94rem; border-bottom: 1px solid #efece6; }
        .row:last-child { border-bottom: 0; }
        .row .k { color: #7d7995; }
        .btn { display: block; width: 100%; padding: 12px; background: #fee2e2; color: #991b1b; border: 0; border-radius: 12px; font-weight: 700; cursor: pointer; margin-top: 14px; }
        .btn-dl {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 12px; margin-top: 12px; border: 0; border-radius: 12px; font-weight: 700; cursor: pointer;
            background: linear-gradient(135deg, var(--p), var(--p-d)); color: #fff !important; text-decoration: none;
            box-shadow: 0 8px 22px -8px rgba(255,107,53,.45);
        }
        .qr-card .hint { font-size: .82rem; color: #7d7995; margin-top: 10px; line-height: 1.45; }
        .qr-card img {
            width: 220px; max-width: 100%; height: auto; display: block; margin: 14px auto 0;
            border-radius: 12px; border: 1px solid #efece6;
        }
        .alert { background: #fef3c7; color: #92400e; padding: 10px 14px; border-radius: 10px; margin-top: 10px; font-size: .88rem; }
        .alert.ok { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div class="lbl">REZERVASYON</div>
        <h1>{{ $reservation->restaurant->name }}</h1>
        <div style="margin-top: 8px;">
            <span class="badge badge-{{ $reservation->status }}"><i class="fas fa-circle"></i> {{ $reservation->status_label }}</span>
        </div>
    </div>

    @if(session('success'))<div class="card" style="background: #dcfce7;"><strong style="color: #166534;">{{ session('success') }}</strong></div>@endif
    @if(session('error'))<div class="card" style="background: #fee2e2;"><strong style="color: #991b1b;">{{ session('error') }}</strong></div>@endif

    <div class="card">
        <div class="row"><span class="k">Tarih</span><span><strong>{{ $reservation->reserved_for->format('d.m.Y') }}</strong></span></div>
        <div class="row"><span class="k">Saat</span><span><strong>{{ $reservation->reserved_for->format('H:i') }}</strong></span></div>
        <div class="row"><span class="k">Kişi</span><span><strong>{{ $reservation->party_size }}</strong></span></div>
        <div class="row"><span class="k">Ad</span><span>{{ $reservation->contact_name }}</span></div>
        <div class="row"><span class="k">Telefon</span><span>{{ $reservation->contact_phone }}</span></div>
        @if($reservation->note)
            <div class="row" style="display:block;"><span class="k">Notunuz:</span><div style="margin-top:4px;color:#4a4566;">{{ $reservation->note }}</div></div>
        @endif
        @if($reservation->status === 'confirmed' && $reservation->admin_note)
            <div class="alert ok"><strong>Restoran notu:</strong> {{ $reservation->admin_note }}</div>
        @endif
    </div>

    @if(! in_array($reservation->status, ['cancelled', 'completed', 'no_show']))
        <div class="card qr-card">
            <div class="row" style="border: 0; padding-top: 0; display: block;">
                <strong style="display: block; margin-bottom: 4px;">Girişte gösterin</strong>
                <span class="k" style="font-size: .88rem;">Bu QR kodu rezervasyon sayfanıza gider. Restorana vardığınızda ekranda gösterin veya indirip kaydedin.</span>
            </div>
            <img
                src="{{ route('cekirdex.public.reservation.qr', ['publicCode' => $reservation->public_code]) }}"
                width="220"
                height="220"
                alt="Rezervasyon QR kodu — {{ $reservation->public_code }}"
                loading="lazy">
            <a href="{{ route('cekirdex.public.reservation.qr', ['publicCode' => $reservation->public_code, 'download' => true]) }}"
               class="btn-dl" download>
                <i class="fas fa-download"></i> QR kodunu indir (PNG)
            </a>
            <p class="hint"><i class="fas fa-mobile-screen-button"></i> Telefonda görsele uzun basarak da kaydedebilirsiniz.</p>
        </div>
    @endif

    @if($reservation->status === 'pending')
        <div class="alert"><i class="fas fa-clock"></i> Restoran rezervasyonunuzu inceliyor — onaylanınca e-posta alacaksınız.</div>
    @elseif($reservation->status === 'confirmed')
        <div class="alert ok"><i class="fas fa-check-circle"></i> Rezervasyonunuz onaylandı — sizi bekliyoruz!</div>
    @endif

    @if(in_array($reservation->status, ['pending', 'confirmed']) && $reservation->reserved_for->gt(now()->addMinutes(30)))
        <form action="{{ route('cekirdex.public.reservation.cancel', $reservation->public_code) }}" method="POST"
              onsubmit="return confirm('Rezervasyonu iptal etmek istediğinize emin misiniz?')">
            @csrf
            <button type="submit" class="btn"><i class="fas fa-xmark"></i> Rezervasyonu İptal Et</button>
        </form>
    @endif

    <div class="card" style="text-align: center; color: #7d7995; font-size: .85rem;">
        Sorularınız için <strong>{{ $reservation->restaurant->phone ?: $reservation->restaurant->name }}</strong>
    </div>
</div>
</body>
</html>
