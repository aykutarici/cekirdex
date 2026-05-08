@extends('cekirdex.layout')

@section('title', 'Müşteriler için Çekirdex — QR ile sipariş, hesap paylaşma, puan kazanma')
@section('description', 'Çekirdex ile masaya oturduğunuzda QR\'ı okutun, menüden seçin, hesabı paylaşın ve hızlıca ödeyin. Her ödemede puan kazanın, sonraki ziyaretinizde kullanın.')
@section('canonical', url('/cekirdex/musteriler'))

@push('styles')
<style>
.g-hero { padding: 70px 0; background: linear-gradient(180deg,#fff5fb 0%,#fffaf3 100%); border-bottom: 1px solid var(--c-line); }
.g-hero h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 16px; }
.g-section { padding: 60px 0; }
.g-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
@media (max-width: 900px) { .g-grid { grid-template-columns: 1fr; } }
.g-card { background: #fff; border: 1px solid var(--c-line); border-radius: 18px; padding: 30px 24px; box-shadow: var(--c-shadow); }
.g-card .ic { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; margin-bottom: 16px; }
.g-card h3 { font-size: 1.15rem; margin-bottom: 8px; }
.g-card p { color: var(--c-text-soft); margin: 0; font-size: .94rem; line-height: 1.6; }
</style>
@endpush

@section('outer')
<section class="g-hero"><div class="c-container">
    <span class="c-eyebrow"><i class="fas fa-mobile-screen"></i> Müşteriler için</span>
    <h1>Sıra beklemeden sipariş, hesap kavgası olmadan ödeme.</h1>
    <p style="color:var(--c-text-soft);font-size:1.1rem;max-width:720px">
        Restoranda Çekirdex QR'ını gördüğünüzde — uygulama indirmenize gerek yok.
        Telefon kameranızla okuturken menü açılır; siparişiniz mutfağa düşer, ödemenizi parmak ucuyla tamamlarsınız.
    </p>
</div></section>

<section class="g-section"><div class="c-container">
    <div class="g-grid">
        <div class="g-card">
            <div class="ic" style="background:linear-gradient(135deg,#ff8a4c,#ff6b35)"><i class="fas fa-qrcode"></i></div>
            <h3>QR ile bağlan</h3>
            <p>Masadaki QR kodu telefon kameranızla okutun. Menü ve restoran otomatik tanınır. Uygulama yok, kayıt yok.</p>
        </div>
        <div class="g-card">
            <div class="ic" style="background:linear-gradient(135deg,#ffb347,#f59e0b)"><i class="fas fa-utensils"></i></div>
            <h3>Sipariş ver</h3>
            <p>Ürünleri seçin, varyasyonlarını belirleyin, sepetinize ekleyin. Garson aramak zorunda kalmadan siparişiniz mutfağa iletilir.</p>
        </div>
        <div class="g-card">
            <div class="ic" style="background:linear-gradient(135deg,#f472b6,#ec4899)"><i class="fas fa-bell-concierge"></i></div>
            <h3>Garson çağır</h3>
            <p>Su, peçete, hesap veya ek ürün — tek dokunuşla garsonu çağırın. Anında bildirim gider, kimseyi aramanıza gerek kalmaz.</p>
        </div>
        <div class="g-card">
            <div class="ic" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-people-group"></i></div>
            <h3>Hesabı paylaş</h3>
            <p>Aynı masadaki herkes kendi telefonundan bağlanır, sadece kendi siparişini seçer ve öder. Hesap bölme matematiği yok.</p>
        </div>
        <div class="g-card">
            <div class="ic" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-credit-card"></i></div>
            <h3>Hızlı öde</h3>
            <p>Apple Pay, Google Pay, kredi/banka kartı, FAST, TR Karekod, yemek kartı — istediğiniz yöntemle saniyeler içinde ödeyin.</p>
        </div>
        <div class="g-card">
            <div class="ic" style="background:linear-gradient(135deg,#a855f7,#7e22ce)"><i class="fas fa-medal"></i></div>
            <h3>Puan kazan</h3>
            <p>Her ödemede Çekirdex puanı kazanın. Sonraki ziyaretinizde indirim, ücretsiz ürün veya özel kampanyalarda kullanın.</p>
        </div>
    </div>
</div></section>

<section class="ck-final-cta"><div class="c-container">
    <h2>Çekirdex'li bir restoran arıyorsanız…</h2>
    <p style="color:var(--c-text-soft);margin-bottom:24px">…oraya yakında varacaksınız. Ya da sahibine önerin!</p>
    <a class="btn-c-ghost" href="{{ route('cekirdex.contact') }}"><i class="fas fa-headset"></i> Bize yazın</a>
</div></section>
@endsection
