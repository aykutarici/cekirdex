@extends('cekirdex.layout')

@section('title', 'İletişim — Çekirdex')
@section('description', 'Çekirdex ile iletişime geçin. Demo talebi, restoranınız için kurulum desteği veya sorularınız için bize yazın.')
@section('canonical', url('/cekirdex/iletisim'))

@push('styles')
<style>
.c-form { background: #fff; border: 1px solid var(--c-line); border-radius: 18px; padding: 32px 28px; box-shadow: var(--c-shadow); }
.c-form .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 700px) { .c-form .row { grid-template-columns: 1fr; } }
.c-form label { display: block; font-size: .85rem; font-weight: 600; color: var(--c-text); margin: 14px 0 6px; }
.c-form input, .c-form textarea, .c-form select {
    width: 100%; padding: 12px 14px; font-size: .96rem;
    border: 1.5px solid var(--c-line-2); border-radius: 11px;
    background: #fff; color: var(--c-text); font-family: inherit;
    transition: border-color .15s;
}
.c-form input:focus, .c-form textarea:focus, .c-form select:focus { outline: none; border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(255,107,53,.12); }
.c-form textarea { min-height: 120px; resize: vertical; }
.c-form button { margin-top: 22px; }
.c-form .err { color: #b91c1c; font-size: .82rem; margin-top: 4px; }
.c-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 32px; }
@media (max-width: 700px) { .c-info-grid { grid-template-columns: 1fr; } }
.c-info { background: #fff8f1; border: 1px solid #fde9d6; border-radius: 14px; padding: 20px 22px; }
.c-info i { color: var(--c-accent-d); font-size: 1.3rem; margin-bottom: 8px; display: block; }
.c-info strong { display: block; color: var(--c-text); font-size: 1rem; }
.c-info span { color: var(--c-text-soft); font-size: .92rem; }
.honey { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
</style>
@endpush

@section('content')
<span class="c-eyebrow"><i class="fas fa-headset"></i> İletişim</span>
<h1 style="font-size:clamp(1.8rem,3.5vw,2.4rem);margin-bottom:14px">Demo, soru veya iş birliği — bize yazın.</h1>
<p style="margin-bottom:30px">Genellikle 1 iş günü içinde dönüş yapıyoruz. Aciliyet varsa e-posta veya WhatsApp ile ulaşabilirsiniz.</p>

<div class="c-info-grid">
    <div class="c-info">
        <i class="fas fa-envelope"></i>
        <strong>cekirdex@ininia.com</strong>
        <span>İş ve destek için</span>
    </div>
    <div class="c-info">
        <i class="fas fa-globe"></i>
        <strong>İninia ekosistemi</strong>
        <span><a href="{{ route('home') }}">ininia.com</a> · İstanbul, Türkiye</span>
    </div>
</div>

@if($errors->any())
<div class="alert-c error" style="margin-bottom:20px"><i class="fas fa-circle-exclamation"></i>
    <span>
        Lütfen formdaki hataları düzeltin:
        <ul style="margin-top:6px;padding-left:18px">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </span>
</div>
@endif

<form action="{{ route('cekirdex.contact.submit') }}" method="POST" class="c-form" autocomplete="on">
    @csrf

    {{-- Honeypot --}}
    <input class="honey" type="text" name="website" tabindex="-1" autocomplete="off">

    <div class="row">
        <div>
            <label for="name">Adınız Soyadınız *</label>
            <input id="name" type="text" name="name" required maxlength="120" value="{{ old('name') }}">
        </div>
        <div>
            <label for="email">E-posta *</label>
            <input id="email" type="email" name="email" required maxlength="160" value="{{ old('email') }}">
        </div>
    </div>

    <div class="row">
        <div>
            <label for="phone">Telefon</label>
            <input id="phone" type="tel" name="phone" maxlength="32" value="{{ old('phone') }}">
        </div>
        <div>
            <label for="city">Şehir</label>
            <input id="city" type="text" name="city" maxlength="80" value="{{ old('city') }}">
        </div>
    </div>

    <label for="restaurant_name">Restoran adı (varsa)</label>
    <input id="restaurant_name" type="text" name="restaurant_name" maxlength="160" value="{{ old('restaurant_name') }}">

    <label for="subject">Konu</label>
    <input id="subject" type="text" name="subject" maxlength="200" placeholder="Örn. Demo talebi, fiyatlandırma sorusu" value="{{ old('subject') }}">

    <label for="message">Mesajınız *</label>
    <textarea id="message" name="message" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>

    <button type="submit" class="btn-c"><i class="fas fa-paper-plane"></i> Gönder</button>
    <p style="font-size:.82rem;color:var(--c-muted);margin-top:14px">
        Gönderdiğinizde KVKK kapsamında verilerinizin işlenmesini kabul etmiş sayılırsınız. Detaylar için
        <a href="{{ route('cekirdex.privacy') }}">gizlilik politikamızı</a> okuyun.
    </p>
</form>
@endsection
