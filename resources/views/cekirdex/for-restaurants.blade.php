@extends('cekirdex.layout')

@section('title', 'Restoranlar için Çekirdex — QR sipariş, ödeme, sadakat tek panelde')
@section('description', 'Çekirdex restoranınıza QR menü, masadan sipariş, garson çağrısı, mutfak ekranı, kampanya yönetimi ve sadakat sistemini getirir. Aylık ücretsiz, kurulum 5 dakika.')
@section('canonical', url('/restoranlar'))

@push('styles')
<style>
.r-hero { padding: 70px 0 60px; background: linear-gradient(180deg,#fffaf3 0%,#ffffff 100%); border-bottom: 1px solid var(--c-line); }
.r-hero h1 { font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 16px; }
.r-hero p  { font-size: 1.1rem; color: var(--c-text-soft); max-width: 720px; }
.r-section { padding: 70px 0; }
.r-deep { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; padding: 60px 0; border-bottom: 1px solid var(--c-line); }
.r-deep:last-of-type { border-bottom: 0; }
.r-deep.reverse { direction: rtl; }
.r-deep.reverse > * { direction: ltr; }
@media (max-width: 900px) { .r-deep, .r-deep.reverse { grid-template-columns: 1fr; gap: 32px; direction: ltr; padding: 40px 0; } }
.r-deep h3 { font-size: clamp(1.4rem, 2.4vw, 1.95rem); margin-bottom: 14px; }
.r-deep p  { color: var(--c-text-soft); margin-bottom: 14px; }
.r-deep ul { list-style: none; padding: 0; margin: 14px 0; }
.r-deep ul li { padding: 6px 0 6px 28px; position: relative; color: var(--c-text-soft); font-size: .94rem; }
.r-deep ul li::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; left: 0; color: var(--c-accent-d); top: 8px; }
.r-vis {
    aspect-ratio: 1/0.95; border-radius: 28px;
    background: linear-gradient(135deg,#fff8f1 0%,#fff5fb 100%);
    border: 1px solid var(--c-line);
    display: flex; align-items: center; justify-content: center; color: var(--c-accent-d);
    box-shadow: var(--c-shadow-lg); font-size: 5rem;
}
</style>
@endpush

@section('outer')
<section class="r-hero"><div class="c-container">
    <span class="c-eyebrow"><i class="fas fa-store"></i> Restoranlar için</span>
    <h1>Operasyonu basitleştirin, geliri artırın.</h1>
    <p>Çekirdex; siparişten ödemeye, mutfaktan müşteri sadakatine kadar yeme-içme operasyonunun tamamını dijitalleştirir.</p>
    <div style="margin-top:22px;display:flex;flex-wrap:wrap;gap:12px">
        <a class="btn-c" href="{{ route('cekirdex.register') }}"><i class="fas fa-rocket"></i> Hemen başla</a>
        <a class="btn-c-ghost" href="{{ route('cekirdex.pricing') }}"><i class="fas fa-tag"></i> Fiyatlandırma</a>
    </div>
</div></section>

<section class="r-section"><div class="c-container">
    <div class="r-deep">
        <div>
            <span class="c-eyebrow">QR menü</span>
            <h3>Menünüzü dakikada dijitalleştirin.</h3>
            <p>Kategoriler, ürünler, varyasyonlar, ekstralar ve fotoğraflarla zengin menüler oluşturun. Stok bittiğinde tek tuşla pasifleştirin.</p>
            <ul>
                <li>Sınırsız kategori ve ürün</li>
                <li>Ürün başına özel notlar ve varyasyonlar</li>
                <li>Saatlik / günlük menüler (örn. öğle / akşam)</li>
                <li>Çoklu dil desteği</li>
            </ul>
        </div>
        <div class="r-vis"><i class="fas fa-qrcode"></i></div>
    </div>

    <div class="r-deep reverse">
        <div>
            <span class="c-eyebrow">Mutfak ekranı</span>
            <h3>Siparişler doğrudan mutfağa düşer.</h3>
            <p>Garson — mutfak iletişimini dijitalleştirin. Sipariş aşamaları (hazırlanıyor, hazır, servis edildi) tek dokunuşla yönetilir.</p>
            <ul>
                <li>Aktif siparişlerin gerçek zamanlı listesi</li>
                <li>Aşama bazlı renk kodlaması</li>
                <li>Hata azaltıcı sade arayüz</li>
            </ul>
        </div>
        <div class="r-vis"><i class="fas fa-fire"></i></div>
    </div>

    <div class="r-deep">
        <div>
            <span class="c-eyebrow">Hesap & ödeme</span>
            <h3>Hesap bölme derdi tarihe karışıyor.</h3>
            <p>Aynı masadaki müşteriler kendi telefonlarından bağlanır, sadece kendi siparişlerini öderler. Apple Pay, Google Pay, kart, FAST.</p>
            <ul>
                <li>Hesap paylaşma — ürün başına seçim</li>
                <li>Kart, Apple Pay, Google Pay, FAST, TR Karekod</li>
                <li>Yemek kartı entegrasyonları</li>
                <li>Tek tuşla bahşiş seçeneği</li>
            </ul>
        </div>
        <div class="r-vis"><i class="fas fa-credit-card"></i></div>
    </div>

    <div class="r-deep reverse">
        <div>
            <span class="c-eyebrow">Sadakat</span>
            <h3>Tek seferlik müşteri yerine sürekli müşteri.</h3>
            <p>Her ödeme Çekirdex puanı kazandırır, müşteri sonraki ziyarette kullanır. VIP seviyeleri ve özel kampanyalarla bağlılığı yükseltin.</p>
            <ul>
                <li>Harcama → puan oranı ayarı</li>
                <li>VIP seviye ve özel ödüller</li>
                <li>Kampanya: 2 al 1 ücretsiz, Happy Hour, %20</li>
            </ul>
        </div>
        <div class="r-vis"><i class="fas fa-medal"></i></div>
    </div>

    <div class="r-deep">
        <div>
            <span class="c-eyebrow">Raporlar</span>
            <h3>Tahmin değil, veri.</h3>
            <p>Günlük ciro, popüler ürünler, masa doluluk oranı, ortalama sepet, müşteri tekrarı, saat bazlı yoğunluk; hepsi dışa aktarılabilir.</p>
            <ul>
                <li>Ciro ve sipariş trendleri (gün/hafta/ay)</li>
                <li>Şube karşılaştırma</li>
                <li>CSV ve PDF dışa aktarım</li>
            </ul>
        </div>
        <div class="r-vis"><i class="fas fa-chart-line"></i></div>
    </div>
</div></section>

<section class="ck-final-cta" style="margin-top:40px"><div class="c-container">
    <h2>Restoranınızı bugün dijitalleştirin.</h2>
    <p style="color:var(--c-text-soft);margin-bottom:24px">Kurulum 5 dakika, kart bilgisi gerekmez.</p>
    <a class="btn-c" href="{{ route('cekirdex.register') }}"><i class="fas fa-rocket"></i> Ücretsiz başla</a>
</div></section>
@endsection
