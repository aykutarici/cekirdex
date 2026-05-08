@extends('cekirdex.panel.layout')

@section('title', $table->name . ' Hesabı')

@push('styles')
<style>
.bill-cols { display:grid; grid-template-columns: 2fr 1fr; gap:16px; }
@media (max-width: 980px) { .bill-cols { grid-template-columns: 1fr; } }
.totals-card { background:linear-gradient(135deg,#fff7ed,#fefce8); border:1px solid #fde68a; border-radius:14px; padding:18px 20px; }
.totals-card .row { display:flex; justify-content:space-between; padding:5px 0; font-size:.95rem; color:#57534e; }
.totals-card .row.total { font-size:1.2rem; font-weight:800; color:#1c1917; padding-top:10px; margin-top:8px; border-top:1px dashed rgba(0,0,0,.1); }
.totals-card .row.paid { color:#047857; font-weight:700; }
.totals-card .row.remaining { color:#b91c1c; font-weight:800; font-size:1.05rem; padding-top:8px; margin-top:6px; border-top:1px dashed rgba(0,0,0,.08); }
.bill-progress { height:10px; background:#fff; border-radius:99px; overflow:hidden; margin:10px 0; border:1px solid rgba(0,0,0,.05); }
.bill-progress span { display:block; height:100%; background:linear-gradient(90deg,#10b981,#059669); transition:width .35s; }

.kalem { display:grid; grid-template-columns: auto 1fr auto auto; gap:10px; padding:10px 12px; background:#fff; border:1px solid var(--c-line); border-radius:10px; margin-bottom:6px; align-items:center; }
.kalem .qty { font-weight:700; color:#3730a3; min-width:30px; text-align:center; background:#e0e7ff; border-radius:6px; padding:2px 8px; font-size:.85rem; }
.kalem .nm { font-weight:600; }
.kalem .nm small { color:var(--c-muted); display:block; font-weight:400; font-size:.78rem; }
.kalem .pr { font-weight:700; color:var(--c-text); }
.kalem.is-paid { opacity:.55; }

.payment-row { display:flex; justify-content:space-between; padding:8px 12px; background:#fff; border:1px solid var(--c-line); border-radius:10px; margin-bottom:6px; align-items:center; font-size:.9rem; }
.payment-row .who { color:var(--c-muted); font-size:.78rem; }
.payment-row .amt { font-weight:700; }
.payment-row form { display:inline; margin:0; }

.method-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:6px; margin-bottom:10px; }
.method-grid label { padding:8px 6px; text-align:center; border:1.5px solid var(--c-line); border-radius:10px; font-size:.78rem; font-weight:600; cursor:pointer; background:#fff; }
.method-grid label.is-on { border-color:#ff6b35; background:#fff7f1; color:#d9531c; }
.method-grid input { display:none; }

.delivery-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:8px; margin:8px 0 12px; }
.delivery-grid label { padding:12px; text-align:center; border:1.5px solid var(--c-line); border-radius:10px; cursor:pointer; background:#fff; font-size:.85rem; font-weight:600; }
.delivery-grid label.is-on { border-color:#10b981; background:#ecfdf5; color:#047857; }
.delivery-grid input { display:none; }
.delivery-grid label i { display:block; font-size:1.4rem; margin-bottom:4px; }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1><i class="fas fa-receipt"></i> {{ $table->name }} — Hesap</h1>
        <div class="sub">{{ count($bill['orders']) }} açık sipariş, {{ count($bill['items']) }} kalem</div>
    </div>
    <div style="display:flex;gap:6px">
        <a class="btn btn-primary" href="{{ route('cekirdex.panel.bills.waiter-order', $table->id) }}"><i class="fas fa-plus"></i> Sipariş Ekle</a>
        <a class="btn btn-ghost" href="{{ route('cekirdex.panel.bills.index') }}"><i class="fas fa-arrow-left"></i> Geri</a>
    </div>
</div>

@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@if(!$bill['has_open_orders'])
    <div class="card" style="text-align:center;padding:40px;color:var(--c-muted)">
        <i class="fas fa-mug-saucer" style="font-size:2.5rem;margin-bottom:10px"></i>
        <div style="font-weight:700;font-size:1.05rem;color:var(--c-text)">Bu masada açık adisyon yok.</div>
        <a class="btn btn-primary" style="margin-top:14px" href="{{ route('cekirdex.panel.bills.waiter-order', $table->id) }}">
            <i class="fas fa-plus"></i> Yeni Sipariş Aç
        </a>
    </div>
@else

@php $isPaid = $bill['remaining'] < 0.01; $paidPct = $bill['total'] > 0 ? min(100, ($bill['paid'] / $bill['total']) * 100) : 0; @endphp

<div class="bill-cols">
    {{-- SOL: Kalemler + Ödemeler --}}
    <div>
        <div class="card">
            <h3 style="margin-bottom:10px"><i class="fas fa-list"></i> Adisyon Kalemleri</h3>
            @foreach($bill['items'] as $it)
                @php $remQty = $it['quantity'] - $it['paid_quantity']; $isLineP = $remQty <= 0; @endphp
                <div class="kalem {{ $isLineP ? 'is-paid' : '' }}">
                    <div class="qty">{{ $it['quantity'] }}×</div>
                    <div class="nm">
                        {{ $it['name'] }}
                        <small>
                            #{{ $it['order_number'] }}
                            @if($it['note'])  · 📝 {{ $it['note'] }} @endif
                            @if($it['paid_quantity'] > 0)  · {{ $it['paid_quantity'] }} adet ödendi @endif
                        </small>
                    </div>
                    <div class="pr">{{ number_format($it['unit_price'],2,',','.') }} ₺</div>
                    <div class="pr">{{ number_format($it['subtotal'],2,',','.') }} ₺</div>
                </div>
            @endforeach
        </div>

        <div class="card" style="margin-top:14px">
            <h3 style="margin-bottom:10px"><i class="fas fa-credit-card"></i> Ödemeler ({{ count($bill['payments']) }})</h3>
            @if(empty($bill['payments']))
                <div style="color:var(--c-muted);text-align:center;padding:14px">Henüz ödeme yapılmamış.</div>
            @else
                @foreach($bill['payments'] as $p)
                    <div class="payment-row">
                        <div>
                            <div><strong>{{ number_format($p['amount'],2,',','.') }} ₺</strong> · {{ $p['method_label'] }}</div>
                            <div class="who">
                                {{ $p['portion'] ?? '—' }}
                                @if($p['payer'])  · {{ $p['payer'] }} @endif
                                @if($p['paid_at']) · {{ \Carbon\Carbon::parse($p['paid_at'])->format('d.m H:i') }} @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('cekirdex.panel.bills.void-payment', [$table->id, $p['id']]) }}" onsubmit="return confirm('Bu ödemeyi iptal etmek istediğinize emin misiniz?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-ghost" type="submit" title="İptal et"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- SAĞ: Toplam + ödeme + kapatma --}}
    <div>
        <div class="totals-card">
            <div class="row"><span>Ara toplam</span><span>{{ number_format($bill['subtotal'],2,',','.') }} ₺</span></div>
            @if($bill['tax'] > 0)<div class="row"><span>KDV (%{{ rtrim(rtrim(number_format($bill['tax_rate'],2,'.',''),'0'),'.') }})</span><span>{{ number_format($bill['tax'],2,',','.') }} ₺</span></div>@endif
            @if($bill['service_charge'] > 0)<div class="row"><span>Servis (%{{ rtrim(rtrim(number_format($bill['service_charge_rate'],2,'.',''),'0'),'.') }})</span><span>{{ number_format($bill['service_charge'],2,',','.') }} ₺</span></div>@endif
            <div class="row total"><span>Toplam</span><span>{{ number_format($bill['total'],2,',','.') }} ₺</span></div>
            <div class="bill-progress"><span style="width:{{ $paidPct }}%"></span></div>
            <div class="row paid"><span><i class="fas fa-check"></i> Ödenen</span><span>{{ number_format($bill['paid'],2,',','.') }} ₺</span></div>
            <div class="row remaining"><span><i class="fas fa-clock"></i> Kalan</span><span>{{ number_format($bill['remaining'],2,',','.') }} ₺</span></div>
        </div>

        @if(!$isPaid)
            {{-- Manuel ödeme al --}}
            <div class="card" style="margin-top:14px">
                <h3 style="margin-bottom:10px"><i class="fas fa-hand-holding-dollar"></i> Manuel Ödeme Al</h3>
                <form method="POST" action="{{ route('cekirdex.panel.bills.record-payment', $table->id) }}">
                    @csrf
                    <div class="form-group">
                        <label>Tutar (₺)</label>
                        <input type="number" name="amount" min="0.01" step="0.01" max="{{ $bill['remaining'] }}" value="{{ number_format($bill['remaining'],2,'.','') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Ödeme yöntemi</label>
                        <div class="method-grid" id="manual-method">
                            <label class="is-on"><input type="radio" name="method" value="waiter_cash" checked onchange="setOn(this)"><i class="fas fa-money-bill-1"></i><br>Nakit</label>
                            <label><input type="radio" name="method" value="waiter_card" onchange="setOn(this)"><i class="fas fa-credit-card"></i><br>POS</label>
                            <label><input type="radio" name="method" value="bank_transfer" onchange="setOn(this)"><i class="fas fa-building-columns"></i><br>Havale</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Not (kim ödedi vs.)</label>
                        <input type="text" name="note" maxlength="200" placeholder="örn. Masadaki bayan">
                    </div>
                    <button class="btn btn-primary" type="submit" style="width:100%"><i class="fas fa-check"></i> Ödemeyi Kaydet</button>
                </form>
            </div>
        @else
            {{-- Hesap kapatma formu --}}
            <div class="card" style="margin-top:14px;border:2px solid #10b981">
                <h3 style="margin-bottom:10px;color:#047857"><i class="fas fa-circle-check"></i> Hesabı Kapat & Fatura</h3>
                <p style="color:var(--c-text-soft);margin-bottom:12px">Tüm ödemeler tamamlandı. Faturayı nasıl teslim edeceksin?</p>
                <form method="POST" action="{{ route('cekirdex.panel.bills.close', $table->id) }}">
                    @csrf
                    <div class="delivery-grid">
                        <label class="is-on">
                            <input type="radio" name="delivery_method" value="printed" checked onchange="setOn(this); toggleEmail(false)">
                            <i class="fas fa-print"></i> Yazıcıdan bas
                        </label>
                        <label>
                            <input type="radio" name="delivery_method" value="handed" onchange="setOn(this); toggleEmail(false)">
                            <i class="fas fa-hand-holding"></i> Elden ver
                        </label>
                        <label>
                            <input type="radio" name="delivery_method" value="emailed" onchange="setOn(this); toggleEmail(true)">
                            <i class="fas fa-envelope"></i> E-posta gönder
                        </label>
                        <label>
                            <input type="radio" name="delivery_method" value="none" onchange="setOn(this); toggleEmail(false)">
                            <i class="fas fa-ban"></i> Belge istemiyor
                        </label>
                    </div>
                    <div class="form-group" id="email-row" style="display:none">
                        <label>Müşteri e-posta adresi</label>
                        <input type="email" name="recipient_email" maxlength="160" placeholder="ornek@mail.com">
                    </div>
                    <div class="form-group">
                        <label>Not (opsiyonel)</label>
                        <textarea name="note" rows="2" maxlength="500"></textarea>
                    </div>
                    <button class="btn btn-primary" type="submit" style="width:100%;background:linear-gradient(135deg,#10b981,#047857)">
                        <i class="fas fa-check-double"></i> Hesabı Kapat ve Faturayı Teslim Et
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
function setOn(input) {
    const grp = input.closest('.method-grid, .delivery-grid');
    if (!grp) return;
    grp.querySelectorAll('label').forEach(l => l.classList.remove('is-on'));
    input.parentElement.classList.add('is-on');
}
function toggleEmail(show) {
    document.getElementById('email-row').style.display = show ? '' : 'none';
}
</script>
@endif
@endsection
