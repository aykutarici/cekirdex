<section id="ck-hero" class="ck-hero">
    <div class="ck-hero-bg" data-ck-hero-bg aria-hidden="true"></div>

    <span class="ck-floating-bean ck-hero-bean" style="left:8%;top:24%" aria-hidden="true"></span>
    <span class="ck-floating-bean ck-hero-bean" style="left:44%;top:16%;width:28px;height:40px" aria-hidden="true"></span>
    <span class="ck-floating-bean ck-hero-bean" style="right:8%;top:31%;width:30px;height:42px" aria-hidden="true"></span>

    <div class="ck-container">
        <div class="ck-hero-grid">
            <div class="ck-reveal">
                <span class="ck-pill"><i class="fas fa-seedling"></i> restoran · kafe · otel</span>
                <h1 class="ck-font-display ck-title mt-4">
                    Restoranınızın<br>
                    <span class="accent">dijital işletim sistemi.</span>
                </h1>
                <p class="ck-sub">
                    Çekirdex ile müşteriler QR’ı okutur, sipariş verir, garson çağırır, hesabı paylaşır, ödeme yapar ve çekirdek puanı kazanır.
                    Aylık ücret yok, kurulum ücretsiz; sadece işlem oldukça düşük komisyon.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('cekirdex.register') }}" class="ck-btn ck-btn-primary"><i class="fas fa-rocket"></i> Restoranını Ücretsiz Kaydet</a>
                    <a href="#demo" class="ck-btn ck-btn-ghost"><i class="fas fa-circle-play"></i> Canlı Demoyu Gör</a>
                </div>
                <div class="ck-bullets">
                    <span><i class="fas fa-check"></i> Uygulama indirme yok</span>
                    <span><i class="fas fa-check"></i> 5 dakikada kurulum</span>
                    <span><i class="fas fa-check"></i> Masa bazlı QR</span>
                    <span><i class="fas fa-check"></i> Çekirdek puanı ekosistemi</span>
                </div>
            </div>

            <div id="demo" class="ck-reveal" data-ck-phone-root>
                <div class="ck-phone">
                    <div class="ck-notch"></div>
                    <div class="ck-screen">
                        <img
                            src="{{ asset('cekirdex/landing/hero-phone.png') }}"
                            alt="Çekirdex mobil menü ekranı"
                            class="absolute inset-0 h-full w-full object-cover opacity-95"
                            loading="eager"
                            decoding="async"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a]/55 via-transparent to-transparent"></div>
                        <div data-ck-phone-screen class="absolute inset-0 p-4">
                            <p class="text-[.62rem] text-[#c9ac86]">Masa 13 • Atlas Restaurant</p>
                            <p class="mt-2 text-sm font-bold text-[#f4e2c7]">Menü & sipariş</p>
                            <div class="mt-3 space-y-2">
                                <div class="rounded-xl border border-white/10 bg-white/8 p-2 text-[.7rem] text-[#f4e2c7]">Trüf Mantarlı Risotto <span class="float-right text-[#d39a59]">390 ₺</span></div>
                                <div class="rounded-xl border border-white/10 bg-white/8 p-2 text-[.7rem] text-[#f4e2c7]">Espresso Tonic <span class="float-right text-[#d39a59]">185 ₺</span></div>
                            </div>
                        </div>
                        <div data-ck-phone-screen class="absolute inset-0 p-4 opacity-0">
                            <p class="text-sm font-bold text-[#f4e2c7]">Garson çağırma</p>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-[.68rem]">
                                <button type="button" class="rounded-xl border border-white/10 bg-white/8 py-2 text-[#ccb08a]">Su</button>
                                <button type="button" class="rounded-xl border border-white/10 bg-white/8 py-2 text-[#ccb08a]">Hesap</button>
                                <button type="button" class="rounded-xl border border-white/10 bg-white/8 py-2 text-[#ccb08a]">Peçete</button>
                                <button type="button" class="rounded-xl border border-white/10 bg-white/8 py-2 text-[#ccb08a]">Garson</button>
                            </div>
                        </div>
                        <div data-ck-phone-screen class="absolute inset-0 p-4 opacity-0">
                            <p class="text-sm font-bold text-[#f4e2c7]">Ödeme & çekirdek</p>
                            <div class="mt-4 rounded-xl border border-white/10 bg-white/6 p-3 text-[.7rem] text-[#ccb08a]">
                                Toplam: <strong class="text-[#f4e2c7]">790 ₺</strong>
                                <br>Ödeme sonrası +79 çekirdek puanı
                            </div>
                            <button type="button" class="mt-4 w-full rounded-xl bg-gradient-to-r from-[#bf7a3f] to-[#d6a061] py-2 text-[.72rem] font-bold text-white">Şimdi Öde</button>
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-center text-[.7rem] text-[#9f8461]">Kaydırdıkça ekran akışı değişir</p>
            </div>
        </div>

        <div class="ck-stats">
            <div class="ck-card ck-stat ck-reveal"><div class="v">%0</div><div class="l">Aylık ücret</div></div>
            <div class="ck-card ck-stat ck-reveal"><div class="v">5dk</div><div class="l">Kurulum</div></div>
            <div class="ck-card ck-stat ck-reveal"><div class="v">∞</div><div class="l">Masa & şube</div></div>
            <div class="ck-card ck-stat ck-reveal"><div class="v">Tek panel</div><div class="l">Sipariş • ödeme • sadakat</div></div>
        </div>
    </div>
</section>
