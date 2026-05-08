@php
    $isLanding = request()->routeIs('cekirdex.landing');
    $isPricing = request()->routeIs('cekirdex.pricing');
    $isContact = request()->routeIs('cekirdex.contact');
    $isRest    = request()->routeIs('cekirdex.for-restaurants');
    $isGuests  = request()->routeIs('cekirdex.for-guests');
@endphp
<nav class="c-nav {{ $isLanding ? 'c-nav-landing' : '' }}" aria-label="Çekirdex sayfaları">
    <div class="c-nav-inner">
        <div class="c-nav-start">
            <a class="c-brand" href="{{ route('cekirdex.landing') }}">
                <span class="c-brand-mark" aria-hidden="true"></span>
                Çekirdex
            </a>
        </div>
        <div class="c-nav-main">
            <a href="{{ route('cekirdex.for-restaurants') }}" class="@if($isRest) is-active @endif">Restoranlar</a>
            <a href="{{ route('cekirdex.for-guests') }}"      class="@if($isGuests) is-active @endif">Müşteriler</a>
            <a href="{{ route('cekirdex.pricing') }}"         class="@if($isPricing) is-active @endif">Fiyatlandırma</a>
            <a href="{{ route('cekirdex.contact') }}"         class="@if($isContact) is-active @endif">İletişim</a>
        </div>
        <div class="c-nav-actions">
            @auth('cekirdex')
                <a class="c-nav-secondary" href="{{ route('cekirdex.panel.dashboard') }}">Panele Git</a>
            @else
                <a class="c-nav-secondary" href="{{ route('cekirdex.login') }}">Giriş</a>
                <a class="c-nav-cta" href="{{ route('cekirdex.register') }}">
                    <i class="fas fa-rocket c-nav-cta-icon" aria-hidden="true"></i>
                    <span>Restoranını Kaydet</span>
                </a>
            @endauth
        </div>
    </div>
</nav>
