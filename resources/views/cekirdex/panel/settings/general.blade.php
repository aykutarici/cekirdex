@extends('cekirdex.panel.layout')

@section('title', 'Restoran Ayarları')

@section('content')
<div class="pp-head">
    <div>
        <h1>Restoran Ayarları</h1>
        <div class="sub">Genel bilgiler, vergi/servis oranı ve marka renkleri.</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('cekirdex.panel.settings.services') }}" class="btn btn-ghost"><i class="fas fa-bag-shopping"></i> Hizmet Ayarları</a>
        <a href="{{ route('cekirdex.panel.dashboard') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Panele Dön</a>
    </div>
</div>

<div class="card">
    <h2><i class="fas fa-store"></i> Genel Bilgiler</h2>
    <form action="{{ route('cekirdex.panel.settings.general.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-row">
            <div>
                <label>Restoran Adı *</label>
                <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" required>
            </div>
            <div>
                <label>Telefon</label>
                <input type="text" name="phone" value="{{ old('phone', $restaurant->phone) }}">
            </div>
        </div>

        <div class="form-block" style="margin-top:12px">
            <label>Açıklama</label>
            <textarea name="description" rows="3">{{ old('description', $restaurant->description) }}</textarea>
        </div>

        <div class="form-row" style="margin-top:12px">
            <div>
                <label>E-posta</label>
                <input type="email" name="email" value="{{ old('email', $restaurant->email) }}">
            </div>
            <div>
                <label>Web sitesi</label>
                <input type="url" name="website" value="{{ old('website', $restaurant->website) }}" placeholder="https://...">
            </div>
        </div>

        <div class="form-row" style="margin-top:12px">
            <div>
                <label>Adres</label>
                <input type="text" name="address" value="{{ old('address', $restaurant->address) }}">
            </div>
            <div>
                <label>Şehir</label>
                <input type="text" name="city" value="{{ old('city', $restaurant->city) }}">
            </div>
        </div>

        <h2 style="margin-top:24px"><i class="fas fa-percent"></i> Para Birimi & Oranlar</h2>
        <div class="form-row">
            <div>
                <label>Para Birimi</label>
                <select name="currency">
                    @foreach(['TRY','USD','EUR','GBP'] as $c)
                        <option value="{{ $c }}" @selected(old('currency', $restaurant->currency) === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>KDV Oranı (%)</label>
                <input type="number" name="tax_rate" step="0.01" min="0" max="50" value="{{ old('tax_rate', $restaurant->tax_rate) }}">
            </div>
        </div>
        <div class="form-row" style="margin-top:12px">
            <div>
                <label>Servis Bedeli (%)</label>
                <input type="number" name="service_charge_rate" step="0.01" min="0" max="50" value="{{ old('service_charge_rate', $restaurant->service_charge_rate) }}">
            </div>
            <div></div>
        </div>

        <h2 style="margin-top:24px"><i class="fas fa-palette"></i> Marka Renkleri</h2>
        <div class="form-row">
            <div>
                <label>Birincil Renk</label>
                <input type="color" name="primary_color" value="{{ old('primary_color', $restaurant->primary_color ?: '#ff6b35') }}" style="height:46px">
            </div>
            <div>
                <label>İkincil Renk</label>
                <input type="color" name="secondary_color" value="{{ old('secondary_color', $restaurant->secondary_color ?: '#1c1933') }}" style="height:46px">
            </div>
        </div>

        <h2 style="margin-top:24px"><i class="fas fa-image"></i> Logo & Kapak</h2>
        <div class="form-row">
            <div>
                <label>Logo (kare, max 2 MB)</label>
                @if($restaurant->logo)
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                        <img src="{{ asset('storage/'.$restaurant->logo) }}" style="width:60px;height:60px;border-radius:10px;object-fit:cover">
                        <label style="font-weight:600;font-size:.85rem"><input type="checkbox" name="remove_logo" value="1"> Logoyu kaldır</label>
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*">
            </div>
            <div>
                <label>Kapak görseli (max 4 MB)</label>
                @if($restaurant->cover_image)
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                        <img src="{{ asset('storage/'.$restaurant->cover_image) }}" style="width:120px;height:60px;border-radius:10px;object-fit:cover">
                        <label style="font-weight:600;font-size:.85rem"><input type="checkbox" name="remove_cover" value="1"> Kapağı kaldır</label>
                    </div>
                @endif
                <input type="file" name="cover_image" accept="image/*">
            </div>
        </div>

        <div style="margin-top:22px;display:flex;gap:10px">
            <button type="submit" class="btn"><i class="fas fa-floppy-disk"></i> Kaydet</button>
        </div>
    </form>
</div>

<div class="card">
    <h2><i class="fas fa-link"></i> Bağlantılar</h2>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
            <div style="font-size:.78rem;color:#7d7995;text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:4px">Slug</div>
            <code style="background:#f7f6fb;padding:6px 10px;border-radius:8px;font-family:'JetBrains Mono',ui-monospace,monospace;font-size:.88rem">{{ $restaurant->slug }}</code>
        </div>
        <div>
            <div style="font-size:.78rem;color:#7d7995;text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:4px">Durum</div>
            <span class="badge {{ $restaurant->is_active ? 'badge-ready' : 'badge-cancelled' }}">{{ $restaurant->is_active ? 'Aktif' : 'Pasif' }}</span>
        </div>
    </div>
</div>
@endsection
