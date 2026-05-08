<footer class="c-foot">
    <div class="c-foot-inner">
        <div>
            <a class="c-brand" href="{{ route('cekirdex.landing') }}" style="margin-bottom:14px">
                <span class="c-brand-mark" aria-hidden="true"></span>
                Çekirdex
            </a>
            <p style="font-size:.9rem;margin-top:14px;max-width:320px">
                Restoranlar için yeni nesil QR sipariş, ödeme ve sadakat platformu.
                Aylık ücret yok — sadece işlem komisyonu.
            </p>
        </div>
        <div>
            <h4>Ürün</h4>
            <ul>
                <li><a href="{{ route('cekirdex.for-restaurants') }}">Restoranlar için</a></li>
                <li><a href="{{ route('cekirdex.for-guests') }}">Müşteriler için</a></li>
                <li><a href="{{ route('cekirdex.pricing') }}">Fiyatlandırma</a></li>
                <li><a href="{{ route('cekirdex.register') }}">Hemen başla</a></li>
            </ul>
        </div>
        <div>
            <h4>Destek</h4>
            <ul>
                <li><a href="{{ route('cekirdex.contact') }}">İletişim</a></li>
                <li><a href="mailto:cekirdex@ininia.com">cekirdex@ininia.com</a></li>
                <li><a href="{{ route('cekirdex.privacy') }}">Gizlilik</a></li>
                <li><a href="{{ route('cekirdex.terms') }}">Kullanım Koşulları</a></li>
            </ul>
        </div>
        <div>
            <h4>Şirket</h4>
            <ul>
                <li><a href="{{ route('home') }}">İninia</a></li>
                <li><a href="{{ route('about') }}">Hakkımızda</a></li>
                <li><a href="{{ route('blog.index') }}">Blog</a></li>
            </ul>
        </div>
    </div>
    <div class="c-foot-bottom">
        © {{ date('Y') }} Çekirdex · Bir <a href="{{ route('home') }}" style="color:var(--c-accent-d);font-weight:600">İninia</a> ürünüdür.
    </div>
</footer>
