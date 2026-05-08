@extends('cekirdex.layout')

@section('title', 'Restoranınızı kaydedin — Çekirdex')
@section('description', 'Çekirdex\'e restoranınızı 5 dakikada kaydedin. Aylık ücret yok, kart bilgisi gerekmez.')

@push('styles')
<style>
.auth-wrap { max-width: 540px; margin: 50px auto; padding: 0 22px; }
.auth-card { background: #fff; border: 1px solid var(--c-line); border-radius: 22px; padding: 40px 34px; box-shadow: 0 24px 60px -16px rgba(255,107,53,.18); }
.auth-card h1 { font-size: 1.7rem; margin-bottom: 8px; }
.auth-card .subtitle { color: var(--c-text-soft); margin-bottom: 24px; }
.auth-card label { display: block; font-size: .85rem; font-weight: 600; color: var(--c-text); margin: 14px 0 6px; }
.auth-card input, .auth-card select {
    width: 100%; padding: 12px 14px; font-size: .96rem;
    border: 1.5px solid var(--c-line-2); border-radius: 11px;
    background: #fff; color: var(--c-text); font-family: inherit;
}
.auth-card input:focus { outline: none; border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(255,107,53,.12); }
.auth-card .row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 540px) { .auth-card .row { grid-template-columns: 1fr; } }
.auth-card button { width: 100%; margin-top: 22px; }
.auth-card .terms { display: flex; gap: 8px; margin-top: 18px; align-items: flex-start; font-size: .88rem; color: var(--c-text-soft); }
.auth-card .terms input { width: auto; margin-top: 3px; }
.auth-card .meta { text-align: center; margin-top: 20px; font-size: .9rem; color: var(--c-text-soft); }
.auth-card .meta a { color: var(--c-accent-d); font-weight: 600; }
.benefits { display: flex; flex-wrap: wrap; gap: 10px; margin: 16px 0 24px; }
.benefits span { font-size: .78rem; padding: 5px 12px; background: #fff8f1; border: 1px solid #ffd9c2; border-radius: 99px; color: var(--c-accent-d); font-weight: 600; }
</style>
@endpush

@section('outer')
<div class="auth-wrap">
    <div class="auth-card">
        <span class="c-eyebrow"><i class="fas fa-rocket"></i> 5 dakikada kurulum</span>
        <h1>Restoranınızı Çekirdex'e ekleyin</h1>
        <p class="subtitle">Aylık ücret yok, kart bilgisi gerekmez. Sadece müşteri adına ödeme aldığınızda komisyon alınır.</p>

        <div class="benefits">
            <span><i class="fas fa-check"></i> Sınırsız ürün</span>
            <span><i class="fas fa-check"></i> Sınırsız masa</span>
            <span><i class="fas fa-check"></i> Mutfak ekranı dahil</span>
            <span><i class="fas fa-check"></i> Türkçe destek</span>
        </div>

        @if($errors->any())<div class="alert-c error" style="margin-bottom:18px"><i class="fas fa-circle-exclamation"></i>
            <span>
                @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
            </span>
        </div>@endif

        <form action="{{ route('cekirdex.register.submit') }}" method="POST">
            @csrf

            <h3 style="font-size:1rem;color:var(--c-text);margin-top:8px"><i class="fas fa-store" style="color:var(--c-accent-d);margin-right:6px"></i> Restoran bilgileri</h3>
            <label for="restaurant_name">Restoran / işletme adı *</label>
            <input id="restaurant_name" type="text" name="restaurant_name" required maxlength="160" value="{{ old('restaurant_name') }}">

            <div class="row">
                <div>
                    <label for="city">Şehir</label>
                    <input id="city" type="text" name="city" maxlength="80" value="{{ old('city') }}">
                </div>
                <div>
                    <label for="phone">Telefon</label>
                    <input id="phone" type="tel" name="phone" maxlength="32" value="{{ old('phone') }}">
                </div>
            </div>

            <h3 style="font-size:1rem;color:var(--c-text);margin-top:22px"><i class="fas fa-user" style="color:var(--c-accent-d);margin-right:6px"></i> Yönetici hesabınız</h3>
            <label for="name">Ad Soyad *</label>
            <input id="name" type="text" name="name" required maxlength="120" value="{{ old('name') }}">

            <label for="email">E-posta *</label>
            <input id="email" type="email" name="email" required maxlength="160" value="{{ old('email') }}">

            <div class="row">
                <div>
                    <label for="password">Şifre * (en az 6 karakter)</label>
                    <input id="password" type="password" name="password" required minlength="6">
                </div>
                <div>
                    <label for="password_confirmation">Şifre tekrar *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="6">
                </div>
            </div>

            <label class="terms">
                <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
                <span>
                    <a href="{{ route('cekirdex.terms') }}" target="_blank">Kullanım koşullarını</a> ve
                    <a href="{{ route('cekirdex.privacy') }}" target="_blank">gizlilik politikasını</a> okudum, kabul ediyorum.
                </span>
            </label>

            <button type="submit" class="btn-c"><i class="fas fa-rocket"></i> Hesabımı oluştur</button>
        </form>

        <div class="meta">
            Zaten hesabınız var mı? <a href="{{ route('cekirdex.login') }}">Giriş yapın</a>
        </div>
    </div>
</div>
@endsection
