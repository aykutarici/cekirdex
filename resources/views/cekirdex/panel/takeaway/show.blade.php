@extends('cekirdex.panel.layout')
@section('title', 'Paket Sipariş #'.$order->order_number)

@section('content')
<div class="pp-head">
    <div>
        <h1>Sipariş #{{ $order->order_number }}</h1>
        <div class="sub">
            <span class="badge badge-{{ $order->order_type === 'delivery' ? 'preparing' : 'confirmed' }}">
                <i class="fas fa-{{ $order->order_type === 'delivery' ? 'motorcycle' : 'bag-shopping' }}"></i>
                {{ $order->type_label }}
            </span>
            <span class="badge badge-{{ $order->status }}" style="margin-left: 6px;">{{ $order->status_label }}</span>
            <span class="muted" style="margin-left: 8px;">{{ $order->created_at->format('d.m.Y H:i') }}</span>
        </div>
    </div>
    <a href="{{ route('cekirdex.panel.takeaway.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Listeye dön</a>
</div>

<div class="card-grid">
    <div class="card">
        <h2><i class="fas fa-user"></i> Müşteri</h2>
        <table class="pp-table">
            <tr><th>Ad</th><td>{{ $order->contact_name }}</td></tr>
            <tr><th>Telefon</th><td><a href="tel:{{ $order->contact_phone }}">{{ $order->contact_phone }}</a></td></tr>
            @if($order->contact_email)<tr><th>E-posta</th><td>{{ $order->contact_email }}</td></tr>@endif
            @if($order->order_type === 'delivery')
                <tr><th>Adres</th><td>{{ $order->delivery_address }}</td></tr>
            @endif
            @if($order->note)<tr><th>Not</th><td>{{ $order->note }}</td></tr>@endif
            @if($order->eta_minutes)<tr><th>ETA</th><td>{{ $order->eta_minutes }} dk</td></tr>@endif
            <tr><th>Takip linki</th><td><a href="{{ url('/cekirdex/o/'.$order->public_code) }}" target="_blank">{{ url('/cekirdex/o/'.$order->public_code) }}</a></td></tr>
        </table>
    </div>

    <div class="card">
        <h2><i class="fas fa-receipt"></i> Sipariş</h2>
        <table class="pp-table">
            @foreach($order->items as $it)
                <tr>
                    <td><strong>{{ $it->name }}</strong>@if($it->note)<div class="muted">{{ $it->note }}</div>@endif</td>
                    <td>× {{ $it->quantity }}</td>
                    <td style="text-align: right;"><strong>{{ number_format((float) $it->subtotal, 2, ',', '.') }} ₺</strong></td>
                </tr>
            @endforeach
        </table>
        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--c-line);">
            <div style="display: flex; justify-content: space-between; padding: 4px 0;"><span>Ara toplam</span><span>{{ number_format((float) $order->subtotal, 2, ',', '.') }} ₺</span></div>
            @if((float) $order->tax > 0)<div style="display: flex; justify-content: space-between; padding: 4px 0;"><span>KDV</span><span>{{ number_format((float) $order->tax, 2, ',', '.') }} ₺</span></div>@endif
            @if((float) $order->service_charge > 0)<div style="display: flex; justify-content: space-between; padding: 4px 0;"><span>Servis</span><span>{{ number_format((float) $order->service_charge, 2, ',', '.') }} ₺</span></div>@endif
            @if((float) $order->delivery_fee > 0)<div style="display: flex; justify-content: space-between; padding: 4px 0;"><span>Teslim</span><span>{{ number_format((float) $order->delivery_fee, 2, ',', '.') }} ₺</span></div>@endif
            <div style="display: flex; justify-content: space-between; padding: 8px 0; font-weight: 800; font-size: 1.05rem;"><span>TOPLAM</span><span>{{ number_format((float) $order->total, 2, ',', '.') }} ₺</span></div>
        </div>
    </div>
</div>

<div class="card">
    <h2><i class="fas fa-bolt"></i> İşlemler</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">

        @if($order->status === 'new')
            <form action="{{ route('cekirdex.panel.takeaway.confirm', $order->id) }}" method="POST" style="display: flex; gap: 8px; align-items: center;">
                @csrf
                <label style="font-weight: 600; font-size: .88rem;">ETA (dk):</label>
                <input type="number" name="eta_minutes" value="30" min="5" max="240" required
                       style="width: 80px; padding: 8px; border: 1.5px solid var(--c-line-2); border-radius: 8px;">
                <button class="btn btn-sm" type="submit"><i class="fas fa-check"></i> Onayla</button>
            </form>
        @endif

        @if(in_array($order->status, ['confirmed', 'preparing', 'ready']))
            <form action="{{ route('cekirdex.panel.takeaway.advance', $order->id) }}" method="POST">
                @csrf
                <button class="btn btn-sm" type="submit">
                    <i class="fas fa-forward"></i>
                    @if($order->status === 'confirmed') Hazırlamaya Başla
                    @elseif($order->status === 'preparing') Hazır olarak işaretle
                    @elseif($order->status === 'ready')
                        @if($order->order_type === 'delivery') Yola Çıktı
                        @else Teslim Edildi
                        @endif
                    @endif
                </button>
            </form>
        @endif

        @if($order->status === 'ready' && $order->order_type === 'delivery')
            <form action="{{ route('cekirdex.panel.takeaway.advance', $order->id) }}" method="POST">
                @csrf
                <button class="btn btn-sm" type="submit"><i class="fas fa-flag-checkered"></i> Teslim Edildi</button>
            </form>
        @endif

        @if(!in_array($order->status, ['cancelled', 'delivered', 'closed', 'served']))
            <form action="{{ route('cekirdex.panel.takeaway.cancel', $order->id) }}" method="POST"
                  onsubmit="return confirm('Siparişi iptal etmek istediğinize emin misiniz?')"
                  style="display: flex; gap: 8px; align-items: center;">
                @csrf
                <input type="text" name="reason" placeholder="İptal sebebi (op)" maxlength="300"
                       style="padding: 8px; border: 1.5px solid var(--c-line-2); border-radius: 8px; min-width: 220px;">
                <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-xmark"></i> İptal Et</button>
            </form>
        @endif
    </div>
</div>
@endsection
