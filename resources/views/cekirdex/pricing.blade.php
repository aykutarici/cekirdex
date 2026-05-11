@extends('cekirdex.layout')

@section('title', 'Çekirdex Fiyatlandırma — Aylık ücret yok, sadece komisyon')
@section('description', 'Çekirdex restoran platformunun fiyatlandırması: aylık veya yıllık ücret yok. Sadece platform üzerinden geçen ödemelerde düşük oranlı işlem komisyonu.')
@section('canonical', url('/fiyatlandirma'))

@push('styles')
<style>
.p-hero { padding: 70px 0 50px; background: linear-gradient(180deg,#fffaf3 0%,#ffffff 100%); }
.p-hero h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 16px; text-align: center; }
.p-hero p  { color: var(--c-text-soft); text-align: center; max-width: 720px; margin: 0 auto; font-size: 1.05rem; }

.p-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; max-width: 980px; margin: 50px auto 0; padding: 0 22px; }
@media (max-width: 800px) { .p-cards { grid-template-columns: 1fr; } }
.p-card { background: #fff; border: 1px solid var(--c-line); border-radius: 22px; padding: 38px 32px; position: relative; box-shadow: var(--c-shadow); }
.p-card.featured { background: linear-gradient(135deg,#fff8f1 0%,#fff5fb 100%); border-color: #ffd0a8; box-shadow: 0 24px 60px -16px rgba(255,107,53,.22); }
.p-card.featured::before {
    content: 'Önerilen'; position: absolute; top: 18px; right: 18px;
    background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff;
    padding: 4px 12px; border-radius: 99px; font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
}
.p-name { color: var(--c-text); font-weight: 700; font-size: 1.05rem; }
.p-amount { font-size: 2.6rem; font-weight: 900; margin: 12px 0 6px; letter-spacing: -0.02em;
    background: linear-gradient(135deg,#d9531c,#ff8a4c);
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.p-amount small { font-size: .95rem; -webkit-text-fill-color: var(--c-muted); color: var(--c-muted); font-weight: 500; }
.p-note { color: var(--c-muted); font-size: .9rem; margin-bottom: 22px; }
.p-feats { list-style: none; padding: 0; margin: 0 0 28px; }
.p-feats li { padding: 8px 0 8px 28px; position: relative; color: var(--c-text-soft); font-size: .94rem; }
.p-feats li::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; left: 0; color: var(--c-accent-d); top: 9px; }

.p-faq { padding: 60px 0 80px; }
</style>
@endpush

@section('outer')
<section class="p-hero"><div class="c-container">
    <span class="c-eyebrow" style="display:block;text-align:center;width:fit-content;margin:0 auto 14px">
        <i class="fas fa-tag"></i> Fiyatlandırma
    </span>
    <h1>Bizim de ancak siz kazandığınızda kazanmamız gerekiyor.</h1>
    <p>Çekirdex'in fiyatlandırma modeli abonelik üzerine değil, başarı üzerinedir. Restoranınız kazandıkça biz kazanırız; hareketsiz kaldığında size yük olmayız.</p>
</div></section>

<div class="p-cards">
    <div class="p-card">
        <div class="p-name"><i class="fas fa-leaf" style="color:#10b981"></i> Çekirdex Başlangıç</div>
        <div class="p-amount">Ücretsiz</div>
        <p class="p-note">Restoranınızı sisteme kuruluyorsa, masaları tanımlıyorsa veya online ödeme almıyorsanız.</p>
        <ul class="p-feats">
            <li>Sınırsız menü, ürün ve masa</li>
            <li>QR kod üretimi (PDF indirme)</li>
            <li>Restoran paneli ve mutfak ekranı</li>
            <li>Garson çağırma sistemi</li>
            <li>Temel raporlar</li>
            <li class="muted" style="color:#b4b0c4">Online ödeme (Standart'ta)</li>
        </ul>
        <a class="btn-c-ghost" href="{{ route('cekirdex.register') }}" style="width:100%"><i class="fas fa-rocket"></i> Hemen başla</a>
    </div>

    <div class="p-card featured">
        <div class="p-name"><i class="fas fa-star" style="color:#f59e0b"></i> Çekirdex Standart</div>
        <div class="p-amount">%1.49 <small>· işlem komisyonu</small></div>
        <p class="p-note">Online ödeme, hesap paylaşma, sadakat sistemi açıldığında devreye girer. Aylık ücret yok.</p>
        <ul class="p-feats">
            <li>Tüm Başlangıç özellikleri</li>
            <li><strong>Online ödeme — kart, Apple Pay, Google Pay, FAST</strong></li>
            <li>Hesap paylaşma & ürün başına ödeme</li>
            <li>Sadakat sistemi & VIP seviyeler</li>
            <li>Kampanya yönetimi (Happy Hour, 2 al 1 ücretsiz)</li>
            <li>Yemek kartı entegrasyonları</li>
            <li>Detaylı raporlar ve dışa aktarım</li>
            <li>Çoklu şube yönetimi</li>
            <li>Öncelikli e-posta desteği</li>
        </ul>
        <a class="btn-c" href="{{ route('cekirdex.register') }}" style="width:100%"><i class="fas fa-rocket"></i> Restoranını ekle</a>
    </div>
</div>

<section class="p-faq"><div class="c-container">
    <div class="ck-section-head">
        <h2 class="ck-section-title">Sık sorulanlar</h2>
    </div>
    <div class="ck-faq-list">
        <details class="ck-faq" open>
            <summary>Komisyon nasıl hesaplanır?</summary>
            <div class="ck-faq-body">Komisyon yalnızca Çekirdex üzerinden tahsil edilen ödemelerden alınır. Müşteri nakit öderse veya restoran kasasından öderse komisyon uygulanmaz.</div>
        </details>
        <details class="ck-faq">
            <summary>Kurulum ücreti var mı?</summary>
            <div class="ck-faq-body">Hayır. Hesap açma, kurulum, eğitim — hiçbiri için ücret almıyoruz. Türkçe dokümantasyon ve destek dahildir.</div>
        </details>
        <details class="ck-faq">
            <summary>Para kasama ne zaman geçer?</summary>
            <div class="ck-faq-body">Ödeme sağlayıcılarımız (iyzico/PayTR alt-merchant modeli) standart valör süreleriyle (genellikle 1-3 iş günü) restoran banka hesabınıza aktarım yapar.</div>
        </details>
        <details class="ck-faq">
            <summary>İptal etmek istersem?</summary>
            <div class="ck-faq-body">İstediğiniz an hesabınızı kapatabilirsiniz. Cayma bedeli, kapanış ücreti yok. Verilerinizi dışa aktararak ayrılabilirsiniz.</div>
        </details>
    </div>
</div></section>
@endsection
