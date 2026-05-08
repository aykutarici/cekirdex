@extends('cekirdex.layout')

@section('title', 'Çekirdex — Restoran ve oteller için QR menü, ödeme ve çekirdek sadakat')
@section('description', 'Çekirdex; restoran, kafe ve oteller için QR menü, masadan sipariş, garson çağırma, hesap paylaşma, dijital ödeme ve çekirdek puanı sadakatını tek platformda sunar. Müşteriler ödedikçe puan biriktirir, ortak işletmelerde harcar. Aylık ücret yok; düşük işlem komisyonu.')
@section('canonical', route('cekirdex.landing'))

@push('meta')
<meta property="og:type"        content="website">
<meta property="og:title"       content="Çekirdex — Restoran ve oteller için dijital işletim sistemi">
<meta property="og:description" content="QR menüden ödemeye tek platform. Çekirdek puanı sadakati, kampanya ve raporlama. Aylık ücret yok.">
<meta property="og:url"         content="{{ url('/cekirdex') }}">
<meta property="og:locale"      content="tr_TR">
<meta property="og:site_name"   content="Çekirdex">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="Çekirdex — Restoran ve oteller için dijital işletim sistemi">
<meta name="twitter:description" content="QR menü, sipariş, ödeme ve çekirdek sadakat tek panelde.">
<meta name="keywords" content="qr menü, restoran yazılımı, otel restoran qr, masadan sipariş, dijital ödeme restoran, sadakat sistemi, çekirdek puanı, garson çağırma, mutfak ekranı">

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SoftwareApplication",
  "name": "Çekirdex",
  "applicationCategory": "BusinessApplication",
  "applicationSubCategory": "RestaurantManagement",
  "description": "Restoran ve oteller için QR menü, sipariş, dijital ödeme ve çekirdek puanı sadakat platformu.",
  "url": "{{ url('/cekirdex') }}",
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
  "url": "{{ url('/cekirdex') }}",
  "logo": "{{ asset('cekirdex/favicon.svg') }}",
  "parentOrganization": { "@@type": "Organization", "name": "İninia", "url": "{{ url('/') }}" }
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    { "@@type": "Question", "name": "Aylık veya kurulum ücreti var mı?", "acceptedAnswer": { "@@type": "Answer", "text": "Hayır. Temel platform ücretsizdir; yalnızca platform üzerinden alınan ödemelerde işlem komisyonu uygulanır." } },
    { "@@type": "Question", "name": "Çekirdek puanları başka işletmelerde geçerli mi?", "acceptedAnswer": { "@@type": "Answer", "text": "Evet. Müşteriler ödeme yaptıkça çekirdek biriktirip ortak işletmelerde harcayabilir; kapsam iş ortaklıklarına göre genişler." } },
    { "@@type": "Question", "name": "Müşteri uygulama indirmeli mi?", "acceptedAnswer": { "@@type": "Answer", "text": "Hayır. QR ile tarayıcıda açılan mobil web uygulaması kullanılır." } }
  ]
}
</script>
@endpush

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
@vite(['resources/css/cekirdex-landing.css'])
@endpush

@push('scripts')
@vite(['resources/js/cekirdex-landing.js'])
@endpush

@section('outer')
<div class="ck-lp ck-lp-grain">
    @include('cekirdex.landing._hero')
    @include('cekirdex.landing._sections')
</div>
@endsection
