<footer class="c-foot">
    <div class="c-foot-inner">
        <div>
            <a class="c-brand" href="{{ route('cekirdex.landing') }}" style="margin-bottom:14px">
                <img class="c-brand-img" src="{{ asset('cekirdex/brand-logo.png') }}" width="40" height="40" alt="" aria-hidden="true" decoding="async">
                <span class="c-brand-text">Çekirdex</span>
            </a>
            <div class="c-foot-social">
                <a href="https://www.linkedin.com/company/ininia" class="c-foot-social-link" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                <a href="https://ininia.com" class="c-foot-social-link" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="https://ininia.com" class="c-foot-social-link" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
            </div>
            <p style="font-size:.9rem;margin-top:14px;max-width:320px">
                QR ödeme, sadakat ve operasyonu tek platformda birleştiren modern hospitality altyapısı.
            </p>
        </div>
        <div>
            <h4>Ürün</h4>
            <ul>
                <li><a href="{{ route('cekirdex.landing') }}#modules">Modüller</a></li>
                <li><a href="{{ route('cekirdex.for-restaurants') }}">Restoranlar için</a></li>
                <li><a href="{{ route('cekirdex.for-guests') }}">Misafirler için</a></li>
                <li><a href="{{ route('cekirdex.pricing') }}">Fiyatlandırma</a></li>
            </ul>
        </div>
        <div>
            <h4>Şirket</h4>
            <ul>
                <li><a href="https://ininia.com" rel="noopener noreferrer">İninia</a></li>
                <li><a href="https://ininia.com/hakkimizda" rel="noopener noreferrer">Hakkımızda</a></li>
                <li><a href="https://ininia.com/blog" rel="noopener noreferrer">Blog</a></li>
            </ul>
        </div>
        <div>
            <h4>Yasal</h4>
            <ul>
                <li><a href="{{ route('cekirdex.privacy') }}">Gizlilik</a></li>
                <li><a href="{{ route('cekirdex.terms') }}">Kullanım koşulları</a></li>
            </ul>
        </div>
        <div>
            <h4>İletişim</h4>
            <ul>
                <li><a href="{{ route('cekirdex.contact') }}">Bize yazın</a></li>
                <li><a href="mailto:cekirdex@ininia.com">cekirdex@ininia.com</a></li>
            </ul>
        </div>
    </div>
    <div class="c-foot-bottom">
        © {{ date('Y') }} Çekirdex · Bir <a href="https://ininia.com" rel="noopener noreferrer" style="color:var(--c-accent-d);font-weight:600">İninia</a> ürünüdür.
    </div>
</footer>
