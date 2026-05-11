@extends('cekirdex.panel.layout')

@section('title', 'Masalar & QR')

@push('styles')
<style>
.table-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.table-card {
    background: #fff; border: 1px solid var(--c-line); border-radius: 16px;
    padding: 22px 20px; text-align: center; box-shadow: var(--c-shadow);
}
.table-card.inactive { opacity: 0.55; background: var(--c-bg); }
.table-card .name { font-weight: 800; font-size: 1.1rem; }
.table-card .meta { color: var(--c-muted); font-size: .82rem; margin: 4px 0 10px; line-height: 1.4; }
.table-card .ph-wrap {
    width: 100%; max-height: 120px; margin: 0 auto 10px; border-radius: 10px; overflow: hidden;
    background: var(--c-bg); border: 1px solid var(--c-line);
}
.table-card .ph-wrap img { width: 100%; height: 120px; object-fit: cover; display: block; }
.table-card .qr {
    width: 160px; height: 160px; margin: 0 auto 12px;
    background: #fff; border: 1px solid var(--c-line); border-radius: 10px;
    padding: 6px;
}
.table-card .qr img { width: 100%; height: 100%; }
.table-card .actions { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; }
.table-card .url { font-size: .68rem; color: var(--c-muted); word-break: break-all; }
.table-card .edit-block { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--c-line-2); text-align: left; }
.table-card .edit-block label { font-size: .75rem; font-weight: 600; display: block; margin-bottom: 4px; }
.table-card .edit-block input[type="text"],
.table-card .edit-block input[type="number"],
.table-card .edit-block textarea {
    width: 100%; padding: 8px 10px; border: 1.5px solid var(--c-line-2); border-radius: 8px; font-size: .85rem; margin-bottom: 8px;
}
.table-card .edit-block .chk-row { display: flex; align-items: center; gap: 8px; font-size: .82rem; margin-bottom: 6px; }
@media print {
    body * { visibility: hidden !important; }
    .pp-side, .pp-head, .form-add, .actions-inline, .edit-block { display: none !important; }
    .pp-main { padding: 0 !important; }
    .table-card, .table-card * { visibility: visible !important; }
    .table-card { page-break-inside: avoid; box-shadow: none !important; border: 2px solid #000 !important; margin: 12px; }
}
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1>Masalar & QR Kodları</h1>
        <div class="sub">Toplam <strong>{{ $tables->count() }}</strong> masa. QR kodlarını yazdırıp masalara koyabilirsiniz.</div>
    </div>
    <div class="actions-inline">
        <a class="btn-ghost btn" href="javascript:window.print()"><i class="fas fa-print"></i> QR'ları yazdır</a>
    </div>
</div>

<div class="card form-add">
    <h2><i class="fas fa-plus"></i> Yeni masa ekle</h2>
    <form method="POST" action="{{ route('cekirdex.panel.tables.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <div><label>Masa adı *</label><input type="text" name="name" required maxlength="64" placeholder="Örn. Masa 5, Bahçe 1"></div>
            <div><label>Kod (opsiyonel)</label><input type="text" name="code" maxlength="32" placeholder="Harici sistem kodu"></div>
            <div><label>Kapasite</label><input type="number" name="capacity" min="1" max="60" value="2"></div>
        </div>
        <div class="form-row">
            <div style="grid-column: 1 / -1;">
                <label>İç not (personel — özel masa, yer, vb.)</label>
                <textarea name="internal_note" rows="2" maxlength="2000" placeholder="Örn. Köşe, doğum günü masası, yüksek sandalye..."></textarea>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="accepts_reservations" value="0">
                    <input type="checkbox" name="accepts_reservations" value="1" checked> Rezervasyon kapasitesine dahil
                </label>
            </div>
            <div>
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked> Aktif
                </label>
            </div>
            <div><label>Fotoğraf (opsiyonel)</label><input type="file" name="photo" accept="image/*"></div>
        </div>
        <button type="submit" class="btn" style="margin-top:8px"><i class="fas fa-plus"></i> Masa ekle</button>
    </form>
</div>

<div class="table-grid">
    @foreach($tables as $t)
        @php $url = url('/m/'.$t->qr_token); @endphp
        <div class="table-card @if(!$t->is_active) inactive @endif">
            <div class="name">{{ $t->name }}</div>
            <div class="meta">
                {{ $t->capacity }} kişilik
                @if($t->code) · {{ $t->code }} @endif
                · {{ $t->is_active ? 'Aktif' : 'Pasif' }}
                @if(!$t->accepts_reservations)<br><span class="muted">Rezervasyona dahil değil</span>@endif
                @if($t->internal_note)<br><em title="{{ $t->internal_note }}">{{ \Illuminate\Support\Str::limit($t->internal_note, 70) }}</em>@endif
            </div>
            @if($t->photo_url)
                <div class="ph-wrap"><img src="{{ $t->photo_url }}" alt=""></div>
            @endif
            <div class="qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=4&data={{ urlencode($url) }}" alt="QR · {{ $t->name }}">
            </div>
            <div class="url">{{ $url }}</div>
            <div class="actions">
                <a class="btn btn-sm btn-ghost" href="{{ $url }}" target="_blank"><i class="fas fa-eye"></i> Önizle</a>
                <form method="POST" action="{{ route('cekirdex.panel.tables.regenerate-qr', $t->id) }}" onsubmit="return confirm('QR kodu yenilenecek. Mevcut yazılı QR\'lar geçersiz olur. Devam?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-ghost" style="width:100%"><i class="fas fa-rotate"></i> QR yenile</button>
                </form>
                <form method="POST" action="{{ route('cekirdex.panel.tables.destroy', $t->id) }}" onsubmit="return confirm('Bu masayı silmek istiyor musunuz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="width:100%"><i class="fas fa-trash"></i> Sil</button>
                </form>
            </div>

            <div class="edit-block">
                <strong style="font-size:.82rem;"><i class="fas fa-pen"></i> Düzenle</strong>
                <form method="POST" action="{{ route('cekirdex.panel.tables.update', $t->id) }}" enctype="multipart/form-data" style="margin-top:10px;">
                    @csrf @method('PUT')
                    <label>Masa adı *</label>
                    <input type="text" name="name" required maxlength="64" value="{{ $t->name }}">
                    <label>Kod</label>
                    <input type="text" name="code" maxlength="32" value="{{ $t->code }}">
                    <label>Kapasite</label>
                    <input type="number" name="capacity" min="1" max="60" value="{{ $t->capacity }}">
                    <label>İç not</label>
                    <textarea name="internal_note" rows="2" maxlength="2000">{{ $t->internal_note }}</textarea>
                    <div class="chk-row">
                        <input type="hidden" name="accepts_reservations" value="0">
                        <input type="checkbox" name="accepts_reservations" value="1" id="ar-{{ $t->id }}" {{ $t->accepts_reservations ? 'checked' : '' }}>
                        <label for="ar-{{ $t->id }}" style="margin:0;font-weight:500;">Rezervasyona dahil</label>
                    </div>
                    <div class="chk-row">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="ia-{{ $t->id }}" {{ $t->is_active ? 'checked' : '' }}>
                        <label for="ia-{{ $t->id }}" style="margin:0;font-weight:500;">Aktif</label>
                    </div>
                    @if($t->photo)
                        <div class="chk-row">
                            <input type="checkbox" name="remove_photo" value="1" id="rp-{{ $t->id }}">
                            <label for="rp-{{ $t->id }}" style="margin:0;font-weight:500;">Fotoğrafı kaldır</label>
                        </div>
                    @endif
                    <label>Yeni fotoğraf</label>
                    <input type="file" name="photo" accept="image/*" style="margin-bottom:8px;">
                    <button type="submit" class="btn btn-sm" style="width:100%"><i class="fas fa-save"></i> Kaydet</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
