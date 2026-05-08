@extends('cekirdex.panel.layout')

@section('title', 'Sipariş ' . $order->order_number)

@section('content')
<div class="pp-head">
    <div>
        <a href="{{ route('cekirdex.panel.orders.index') }}" style="color:var(--c-muted);font-size:.88rem"><i class="fas fa-arrow-left"></i> Tüm siparişler</a>
        <h1>{{ $order->order_number }}</h1>
        <div class="sub">{{ $order->created_at->format('d.m.Y H:i') }} · Masa: <strong>{{ optional($order->table)->name ?? '—' }}</strong></div>
    </div>
    <div>
        <span class="badge badge-{{ $order->status }}" style="font-size:.85rem;padding:6px 14px">{{ $order->status_label }}</span>
    </div>
</div>

<div class="card-grid">
    <div class="card">
        <h2><i class="fas fa-list"></i> Ürünler</h2>
        <table class="pp-table">
            <thead><tr><th>Ürün</th><th>Adet</th><th>Birim</th><th>Tutar</th></tr></thead>
            <tbody>
                @foreach($order->items as $it)
                <tr>
                    <td>
                        <strong>{{ $it->name }}</strong>
                        @if($it->note)<div class="muted">Not: {{ $it->note }}</div>@endif
                    </td>
                    <td>{{ $it->quantity }}</td>
                    <td>{{ number_format((float) $it->price, 2, ',', '.') }} ₺</td>
                    <td><strong>{{ number_format((float) $it->subtotal, 2, ',', '.') }} ₺</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--c-line);font-size:.92rem">
            <div style="display:flex;justify-content:space-between;padding:4px 0"><span class="muted">Ara toplam</span><span>{{ number_format((float) $order->subtotal, 2, ',', '.') }} ₺</span></div>
            @if((float) $order->tax > 0)<div style="display:flex;justify-content:space-between;padding:4px 0"><span class="muted">KDV</span><span>{{ number_format((float) $order->tax, 2, ',', '.') }} ₺</span></div>@endif
            @if((float) $order->service_charge > 0)<div style="display:flex;justify-content:space-between;padding:4px 0"><span class="muted">Servis</span><span>{{ number_format((float) $order->service_charge, 2, ',', '.') }} ₺</span></div>@endif
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--c-line);font-size:1.1rem;font-weight:800;margin-top:6px">
                <span>Toplam</span><span style="color:var(--c-accent-d)">{{ number_format((float) $order->total, 2, ',', '.') }} ₺</span>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <h2><i class="fas fa-flag"></i> Durum güncelle</h2>
            <form method="POST" action="{{ route('cekirdex.panel.orders.update-status', $order->id) }}">
                @csrf @method('PATCH')
                <div class="form-block">
                    <select name="status">
                        @foreach(\App\Cekirdex\Models\CekirdexOrder::STATUSES as $k => $l)
                            <option value="{{ $k }}" @selected($order->status === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn" style="width:100%"><i class="fas fa-save"></i> Güncelle</button>
            </form>
        </div>
        <div class="card">
            <h2><i class="fas fa-circle-info"></i> Detay</h2>
            <div style="font-size:.9rem;line-height:1.9">
                @if($order->guest_name)<div><span class="muted">Misafir:</span> <strong>{{ $order->guest_name }}</strong></div>@endif
                @if($order->guest_phone)<div><span class="muted">Telefon:</span> {{ $order->guest_phone }}</div>@endif
                @if($order->note)<div><span class="muted">Not:</span> {{ $order->note }}</div>@endif
                <div><span class="muted">Ödeme:</span> {{ $order->payment_status === 'paid' ? 'Ödendi' : 'Bekliyor' }}</div>
                @if($order->payment_method)<div><span class="muted">Yöntem:</span> {{ $order->payment_method }}</div>@endif
                <div class="muted" style="font-size:.78rem;margin-top:8px">{{ $order->ip_address }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
