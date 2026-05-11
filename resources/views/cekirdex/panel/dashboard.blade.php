@extends('cekirdex.panel.layout')

@section('title', 'Anasayfa')

@section('content')
<div class="pp-head">
    <div>
        <h1>Hoş geldin, {{ $user->name }}</h1>
        <div class="sub">İşte bugünün özeti.</div>
    </div>
    <div>
        <a class="btn-ghost btn" href="{{ route('cekirdex.panel.menu.index') }}"><i class="fas fa-utensils"></i> Menü</a>
        <a class="btn" href="{{ route('cekirdex.panel.tables.index') }}"><i class="fas fa-qrcode"></i> Masaları gör</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi">
        <div class="ic" style="background:linear-gradient(135deg,#ff8a4c,#ff6b35)"><i class="fas fa-receipt"></i></div>
        <div class="label">Bugünkü sipariş</div>
        <div class="v">{{ $todayOrders }}</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-coins"></i></div>
        <div class="label">Bugünkü ciro</div>
        <div class="v">{{ number_format((float) $todayRevenue, 2, ',', '.') }} ₺</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:linear-gradient(135deg,#f472b6,#be185d)"><i class="fas fa-fire"></i></div>
        <div class="label">Aktif sipariş</div>
        <div class="v">{{ $activeOrders }}</div>
    </div>
    <div class="kpi">
        <div class="ic" style="background:linear-gradient(135deg,#fbbf24,#d97706)"><i class="fas fa-bell"></i></div>
        <div class="label">Bekleyen çağrı</div>
        <div class="v">{{ $pendingCalls }}</div>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <h2><i class="fas fa-receipt"></i> Son siparişler</h2>
        @if($recentOrders->isEmpty())
            <p style="color:var(--c-muted)">Henüz sipariş yok. Müşteriler QR'ı okutup sipariş verdiğinde burada görünür.</p>
        @else
            <table class="pp-table">
                <thead>
                    <tr><th>No</th><th>Masa</th><th>Tutar</th><th>Durum</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($recentOrders as $o)
                    <tr>
                        <td><strong>{{ $o->order_number }}</strong><div class="muted">{{ $o->created_at->diffForHumans() }}</div></td>
                        <td>{{ optional($o->table)->name ?? '—' }}</td>
                        <td><strong>{{ number_format((float) $o->total, 2, ',', '.') }} ₺</strong></td>
                        <td><span class="badge badge-{{ $o->status }}">{{ $o->status_label }}</span></td>
                        <td><a class="btn btn-sm btn-ghost" href="{{ route('cekirdex.panel.orders.show', $o->id) }}">Detay</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <h2><i class="fas fa-bell-concierge"></i> Bekleyen çağrılar</h2>
        @if($recentCalls->isEmpty())
            <p style="color:var(--c-muted)">Şu anda bekleyen çağrı yok. 👍</p>
        @else
            <table class="pp-table">
                <thead><tr><th>Masa</th><th>Tip</th><th>Mesaj</th><th>Zaman</th></tr></thead>
                <tbody>
                @foreach($recentCalls as $c)
                    <tr>
                        <td><strong>{{ optional($c->table)->name ?? '—' }}</strong></td>
                        <td><span class="badge badge-pending">{{ $c->type_label }}</span></td>
                        <td class="muted">{{ $c->message ?: '—' }}</td>
                        <td class="muted">{{ $c->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <a class="btn btn-sm btn-ghost" href="{{ route('cekirdex.panel.calls.index') }}" style="margin-top:12px"><i class="fas fa-arrow-right"></i> Tüm çağrılar</a>
        @endif
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <h2><i class="fas fa-circle-info"></i> Hızlı bilgi</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                <div class="muted">Toplam masa</div>
                <div style="font-size:1.4rem;font-weight:700">{{ $totals['tables'] }}</div>
            </div>
            <div>
                <div class="muted">Menüdeki ürün</div>
                <div style="font-size:1.4rem;font-weight:700">{{ $totals['products'] }}</div>
            </div>
            <div>
                <div class="muted">Toplam sipariş</div>
                <div style="font-size:1.4rem;font-weight:700">{{ $totals['all_orders'] }}</div>
            </div>
        </div>
    </div>
    <div class="card">
        <h2><i class="fas fa-rocket"></i> Hızlı başlat</h2>
        <ul style="list-style:none;padding:0;margin:0">
            <li style="padding:8px 0"><i class="fas fa-check-circle" style="color:#10b981"></i> Restoran kaydedildi</li>
            <li style="padding:8px 0"><i class="fas fa-{{ $totals['products'] > 0 ? 'check-circle' : 'circle' }}" style="color:{{ $totals['products'] > 0 ? '#10b981' : '#cbd5e1' }}"></i>
                Menüye ürün ekle — <a href="{{ route('cekirdex.panel.menu.index') }}">menüye git</a></li>
            <li style="padding:8px 0"><i class="fas fa-{{ $totals['tables'] > 0 ? 'check-circle' : 'circle' }}" style="color:{{ $totals['tables'] > 0 ? '#10b981' : '#cbd5e1' }}"></i>
                Masaları yazdır — <a href="{{ route('cekirdex.panel.tables.index') }}">masalara git</a></li>
            <li style="padding:8px 0"><i class="fas fa-circle" style="color:#cbd5e1"></i>
                İlk müşteri siparişini al 🚀</li>
        </ul>
    </div>
</div>

@if(isset($outOfStock) && $outOfStock->isNotEmpty())
<div class="card" style="margin-top: 18px; border-left: 4px solid #f59e0b;">
    <h2><i class="fas fa-circle-exclamation" style="color:#f59e0b"></i> Bugün Tükenen Ürünler ({{ $outOfStock->count() }})</h2>
    <p class="muted" style="margin-bottom: 12px;">Bu ürünler menüde "Tükendi" rozetiyle gösteriliyor ve sipariş alınmıyor.</p>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        @foreach($outOfStock as $p)
            <button type="button" onclick="quickRestoreStock({{ $p->id }}, this)"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; background: #fff7ed; border: 1px solid #fed7aa; color: #7c2d12; border-radius: 99px; font-weight: 600; font-size: .88rem; cursor: pointer;">
                <i class="fas fa-rotate-right"></i>
                {{ $p->name }}
                <span style="background: #10b981; color: #fff; padding: 2px 8px; border-radius: 99px; font-size: .72rem;">Tekrar var yap</span>
            </button>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
async function quickRestoreStock(productId, btn) {
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    btn.disabled = true; const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';
    try {
        const r = await fetch(`/panel/menu/product/${productId}/toggle-stock`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const d = await r.json();
        if (!r.ok || !d.ok) throw new Error();
        btn.style.background = '#dcfce7'; btn.style.color = '#166534';
        btn.innerHTML = '<i class="fas fa-check"></i> Tekrar var';
        setTimeout(() => location.reload(), 800);
    } catch (e) {
        btn.disabled = false; btn.innerHTML = orig;
        alert('İşlem başarısız.');
    }
}
</script>
@endpush
@endif
@endsection
