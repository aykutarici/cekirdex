@extends('cekirdex.panel.layout')

@section('title', 'Personel')

@section('content')
<div class="pp-head">
    <div>
        <h1>Personel</h1>
        <div class="sub">Restoran ekibinizi yönetin. Garson, mutfak personeli ve müdür ekleyin.</div>
    </div>
    <a href="{{ route('cekirdex.panel.dashboard') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Panele Dön</a>
</div>

<div class="card">
    <h2><i class="fas fa-user-plus"></i> Yeni Personel</h2>
    <form action="{{ route('cekirdex.panel.staff.store') }}" method="POST">
        @csrf
        <div class="form-row">
            <div>
                <label>Ad Soyad *</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>E-posta *</label>
                <input type="email" name="email" required>
            </div>
        </div>
        <div class="form-row" style="margin-top:12px">
            <div>
                <label>Rol *</label>
                <select name="role" required>
                    <option value="manager">Müdür (tüm yönetim)</option>
                    <option value="waiter">Garson (sipariş + çağrı)</option>
                    <option value="kitchen">Mutfak (sadece KDS)</option>
                </select>
            </div>
            <div>
                <label>Telefon</label>
                <input type="text" name="phone">
            </div>
        </div>
        <div class="form-row" style="margin-top:12px">
            <div>
                <label>Şifre *</label>
                <input type="password" name="password" minlength="6" required>
            </div>
            <div></div>
        </div>
        <div style="margin-top:14px"><button type="submit" class="btn"><i class="fas fa-plus"></i> Ekle</button></div>
    </form>
</div>

<div class="card">
    <h2><i class="fas fa-users"></i> Mevcut Personel ({{ $staff->count() }})</h2>
    @if($staff->isEmpty())
        <div style="color:#7d7995;font-size:.92rem">Henüz başka kullanıcı eklenmemiş.</div>
    @else
        <table class="pp-table">
            <thead>
                <tr><th>Ad</th><th>E-posta</th><th>Rol</th><th>Durum</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($staff as $s)
                <tr>
                    <td>
                        <div style="font-weight:700">{{ $s->name }}</div>
                        @if($s->phone)<div class="muted">{{ $s->phone }}</div>@endif
                    </td>
                    <td>{{ $s->email }}</td>
                    <td>
                        @php $roleLabel = ['owner'=>'Sahip','manager'=>'Müdür','waiter'=>'Garson','kitchen'=>'Mutfak','super_admin'=>'Süper Admin'][$s->role] ?? $s->role; @endphp
                        <span class="badge badge-served">{{ $roleLabel }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $s->is_active ? 'badge-ready' : 'badge-cancelled' }}">{{ $s->is_active ? 'Aktif' : 'Pasif' }}</span>
                    </td>
                    <td style="text-align:right">
                        <details>
                            <summary style="cursor:pointer;color:var(--c-accent-d);font-weight:600">Düzenle</summary>
                            <form action="{{ route('cekirdex.panel.staff.update', $s->id) }}" method="POST" style="margin-top:10px;background:#f7f6fb;padding:14px;border-radius:10px;width:320px">
                                @csrf @method('PUT')
                                <div class="form-block"><label>Ad</label><input type="text" name="name" value="{{ $s->name }}" required></div>
                                <div class="form-block"><label>E-posta</label><input type="email" name="email" value="{{ $s->email }}" required></div>
                                <div class="form-block">
                                    <label>Rol</label>
                                    <select name="role">
                                        @foreach(['owner'=>'Sahip','manager'=>'Müdür','waiter'=>'Garson','kitchen'=>'Mutfak'] as $k=>$v)
                                            <option value="{{ $k }}" @selected($s->role===$k)>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-block"><label>Telefon</label><input type="text" name="phone" value="{{ $s->phone }}"></div>
                                <div class="form-block"><label>Yeni Şifre (opsiyonel)</label><input type="password" name="password" minlength="6"></div>
                                <div class="form-block"><label><input type="checkbox" name="is_active" value="1" @checked($s->is_active)> Aktif</label></div>
                                <div style="display:flex;gap:8px">
                                    <button type="submit" class="btn btn-sm">Kaydet</button>
                                </div>
                            </form>
                            <form action="{{ route('cekirdex.panel.staff.destroy', $s->id) }}" method="POST" style="margin-top:8px"
                                  onsubmit="return confirm('Bu personeli silmek istediğinizden emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Sil</button>
                            </form>
                        </details>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
