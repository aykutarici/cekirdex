{{-- Güven şeridi --}}
<section class="ck-section ck-trust-section ck-reveal" id="trust">
    <div class="ck-container">
        <p class="ck-trust-heading">Türkiye'nin önde gelen restoran ve otelleri Çekirdex ile büyüyor.</p>
        <div class="ck-trust-logos ck-trust-logos--brands" aria-label="İş ortakları">
            @foreach (['BIGCHEFS', 'KAHVE DÜNYASI', 'MADO', 'divan', 'NUSR-ET', 'THE MARMARA'] as $brand)
                <span class="ck-trust-brand">{{ $brand }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- Modüller --}}
<section class="ck-section ck-section--soft ck-reveal" id="modules">
    <div class="ck-container">
        <div class="ck-head ck-head--left">
            <span class="ck-pill ck-pill--accent">Her şey tek platformda</span>
            <h2 class="ck-font-display mt-4">İhtiyacınız olan tüm modüller</h2>
            <p>Ödeme, sadakat, operasyon ve analitiği tek çatı altında birleştirin.</p>
        </div>

        <div class="ck-module-grid">
            <article class="ck-card ck-module-card">
                <div class="ck-module-icon" style="--m: 242 106 61"><i class="fas fa-mobile-screen-button" aria-hidden="true"></i></div>
                <h3>QR'dan ödeme</h3>
                <p>Masa QR ile güvenli tahsilat; hesap bölme ve dijital ödeme seçenekleri.</p>
            </article>
            <article class="ck-card ck-module-card">
                <div class="ck-module-icon" style="--m: 137 167 146"><i class="fas fa-heart" aria-hidden="true"></i></div>
                <h3>Sadakat yönetimi</h3>
                <p>Çekirdek puanı ve kampanyalarla misafiri bağlayın; ekosistemde harcanabilir puan.</p>
            </article>
            <article class="ck-card ck-module-card">
                <div class="ck-module-icon" style="--m: 214 160 97"><i class="fas fa-layer-group" aria-hidden="true"></i></div>
                <h3>Operasyon yönetimi</h3>
                <p>Mutfak ekranı, masa akışı ve garson süreçleri tek panelden yönetilir.</p>
            </article>
            <article class="ck-card ck-module-card">
                <div class="ck-module-icon" style="--m: 99 102 241"><i class="fas fa-chart-line" aria-hidden="true"></i></div>
                <h3>Analiz &amp; raporlama</h3>
                <p>Şube ve kanal bazlı performans; geliri ve sadakat etkisini ölçün.</p>
            </article>
        </div>
    </div>
</section>

{{-- Dashboard --}}
<section class="ck-section ck-reveal" id="dashboard">
    <div class="ck-container">
        <div class="ck-dash-showcase">
            <div class="ck-dash-showcase-visual" aria-hidden="true">
                <div class="ck-dash-ui">
                    <div class="ck-dash-ui-top">
                        <span class="ck-dash-dot"></span><span class="ck-dash-dot"></span><span class="ck-dash-dot"></span>
                        <span class="ck-dash-ui-title">Canlı özet</span>
                    </div>
                    <div class="ck-dash-ui-metrics">
                        <div class="ck-dash-metric"><span class="k">Günlük gelir</span><span class="v">₺128.400</span></div>
                        <div class="ck-dash-metric"><span class="k">Misafir</span><span class="v">842</span></div>
                    </div>
                    <div class="ck-dash-chart">
                        <svg viewBox="0 0 320 120" class="ck-dash-chart-svg" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="ckdashFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#f26a3d" stop-opacity="0.28"/>
                                    <stop offset="100%" stop-color="#f26a3d" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <path d="M0,90 L40,70 L80,85 L120,45 L160,55 L200,35 L240,50 L280,25 L320,40 L320,120 L0,120 Z" fill="url(#ckdashFill)"/>
                            <path d="M0,90 L40,70 L80,85 L120,45 L160,55 L200,35 L240,50 L280,25 L320,40" fill="none" stroke="#f26a3d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="ck-dash-donut-row">
                        <div class="ck-dash-donut">
                            <svg viewBox="0 0 72 72" aria-hidden="true">
                                <circle cx="36" cy="36" r="28" fill="none" stroke="#2a3038" stroke-width="8"/>
                                <circle cx="36" cy="36" r="28" fill="none" stroke="#f26a3d" stroke-width="8" stroke-dasharray="110 176" stroke-linecap="round" transform="rotate(-90 36 36)"/>
                            </svg>
                            <span>Kanal</span>
                        </div>
                        <div class="ck-dash-bars">
                            <div class="ck-dash-bar" style="height:45%"></div>
                            <div class="ck-dash-bar" style="height:72%"></div>
                            <div class="ck-dash-bar" style="height:58%"></div>
                            <div class="ck-dash-bar" style="height:88%"></div>
                        </div>
                    </div>
                    <div class="ck-dash-footer-note">Aktif masalar · anlık güncellenir</div>
                </div>
            </div>
            <div class="ck-dash-showcase-copy">
                <span class="ck-pill ck-pill--accent">Canlı veriler</span>
                <h2 class="ck-font-display mt-4">İşinizi anlık verilerle yönetin</h2>
                <p class="ck-dash-intro">Performansı tek bakışta görün; ekibiniz aynı panel üzerinden hareket eder.</p>
                <ul class="ck-dash-list">
                    <li><i class="fas fa-check" aria-hidden="true"></i> Tüm şubelerinizi tek ekrandan yönetin.</li>
                    <li><i class="fas fa-check" aria-hidden="true"></i> Gerçek zamanlı satış, sipariş ve masa durumunu takip edin.</li>
                    <li><i class="fas fa-check" aria-hidden="true"></i> Performansı artıran akıllı içgörüler alın.</li>
                </ul>
                <a href="{{ route('cekirdex.for-restaurants') }}" class="ck-dash-link">Tüm özellikleri keşfet <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>

{{-- Metrik şeridi --}}
<section class="ck-stats-wrap ck-reveal" id="stats" aria-label="Öne çıkan rakamlar">
    <div class="ck-container">
        <div class="ck-stats-band-card">
            <div class="ck-stats-band-grid">
                <div class="ck-stats-band-item">
                    <i class="fas fa-store" aria-hidden="true"></i>
                    <div>
                        <strong>500+</strong>
                        <span>Restoran &amp; otel</span>
                    </div>
                </div>
                <div class="ck-stats-band-item">
                    <i class="fas fa-face-smile" aria-hidden="true"></i>
                    <div>
                        <strong>2M+</strong>
                        <span>Mutlu misafir</span>
                    </div>
                </div>
                <div class="ck-stats-band-item">
                    <i class="fas fa-turkish-lira-sign" aria-hidden="true"></i>
                    <div>
                        <strong>₺2.5B+</strong>
                        <span>İşlem hacmi</span>
                    </div>
                </div>
                <div class="ck-stats-band-item">
                    <i class="fas fa-thumbs-up" aria-hidden="true"></i>
                    <div>
                        <strong>%98</strong>
                        <span>Müşteri memnuniyeti</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Sadakat ekosistemi --}}
<section class="ck-section ck-section--soft ck-reveal" id="loyalty">
    <div class="ck-container">
        <div class="ck-loyalty-split">
            <div class="ck-loyalty-copy">
                <span class="ck-pill">Çekirdek ekosistemi</span>
                <h2 class="ck-font-display mt-4">Sadakat puanları sınırları kaldırır</h2>
                <p class="ck-loyalty-lead">
                    Çekirdex ekosistemindeki tüm restoran ve otellerde kazanılan ve harcanabilen puanlarla misafirin cebinde, deneyimi her yerde.
                </p>
            </div>
            <div class="ck-loyalty-visual">
                <img
                    class="ck-loyalty-img"
                    src="{{ asset('cekirdex/loyalty-ecosystem.png') }}"
                    width="1200"
                    height="675"
                    alt="Çekirdex sadakat ekosistemi: merkezde marka, orbit çizgileri ve bağlı işletme ikonları ile ağ görünümü."
                    loading="lazy"
                    decoding="async"
                >
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="ck-section ck-reveal">
    <div class="ck-container">
        <div class="ck-cta-banner">
            <h2 class="ck-font-display">Çekirdex ile operasyonunuzu büyütmeye hazır mısınız?</h2>
            <p>Hemen demo talep edin, ekibimiz size özel çözümleri anlatsın.</p>
            <a href="{{ route('cekirdex.contact') }}" class="ck-btn ck-btn-primary">Demo talep et</a>
        </div>
    </div>
</section>

{{-- SSS --}}
<section class="ck-section ck-faq-section ck-reveal" id="faq">
    <div class="ck-container">
        <div class="ck-faq-head">
            <h2 class="ck-font-display">Merak ettikleriniz</h2>
            <p>Sık sorulan sorulara göz atın; ayrıntı için iletişime geçebilirsiniz.</p>
        </div>
        @php
            $faqs = [
                ['q' => 'Çekirdex hangi işletmeler için uygun?', 'a' => 'Restoran, kafe, otel outlet ve çoklu şubeli işletmeler için uygundur. Tek panelden QR ödeme, sadakat ve operasyon süreçlerini yönetebilirsiniz.'],
                ['q' => 'QR ödeme nasıl çalışıyor?', 'a' => 'Misafir masa QR kodunu tarar; menü ve ödeme tarayıcı üzerinden güvenli ödeme kuruluşlarıyla tamamlanır. Uygulama indirmesi gerekmez.'],
                ['q' => 'Sadakat sistemi nasıl entegre ediliyor?', 'a' => 'Çekirdek puanı işletme kurallarına göre tanımlanır; ekosistem ortaklarında biriktirme ve harcama tek cüzdanda takip edilir.'],
                ['q' => 'Kurulum süreci ne kadar sürüyor?', 'a' => 'Çoğu işletmede temel kurulum dakikalar içinde tamamlanır. Şube ve menü yapınıza göre ekibimiz yönlendirme sağlar.'],
                ['q' => 'Verilerim güvende mi?', 'a' => 'Ödeme verileri PCI uyumlu altyapı üzerinden işlenir; kart bilgileri işletmenizde saklanmaz. Erişim rolleri panel üzerinden yönetilir.'],
                ['q' => 'Fiyatlandırma nasıl yapılıyor?', 'a' => 'Sabit aylık yerine işlem bazlı şeffaf komisyon modeli sunulur. İşletmenize özel oran için iletişime geçebilirsiniz.'],
            ];
            $half = (int) ceil(count($faqs) / 2);
            $faqCols = [array_slice($faqs, 0, $half), array_slice($faqs, $half)];
        @endphp
        <div class="ck-faq-columns">
            @foreach ($faqCols as $col)
                <div class="ck-faq-col">
                    @foreach ($col as $faq)
                        <details class="ck-faq-item">
                            <summary>{{ $faq['q'] }}</summary>
                            <div class="body">{{ $faq['a'] }}</div>
                        </details>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
