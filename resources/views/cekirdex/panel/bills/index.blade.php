@extends('cekirdex.panel.layout')

@section('title', 'Hesaplar / Adisyonlar')

@push('styles')
<style>
.bill-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap:14px; }
.bill-card { background:#fff; border:1px solid var(--c-line); border-radius:14px; padding:16px; box-shadow:0 6px 16px -10px rgba(0,0,0,.08); }
.bill-card.is-open { border-color:#fcd34d; background:linear-gradient(135deg,#fffbeb,#fff); }
.bill-card.is-paid-full { border-color:#86efac; background:linear-gradient(135deg,#ecfdf5,#fff); }
.bill-card .name { font-weight:800; font-size:1.05rem; color:var(--c-text); margin-bottom:6px; display:flex; justify-content:space-between; align-items:center; }
.bill-card .stat { display:flex; justify-content:space-between; font-size:.85rem; padding:3px 0; color:var(--c-text-soft); }
.bill-card .stat strong { color:var(--c-text); }
.bill-card .actions { display:flex; gap:6px; margin-top:12px; }
.bill-card .actions a { flex:1; text-align:center; padding:8px; border-radius:10px; font-size:.82rem; font-weight:600; text-decoration:none; }
.bill-card .pill { font-size:.7rem; font-weight:700; padding:2px 9px; border-radius:99px; }
.pill-open { background:#fef3c7; color:#92400e; }
.pill-empty { background:#f5f5f4; color:#57534e; }
.pill-paid { background:#dcfce7; color:#166534; }
.section-h { margin: 22px 0 10px; font-size:1.05rem; font-weight:700; color:var(--c-text); display:flex; align-items:center; gap:8px; }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1>Hesaplar</h1>
        <div class="sub">Aktif adisyonları yönet, ödemeleri al, masaları kapat.</div>
    </div>
    <a class="btn btn-ghost" href="{{ route('cekirdex.panel.dashboard') }}"><i class="fas fa-arrow-left"></i> Anasayfa</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@php
    $openBills    = $tables->filter(fn ($x) => $x['open']);
    $closedTables = $tables->filter(fn ($x) => !$x['open']);
@endphp

<div class="section-h"><i class="fas fa-fire" style="color:#f59e0b"></i> Açık Adisyonlar ({{ $openBills->count() }})</div>

@if($openBills->isEmpty())
    <div class="card" style="text-align:center;padding:30px;color:var(--c-muted)">Şu an açık adisyon yok.</div>
@else
    <div class="bill-grid">
        @foreach($openBills as $row)
            @php $t = $row['table']; $isPaid = $row['remaining'] < 0.01; @endphp
            <div class="bill-card {{ $isPaid ? 'is-paid-full' : 'is-open' }}">
                <div class="name">
                    <span><i class="fas fa-chair" style="color:#94a3b8"></i> {{ $t->name }}</span>
                    @if($isPaid)
                        <span class="pill pill-paid"><i class="fas fa-check"></i> Ödendi · Kapatılmayı bekliyor</span>
                    @else
                        <span class="pill pill-open">{{ $row['order_count'] }} sipariş</span>
                    @endif
                </div>
                <div class="stat"><span>Toplam</span><strong>{{ number_format($row['total'],2,',','.') }} ₺</strong></div>
                <div class="stat"><span style="color:#047857">Ödenen</span><strong style="color:#047857">{{ number_format($row['paid'],2,',','.') }} ₺</strong></div>
                <div class="stat"><span style="color:#b91c1c">Kalan</span><strong style="color:#b91c1c">{{ number_format($row['remaining'],2,',','.') }} ₺</strong></div>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('cekirdex.panel.bills.show', $t->id) }}"><i class="fas fa-eye"></i> Aç</a>
                    <a class="btn btn-ghost" href="{{ route('cekirdex.panel.bills.waiter-order', $t->id) }}"><i class="fas fa-plus"></i> Sipariş Ekle</a>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="section-h"><i class="fas fa-chair" style="color:#94a3b8"></i> Boş Masalar ({{ $closedTables->count() }})</div>
<div class="bill-grid">
    @foreach($closedTables as $row)
        @php $t = $row['table']; @endphp
        <div class="bill-card">
            <div class="name">
                <span><i class="fas fa-chair" style="color:#cbd5e1"></i> {{ $t->name }}</span>
                <span class="pill pill-empty">Boş</span>
            </div>
            <div class="stat"><span style="color:var(--c-muted)">Adisyon yok</span></div>
            <div class="actions">
                <a class="btn btn-primary" href="{{ route('cekirdex.panel.bills.waiter-order', $t->id) }}"><i class="fas fa-plus"></i> Sipariş Aç</a>
            </div>
        </div>
    @endforeach
</div>

@if($recentClosures->isNotEmpty())
    <div class="section-h"><i class="fas fa-receipt" style="color:#10b981"></i> Son Kapatılan Adisyonlar</div>
    <div class="card">
        <table class="pp-table">
            <thead>
                <tr>
                    <th>Masa</th>
                    <th>Toplam</th>
                    <th>Ödenen</th>
                    <th>Teslim</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentClosures as $c)
                    <tr>
                        <td><strong>{{ optional($c->table)->name ?? '—' }}</strong></td>
                        <td>{{ number_format($c->total,2,',','.') }} ₺</td>
                        <td>{{ number_format($c->paid,2,',','.') }} ₺</td>
                        <td>{{ $c->delivery_label }}{{ $c->email_sent ? ' ✓' : '' }}</td>
                        <td class="muted">{{ $c->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
