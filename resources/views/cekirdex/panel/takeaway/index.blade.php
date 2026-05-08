@extends('cekirdex.panel.layout')
@section('title', 'Paket Siparişler')

@section('content')
<div class="pp-head">
    <div>
        <h1><i class="fas fa-bag-shopping"></i> Paket Siparişler</h1>
        <div class="sub">Gel-al ve adrese teslim siparişleri yönetin.</div>
    </div>
</div>

<div style="display: flex; gap: 8px; margin-bottom: 18px; border-bottom: 1px solid var(--c-line);">
    @foreach (['active' => 'Aktif', 'completed' => 'Tamamlanan', 'cancelled' => 'İptal'] as $k => $lbl)
        <a href="{{ route('cekirdex.panel.takeaway.index', ['tab' => $k]) }}"
           style="padding: 10px 16px; border-bottom: 3px solid {{ $tab === $k ? 'var(--c-accent)' : 'transparent' }}; color: {{ $tab === $k ? 'var(--c-accent-d)' : 'var(--c-text-soft)' }}; font-weight: 700; text-decoration: none;">
            {{ $lbl }} <span style="background: var(--c-line); color: var(--c-text); padding: 2px 8px; border-radius: 99px; font-size: .78rem; margin-left: 4px;">{{ $counts[$k] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<div class="card">
    @if(($orders instanceof \Illuminate\Pagination\AbstractPaginator) ? $orders->isEmpty() : $orders->isEmpty())
        <div style="text-align: center; padding: 40px 0; color: var(--c-muted);">
            <i class="fas fa-box-open" style="font-size: 2.4rem; margin-bottom: 12px;"></i>
            <div>Bu sekmede sipariş yok.</div>
        </div>
    @else
        <table class="pp-table">
            <thead>
                <tr>
                    <th>Sipariş</th>
                    <th>Tip</th>
                    <th>Müşteri</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Yaş</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $o)
                    <tr>
                        <td>
                            <strong>#{{ $o->order_number }}</strong>
                            <div class="muted">{{ $o->items->count() }} ürün</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $o->order_type === 'delivery' ? 'preparing' : 'confirmed' }}">
                                <i class="fas fa-{{ $o->order_type === 'delivery' ? 'motorcycle' : 'bag-shopping' }}"></i>
                                {{ $o->type_label }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $o->contact_name }}</strong>
                            <div class="muted">{{ $o->contact_phone }}</div>
                        </td>
                        <td><strong>{{ number_format((float) $o->total, 2, ',', '.') }} ₺</strong></td>
                        <td><span class="badge badge-{{ $o->status }}">{{ $o->status_label }}</span></td>
                        <td class="muted">{{ $o->created_at->diffForHumans(['short' => true]) }}</td>
                        <td><a href="{{ route('cekirdex.panel.takeaway.show', $o->id) }}" class="btn btn-sm">Detay</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($orders instanceof \Illuminate\Pagination\AbstractPaginator)
            <div style="margin-top: 16px;">{{ $orders->links() }}</div>
        @endif
    @endif
</div>

@push('scripts')
<script>
const FEED = @json(route('cekirdex.panel.takeaway.feed'));
let lastIds = new Set();
async function tick() {
    try {
        const r = await fetch(FEED);
        if (!r.ok) return;
        const d = await r.json();
        const newIds = new Set(d.orders.map(o => o.id));
        let hasNew = false;
        for (const id of newIds) if (!lastIds.has(id)) { hasNew = true; break; }
        if (hasNew && lastIds.size > 0) location.reload();
        lastIds = newIds;
    } catch (e) {}
}
setTimeout(tick, 1500);
setInterval(tick, 10000);
</script>
@endpush
@endsection
