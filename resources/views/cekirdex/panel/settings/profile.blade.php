@extends('cekirdex.panel.layout')

@section('title', 'Profilim')

@section('content')
<div class="pp-head">
    <div>
        <h1>Profilim</h1>
        <div class="sub">Hesap bilgilerinizi ve şifrenizi yönetin.</div>
    </div>
</div>

<div class="card-grid">
    <div class="card" style="margin:0">
        <h2><i class="fas fa-user"></i> Hesap Bilgileri</h2>
        <form action="{{ route('cekirdex.panel.profile.update') }}" method="POST">
            @csrf
            <div class="form-block">
                <label>Ad Soyad *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-block">
                <label>E-posta *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-block">
                <label>Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="form-block">
                <label>Rol</label>
                <input type="text" value="{{ ucfirst($user->role) }}" disabled>
            </div>
            <button type="submit" class="btn"><i class="fas fa-floppy-disk"></i> Kaydet</button>
        </form>
    </div>

    <div class="card" style="margin:0">
        <h2><i class="fas fa-lock"></i> Şifre Değiştir</h2>
        <form action="{{ route('cekirdex.panel.profile.password') }}" method="POST">
            @csrf
            <div class="form-block">
                <label>Mevcut Şifre *</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-block">
                <label>Yeni Şifre *</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <div class="form-block">
                <label>Yeni Şifre (Tekrar) *</label>
                <input type="password" name="password_confirmation" minlength="6" required>
            </div>
            <button type="submit" class="btn"><i class="fas fa-key"></i> Şifreyi Güncelle</button>
        </form>
    </div>
</div>
@endsection
