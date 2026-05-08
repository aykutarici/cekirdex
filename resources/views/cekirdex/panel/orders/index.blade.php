@extends('cekirdex.panel.layout')

@section('title', 'Siparişler')

@push('styles')
<style>
.status-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
.status-pills a { padding: 8px 14px; background: #fff; border: 1px solid var(--c-line); border-radius: 99px; font-size: .85rem; font-weight: 600; color: var(--c-text-soft); text-decoration: none; }
.status-pills a:hover { border-color: var(--c-accent); color: var(--c-accent-d); }
.status-pills a.is-active { background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff; border-color: transparent; box-shadow: 0 6px 14px -4px rgba(255,107,53,.45); }
.status-pills a small { margin-left: 6px; opacity: .85; }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1>Siparişler</h1>
        <div class="sub">Toplam {{ $orders->total() }} sipariş.</div>
    </div>
    <div>
        <a class="btn-ghost btn" href="{{ route('cekirdex.panel.dashboard') }}"><i class="fas fa-arrow-left"></i> Anasayfa</a>
    </div>
</div>

<div class="status-pills">
    <a href="{{ route('cekirdex.panel.orders.index') }}" class="@if(!$status) is-active @endif">Tümü</a>
    @foreach(\App\Cekirdex\Models\CekirdexOrder::STATUSES as $key => $label)
        <a href="{{ route('cekirdex.panel.orders.index', ['status' => $key]) }}" class="@if($status === $key) is-active @endif">
            {{ $label }} <small>({{ $counts[$key] ?? 0 }})</small>
        </a>
    @endforeach
</div>

<div class="card">
    @if($orders->isEmpty())
        <p style="color:var(--c-muted);text-align:center;padding:30px 0">Henüz sipariş yok.</p>
    @else
        <table class="pp-table">
            <thead>
                <tr>
                    <th>Sipariş No</th>
                    <th>Masa</th>
                    <th>Adet</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $o)
                <tr>
                    <td><strong>{{ $o->order_number }}</strong>
                        @if($o->guest_name)<div class="muted">{{ $o->guest_name }}</div>@endif
                    </td>
                    <td>{{ optional($o->table)->name ?? '—' }}</td>
                    <td>{{ $o->items->sum('quantity') }} ürün</td>
                    <td><strong>{{ number_format((float) $o->total, 2, ',', '.') }} ₺</strong></td>
                    <td><span class="badge badge-{{ $o->status }}">{{ $o->status_label }}</span></td>
                    <td class="muted">{{ $o->created_at->format('d.m.Y H:i') }}</td>
                    <td><a class="btn btn-sm btn-ghost" href="{{ route('cekirdex.panel.orders.show', $o->id) }}">Detay</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:14px">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
