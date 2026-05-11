<section id="ck-hero" class="ck-hero ck-reveal ck-reveal--visible" aria-labelledby="ck-hero-heading">
    <div class="ck-hero-bg" aria-hidden="true"></div>
    <div class="ck-hero-grid-pattern" aria-hidden="true"></div>

    <div class="ck-container ck-hero-inner">
        <div class="ck-hero-main">
            <div class="ck-hero-copy">
                <h1 id="ck-hero-heading" class="ck-hero-headline">
                    Restoran ve oteller için<br>
                    <span class="ck-hero-title-line">
                        <span class="ck-hero-accent-word">sadakatin</span> yeni çekirdeği
                    </span>
                </h1>
                <p class="ck-hero-lead">
                    Çekirdex; QR'dan ödeme, sadakat puanı, operasyon yönetimi ve raporlamayı tek platformda toplar.
                    Misafir deneyimini hızlandırır, işletmenizin gelirini artırır.
                </p>
                <div class="ck-hero-actions">
                    <a href="{{ route('cekirdex.contact') }}" class="ck-btn ck-btn-primary">Demo talep et</a>
                    <a href="#modules" class="ck-btn ck-btn-ghost">Nasıl çalışır?</a>
                </div>
            </div>

            <div class="ck-hero-brand-wrap">
                <div class="ck-hero-ecosystem" aria-hidden="true">
                    <img
                        class="ck-hero-ecosystem-img"
                        src="{{ asset('cekirdex/hero-ecosystem.png') }}"
                        width="1024"
                        height="768"
                        alt=""
                        decoding="async"
                        loading="eager"
                    >
                </div>
            </div>
        </div>

        <div class="ck-hero-chips" role="list">
            <div class="ck-hero-chip" role="listitem">
                <span class="ck-hero-chip-icon"><i class="fas fa-qrcode" aria-hidden="true"></i></span>
                <span>QR'dan ödeme</span>
            </div>
            <div class="ck-hero-chip" role="listitem">
                <span class="ck-hero-chip-icon"><i class="fas fa-seedling" aria-hidden="true"></i></span>
                <span>Sadakat &amp; puan</span>
            </div>
            <div class="ck-hero-chip" role="listitem">
                <span class="ck-hero-chip-icon"><i class="fas fa-chart-simple" aria-hidden="true"></i></span>
                <span>Raporlama</span>
            </div>
            <div class="ck-hero-chip" role="listitem">
                <span class="ck-hero-chip-icon"><i class="fas fa-sliders" aria-hidden="true"></i></span>
                <span>Operasyon yönetimi</span>
            </div>
        </div>
    </div>
</section>
