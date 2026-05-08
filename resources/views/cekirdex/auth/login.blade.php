@extends('cekirdex.layout')

@section('title', 'Giriş — Çekirdex Restoran Paneli')
@section('description', 'Çekirdex restoran paneline giriş yapın.')

@push('styles')
<style>
.auth-wrap { max-width: 460px; margin: 60px auto; padding: 0 22px; }
.auth-card { background: #fff; border: 1px solid var(--c-line); border-radius: 22px; padding: 40px 34px; box-shadow: 0 24px 60px -16px rgba(255,107,53,.18); }
.auth-card h1 { font-size: 1.6rem; margin-bottom: 6px; }
.auth-card p { margin-bottom: 26px; color: var(--c-text-soft); }
.auth-card label { display: block; font-size: .85rem; font-weight: 600; color: var(--c-text); margin: 14px 0 6px; }
.auth-card input {
    width: 100%; padding: 12px 14px; font-size: .96rem;
    border: 1.5px solid var(--c-line-2); border-radius: 11px;
    background: #fff; color: var(--c-text); font-family: inherit;
}
.auth-card input:focus { outline: none; border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(255,107,53,.12); }
.auth-card button { width: 100%; margin-top: 22px; }
.auth-card .meta { text-align: center; margin-top: 20px; font-size: .9rem; color: var(--c-text-soft); }
.auth-card .meta a { color: var(--c-accent-d); font-weight: 600; }
</style>
@endpush

@section('outer')
<div class="auth-wrap">
    <div class="auth-card">
        <h1>Tekrar hoş geldiniz</h1>
        <p>Çekirdex restoran panelinize giriş yapın.</p>

        @if(session('info'))<div class="alert-c" style="margin-bottom:18px"><i class="fas fa-circle-info"></i><span>{{ session('info') }}</span></div>@endif
        @if($errors->any())<div class="alert-c error" style="margin-bottom:18px"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>@endif

        <form action="{{ route('cekirdex.login.submit') }}" method="POST">
            @csrf
            <label for="email">E-posta</label>
            <input id="email" type="email" name="email" required autofocus value="{{ old('email') }}">
            <label for="password">Şifre</label>
            <input id="password" type="password" name="password" required>

            <button type="submit" class="btn-c"><i class="fas fa-arrow-right-to-bracket"></i> Giriş yap</button>
        </form>

        <div class="meta">
            Hesabınız yok mu? <a href="{{ route('cekirdex.register') }}">Restoranınızı ekleyin</a>
        </div>
    </div>
</div>
@endsection
