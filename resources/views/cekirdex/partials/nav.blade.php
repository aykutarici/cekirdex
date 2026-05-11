@php
    $isLanding = request()->routeIs('cekirdex.landing');
    $home = route('cekirdex.landing');
    $isPricing = request()->routeIs('cekirdex.pricing');
    $isRest = request()->routeIs('cekirdex.for-restaurants');
    $isGuests = request()->routeIs('cekirdex.for-guests');
@endphp
<nav class="c-nav {{ $isLanding ? 'c-nav-landing' : '' }}" aria-label="Çekirdex sayfaları">
    <div class="c-nav-inner">
        <div class="c-nav-start">
            <a class="c-brand" href="{{ $home }}">
                <img class="c-brand-img" src="{{ asset('cekirdex/brand-logo.png') }}" width="40" height="40" alt="" aria-hidden="true" decoding="async">
                <span class="c-brand-text">Çekirdex</span>
            </a>
        </div>
        <div class="c-nav-main">
            <a href="{{ $home }}#modules">Ürün</a>
            <a href="{{ route('cekirdex.for-restaurants') }}" class="@if($isRest || $isGuests) is-active @endif">Çözümler</a>
            <a href="{{ route('cekirdex.pricing') }}" class="@if($isPricing) is-active @endif">Fiyatlandırma</a>
            <a href="https://ininia.com/blog" rel="noopener noreferrer">Kaynaklar</a>
            <a href="https://ininia.com/hakkimizda" rel="noopener noreferrer">Hakkımızda</a>
        </div>
        <div class="c-nav-actions">
            @auth('cekirdex')
                <a class="c-nav-secondary" href="{{ route('cekirdex.panel.dashboard') }}">Panele git</a>
            @else
                <a class="c-nav-secondary" href="{{ route('cekirdex.login') }}">Giriş yap</a>
                <a class="c-nav-cta" href="{{ route('cekirdex.contact') }}">
                    <span>Demo talep et</span>
                </a>
            @endauth
        </div>
    </div>
</nav>
