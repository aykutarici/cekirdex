@extends('cekirdex.layout')

@section('title', 'Kullanım Koşulları — Çekirdex')
@section('description', 'Çekirdex restoran platformu kullanım koşulları, hizmet kapsamı ve sorumluluklar.')
@section('canonical', url('/kullanim-kosullari'))

@section('content')
<span class="c-eyebrow"><i class="fas fa-file-contract"></i> Kullanım koşulları</span>
<h1 style="font-size:clamp(1.8rem,3.5vw,2.4rem);margin-bottom:8px">Kullanım Koşulları</h1>
<p class="muted">Son güncelleme: {{ now()->translatedFormat('d F Y') }}</p>

<h2>1. Genel hükümler</h2>
<p>Çekirdex, restoranlar ve müşterileri arasında dijital sipariş, ödeme ve sadakat hizmetleri sunan bir İninia ürünüdür. Hizmeti kullanarak işbu koşulları kabul etmiş olursunuz.</p>

<h2>2. Hesap ve sorumluluk</h2>
<ul>
    <li>Hesap açan kişi, restoran adına yetkili olduğunu beyan eder.</li>
    <li>Hesap güvenliğinden ve şifre paylaşımından kullanıcı sorumludur.</li>
    <li>Yanıltıcı menü, ürün veya fiyat bilgisi yayımlamak yasaktır.</li>
    <li>Çekirdex, kötüye kullanım tespit ettiğinde hesabı askıya alma hakkını saklı tutar.</li>
</ul>

<h2>3. Ücretlendirme</h2>
<ul>
    <li>Çekirdex'in temel kullanım modeli ücretsizdir.</li>
    <li>Platform üzerinden tahsil edilen ödemelerde, fiyatlandırma sayfasında belirtilen oranda işlem komisyonu uygulanır.</li>
    <li>Komisyon oranları değiştirilirse en az 30 gün önceden duyurulur.</li>
</ul>

<h2>4. İçerik ve telif hakları</h2>
<p>Restoranların yüklediği menü, fotoğraf ve metinlerin kullanım haklarını sağlamaktan kullanıcılar sorumludur. Çekirdex platformunun yazılım ve tasarımı İninia'ya aittir.</p>

<h2>5. Hizmet seviyesi</h2>
<p>Çekirdex makul ölçülerde 7/24 erişilebilir bir hizmet sağlamayı taahhüt eder. Planlı bakımlar önceden duyurulur. Force majeure durumlarında oluşacak kesintilerden Çekirdex sorumlu tutulamaz.</p>

<h2>6. Sorumluluğun sınırlandırılması</h2>
<p>Çekirdex; restoranların müşterileriyle ilişkilerinde aracı bir teknoloji platformudur. Sipariş içeriği, ürün kalitesi, gıda güvenliği gibi konulardan restoranın kendisi sorumludur.</p>

<h2>7. Hesabın kapatılması</h2>
<p>Kullanıcı dilediği zaman hesabını kapatabilir. Çekirdex, koşulların ihlali halinde hesabı uyarıda bulunarak veya bulunmayarak kapatma hakkını saklı tutar.</p>

<h2>8. Uyuşmazlık ve uygulanacak hukuk</h2>
<p>Bu koşullara ilişkin uyuşmazlıklarda Türkiye Cumhuriyeti hukuku uygulanır; İstanbul Mahkemeleri ve İcra Müdürlükleri yetkilidir.</p>

<h2>9. İletişim</h2>
<p>Soru ve bildirimleriniz için: <a href="mailto:cekirdex@ininia.com">cekirdex@ininia.com</a></p>
@endsection
