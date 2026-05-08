@extends('cekirdex.layout')

@section('title', 'Gizlilik Politikası — Çekirdex')
@section('description', 'Çekirdex restoran platformu kişisel verilerinizi nasıl işliyor — kapsamlı gizlilik politikası.')
@section('canonical', url('/cekirdex/gizlilik'))

@section('content')
<span class="c-eyebrow"><i class="fas fa-shield-halved"></i> Gizlilik</span>
<h1 style="font-size:clamp(1.8rem,3.5vw,2.4rem);margin-bottom:8px">Gizlilik Politikası</h1>
<p class="muted">Son güncelleme: {{ now()->translatedFormat('d F Y') }}</p>

<h2>Veri sorumlusu</h2>
<p>Çekirdex platformunun veri sorumlusu, bir İninia ürünü olarak <strong>İninia Teknoloji Limited Şirketi</strong>'dir. İletişim: <a href="mailto:cekirdex@ininia.com">cekirdex@ininia.com</a></p>

<h2>Topladığımız veriler</h2>
<p>Çekirdex iki farklı kullanıcı tipiyle çalışır:</p>
<h3>Restoran kullanıcıları (kayıt olanlar)</h3>
<ul>
    <li>Ad, soyad, e-posta, telefon, restoran/işletme adı</li>
    <li>Restoran adresi, şehir, ülke</li>
    <li>Şifre (bcrypt ile şifrelenir)</li>
    <li>Oturum açma kayıtları (IP, tarih)</li>
    <li>Menü, ürün, masa, sipariş, ödeme verileri</li>
</ul>
<h3>Restoran müşterileri (QR ile bağlananlar)</h3>
<ul>
    <li>Sipariş ve çağrı kayıtları (masa, ürünler, tutar)</li>
    <li>İsteğe bağlı isim, telefon</li>
    <li>IP adresi ve tarayıcı bilgisi (güvenlik için)</li>
</ul>

<h2>Verileri ne için kullanıyoruz?</h2>
<ul>
    <li>Hizmeti sunmak (sipariş, ödeme, çağrı yönetimi)</li>
    <li>Hesap güvenliği ve hile önleme</li>
    <li>Yasal yükümlülükler (vergi, fatura, suç önleme)</li>
    <li>Hizmet iyileştirme ve istatistikler (anonim)</li>
    <li>İletişim formu üzerinden gelen taleplere yanıt vermek</li>
</ul>

<h2>Verileri kimlerle paylaşıyoruz?</h2>
<p>Verilerinizi reklam veya pazarlama amacıyla üçüncü taraflara satmıyoruz. Yalnızca aşağıdaki teknik ortaklarla zorunlu paylaşımlar yapılır:</p>
<ul>
    <li><strong>Ödeme sağlayıcıları</strong> (iyzico, PayTR vb.) — ödeme işlemlerinin tamamlanması için</li>
    <li><strong>Bulut altyapı sağlayıcıları</strong> — hosting ve yedekleme</li>
    <li><strong>Yetkili merciler</strong> — yasal zorunluluk hâlinde</li>
</ul>

<h2>Verilerin saklanma süresi</h2>
<p>Sipariş ve fatura verileri vergi mevzuatı gereği 10 yıl saklanır. Hesabınızı kapattığınızda kişisel verileriniz anonimleştirilir veya bu yasal süre sonunda silinir.</p>

<h2>Çerezler</h2>
<p>Oturum yönetimi, dil tercihi ve hizmet kalitesi için zorunlu çerezler kullanılır. Pazarlama çerezleri yoktur.</p>

<h2>Haklarınız</h2>
<p>KVKK madde 11 kapsamında: verilerinizin işlenip işlenmediğini öğrenme, kopyasını isteme, düzeltme, silme, anonimleştirme ve itiraz haklarına sahipsiniz. Talepleriniz için <a href="mailto:cekirdex@ininia.com">cekirdex@ininia.com</a> adresine yazabilirsiniz.</p>

<h2>Değişiklikler</h2>
<p>Bu politika güncellendiğinde sayfa üzerindeki "Son güncelleme" tarihi değiştirilir. Önemli değişikliklerde ayrıca e-posta ile bilgilendirilirsiniz.</p>

<p class="muted" style="margin-top:30px;font-size:.88rem">Bu metin genel bilgilendirme amaçlıdır; kişisel durumunuz için hukuki danışmanlık yerine geçmez. Detaylı sorular için bize yazın.</p>
@endsection
