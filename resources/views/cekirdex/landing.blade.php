@extends('cekirdex.layout')

@section('title', 'Çekirdex — Restoran ve oteller için sadakatin yeni çekirdeği')
@section('description', 'Çekirdex; QR’dan ödeme, sadakat puanı, operasyon yönetimi ve raporlamayı tek platformda toplar. Restoran, kafe ve oteller için modern hospitality platformu.')
@section('canonical', route('cekirdex.landing'))

@push('meta')
<meta property="og:type"        content="website">
<meta property="og:title"       content="Çekirdex — Restoran ve oteller için dijital işletim sistemi">
<meta property="og:description" content="QR'dan ödeme, sadakat, operasyon ve raporlama tek platformda. Misafir deneyimini hızlandırır, geliri artırır.">
<meta property="og:url"         content="{{ url('/') }}">
<meta property="og:locale"      content="tr_TR">
<meta property="og:site_name"   content="Çekirdex">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Çekirdex — Restoran ve oteller için dijital işletim sistemi">
<meta name="twitter:description" content="Modern hospitality platformu: QR ödeme, sadakat ve operasyon tek panelde.">
<meta name="keywords" content="qr menü, restoran yazılımı, otel restoran qr, masadan sipariş, dijital ödeme restoran, sadakat sistemi, çekirdek puanı, garson çağırma, mutfak ekranı">

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SoftwareApplication",
  "name": "Çekirdex",
  "applicationCategory": "BusinessApplication",
  "applicationSubCategory": "RestaurantManagement",
  "description": "Çekirdex; QR'dan ödeme, sadakat puanı, operasyon yönetimi ve raporlamayı tek platformda toplayan hospitality yazılımıdır.",
  "url": "{{ url('/') }}",
  "operatingSystem": "Web",
  "inLanguage": ["tr-TR"],
  "offers": {
    "@@type": "Offer",
    "price": "0",
    "priceCurrency": "TRY",
    "description": "Aylık ücret yok — işlem başına düşük komisyon"
  }
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Organization",
  "name": "Çekirdex",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('cekirdex/favicon.svg') }}",
  "parentOrganization": { "@@type": "Organization", "name": "İninia", "url": "{{ url('/') }}" }
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    { "@@type": "Question", "name": "Çekirdex hangi işletmeler için uygun?", "acceptedAnswer": { "@@type": "Answer", "text": "Restoran, kafe, otel outlet ve çoklu şubeli işletmeler için uygundur. Tek panelden QR ödeme, sadakat ve operasyon süreçlerini yönetebilirsiniz." } },
    { "@@type": "Question", "name": "QR ödeme nasıl çalışıyor?", "acceptedAnswer": { "@@type": "Answer", "text": "Misafir masa QR kodunu tarar; menü ve ödeme tarayıcı üzerinden güvenli ödeme kuruluşlarıyla tamamlanır. Uygulama indirmesi gerekmez." } },
    { "@@type": "Question", "name": "Sadakat sistemi nasıl entegre ediliyor?", "acceptedAnswer": { "@@type": "Answer", "text": "Çekirdek puanı işletme kurallarına göre tanımlanır; ekosistem ortaklarında biriktirme ve harcama tek cüzdanda takip edilir." } },
    { "@@type": "Question", "name": "Kurulum süreci ne kadar sürüyor?", "acceptedAnswer": { "@@type": "Answer", "text": "Çoğu işletmede temel kurulum dakikalar içinde tamamlanır. Şube ve menü yapınıza göre ekibimiz yönlendirme sağlar." } },
    { "@@type": "Question", "name": "Verilerim güvende mi?", "acceptedAnswer": { "@@type": "Answer", "text": "Ödeme verileri PCI uyumlu altyapı üzerinden işlenir; kart bilgileri işletmenizde saklanmaz. Erişim rolleri panel üzerinden yönetilir." } },
    { "@@type": "Question", "name": "Fiyatlandırma nasıl yapılıyor?", "acceptedAnswer": { "@@type": "Answer", "text": "Sabit aylık yerine işlem bazlı şeffaf komisyon modeli sunulur. İşletmenize özel oran için iletişime geçebilirsiniz." } }
  ]
}
</script>
@endpush

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/cekirdex-landing.css'])
@endpush

@push('scripts')
@vite(['resources/js/cekirdex-landing.js'])
@endpush

@section('outer')
<div class="ck-lp">
    @include('cekirdex.landing._hero')
    @include('cekirdex.landing._sections')
</div>
@endsection
