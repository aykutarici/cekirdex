@extends('cekirdex.panel.layout')

@section('title', 'Müşteri Yorumları')

@push('styles')
<style>
.rev-stats { display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; margin-bottom:18px; }
.rev-stats .stat { background:#fff; border:1px solid var(--c-line); border-radius:14px; padding:14px; text-align:center; }
.rev-stats .stat .v { font-size:1.6rem; font-weight:800; color:var(--c-text); }
.rev-stats .stat .l { font-size:.8rem; color:var(--c-muted); margin-top:4px; }

.rev-card { background:#fff; border:1px solid var(--c-line); border-radius:12px; padding:14px; margin-bottom:10px; }
.rev-card.is-hidden { background:#fafafa; opacity:.7; }
.rev-card .h { display:flex; gap:10px; align-items:center; margin-bottom:8px; }
.rev-card .h .av { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#fed7aa,#ffd089); display:flex; align-items:center; justify-content:center; font-weight:800; color:#d9531c; flex-shrink:0; }
.rev-card .h .meta { flex:1; }
.rev-card .h .meta strong { display:block; }
.rev-card .h .meta small { color:var(--c-muted); font-size:.78rem; }
.rev-card .body { padding:8px 0; line-height:1.6; color:var(--c-text); }
.rev-card .footer { display:flex; gap:6px; justify-content:flex-end; padding-top:8px; border-top:1px dashed var(--c-line); }
.rev-card .product-tag { display:inline-flex; align-items:center; gap:6px; background:#fff7ed; color:#d9531c; padding:3px 10px; border-radius:99px; font-size:.78rem; font-weight:600; }
.rev-card .stars { color:#f59e0b; font-size:.85rem; margin-left:6px; }

.filter-bar { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
.filter-bar select, .filter-bar input { padding:8px 12px; border:1.5px solid var(--c-line); border-radius:10px; font-size:.88rem; background:#fff; }
.filter-bar a { padding:8px 14px; border-radius:10px; background:#fff; border:1.5px solid var(--c-line); font-size:.85rem; color:var(--c-text-soft); text-decoration:none; font-weight:600; }
.filter-bar a.is-on { background:linear-gradient(135deg,#ff8a4c,#ff6b35); color:#fff; border-color:transparent; }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1><i class="fas fa-comments"></i> Müşteri Yorumları</h1>
        <div class="sub">Menüdeki ürünlerinize yapılan yorumları yönetin.</div>
    </div>
    <a class="btn btn-ghost" href="{{ route('cekirdex.panel.dashboard') }}"><i class="fas fa-arrow-left"></i> Anasayfa</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="rev-stats">
    <div class="stat"><div class="v">{{ $stats['total'] }}</div><div class="l">Toplam Yorum</div></div>
    <div class="stat"><div class="v" style="color:#10b981">{{ $stats['visible'] }}</div><div class="l">Görünür</div></div>
    <div class="stat"><div class="v" style="color:#ef4444">{{ $stats['hidden'] }}</div><div class="l">Gizli</div></div>
</div>

<form method="GET" class="filter-bar">
    <select name="product_id" onchange="this.form.submit()">
        <option value="">Tüm ürünler</option>
        @foreach($products as $p)
            <option value="{{ $p->id }}" @if(request('product_id')==$p->id) selected @endif>{{ $p->name }}</option>
        @endforeach
    </select>
    <a href="{{ route('cekirdex.panel.reviews.index', request()->except('hidden')) }}" class="@if(!request('hidden')) is-on @endif">Görünür</a>
    <a href="{{ route('cekirdex.panel.reviews.index', array_merge(request()->except('hidden'),['hidden'=>'1'])) }}" class="@if(request('hidden')==='1') is-on @endif">Gizli</a>
</form>

@if($reviews->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:var(--c-muted)">
        <i class="fas fa-comment-slash" style="font-size:2rem;margin-bottom:8px"></i>
        <div>Henüz yorum yok.</div>
    </div>
@else
    @foreach($reviews as $r)
        <div class="rev-card {{ $r->is_visible ? '' : 'is-hidden' }}">
            <div class="h">
                <div class="av">{{ mb_strtoupper(mb_substr($r->user?->name ?? '?', 0, 1)) }}</div>
                <div class="meta">
                    <strong>{{ $r->user?->name ?? 'Misafir' }}</strong>
                    <small>{{ $r->user?->phone ?? '' }} · {{ $r->created_at->format('d.m.Y H:i') }}</small>
                </div>
                <div>
                    <span class="product-tag"><i class="fas fa-utensils"></i> {{ $r->product?->name ?? '—' }}</span>
                    @if($r->rating)<span class="stars">@for($i=1;$i<=5;$i++)<i class="fa{{ $i <= $r->rating ? 's':'r' }} fa-star"></i>@endfor</span>@endif
                </div>
            </div>
            <div class="body">{{ $r->content }}</div>
            <div class="footer">
                <form method="POST" action="{{ route('cekirdex.panel.reviews.toggle', $r->id) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-sm btn-ghost" type="submit">
                        <i class="fas fa-{{ $r->is_visible ? 'eye-slash' : 'eye' }}"></i>
                        {{ $r->is_visible ? 'Gizle' : 'Tekrar Göster' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('cekirdex.panel.reviews.destroy', $r->id) }}" style="display:inline" onsubmit="return confirm('Bu yorumu kalıcı olarak silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-ghost" style="color:#ef4444"><i class="fas fa-trash"></i> Sil</button>
                </form>
            </div>
        </div>
    @endforeach

    <div style="margin-top:14px">{{ $reviews->links() }}</div>
@endif
@endsection
