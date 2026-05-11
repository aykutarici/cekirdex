@extends('cekirdex.panel.layout')

@section('title', 'Menü')

@push('styles')
<style>
.menu-grid { display: grid; grid-template-columns: 280px 1fr; gap: 22px; }
@media (max-width: 900px) { .menu-grid { grid-template-columns: 1fr; } }
.cat-list a { display: block; padding: 10px 14px; border-radius: 10px; color: var(--c-text-soft); font-weight: 600; font-size: .92rem; margin-bottom: 4px; cursor: pointer; }
.cat-list a:hover { background: #fff8f1; color: var(--c-accent-d); text-decoration: none; }
.cat-list a.is-active { background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff !important; }
.product-card {
    background: #fff; border: 1px solid var(--c-line); border-radius: 14px;
    padding: 14px 16px; margin-bottom: 10px;
    display: grid; grid-template-columns: 60px 1fr auto; gap: 14px; align-items: center;
}
.product-card .img { width: 60px; height: 60px; border-radius: 10px; background: linear-gradient(135deg,#ffe5cc,#ffd0a8); display: flex; align-items: center; justify-content: center; color: var(--c-accent-d); font-size: 1.2rem; overflow: hidden; }
.product-card .img img { width: 100%; height: 100%; object-fit: cover; }
.product-card .name { font-weight: 700; font-size: .96rem; }
.product-card .desc { color: var(--c-muted); font-size: .82rem; margin-top: 2px; }
.product-card .price { font-weight: 800; color: var(--c-accent-d); font-size: 1rem; }
.empty-state { text-align: center; padding: 40px 20px; color: var(--c-muted); }
.empty-state i { font-size: 2.4rem; color: #fde9d6; margin-bottom: 10px; display: block; }
details summary { cursor: pointer; padding: 12px 0; font-weight: 700; }
details summary::-webkit-details-marker { display: none; }
details summary::before { content: '+'; margin-right: 8px; color: var(--c-accent-d); font-size: 1.2rem; transition: transform .2s; display: inline-block; }
details[open] summary::before { transform: rotate(45deg); }

/* Görsel seçici (image picker) */
.img-picker {
    display: grid; grid-template-columns: 84px 1fr; gap: 12px;
    border: 1.5px dashed var(--c-line-2); border-radius: 12px;
    padding: 10px; background: #fff;
}
.img-picker .preview {
    width: 84px; height: 84px; border-radius: 10px; overflow: hidden;
    background: linear-gradient(135deg,#ffe5cc,#ffd0a8);
    display: flex; align-items: center; justify-content: center;
    color: var(--c-accent-d); font-size: 1.5rem;
}
.img-picker .preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
.img-picker .meta { display: flex; flex-direction: column; gap: 6px; justify-content: center; }
.img-picker .meta .lbl { font-size: .8rem; color: var(--c-muted); }
.img-picker .meta .val { font-size: .82rem; color: var(--c-text); font-weight: 600; word-break: break-all; }
.img-picker .meta .actions { display: flex; gap: 6px; flex-wrap: wrap; }
.img-picker .meta .actions button { font-size: .82rem; padding: 6px 10px; border-radius: 8px; cursor: pointer; border: 0; font-weight: 600; }
.img-picker .meta .actions .pick { background: linear-gradient(135deg,#ff8a4c,#ff6b35); color: #fff; }
.img-picker .meta .actions .clear { background: #fff; border: 1px solid var(--c-line-2); color: var(--c-text-soft); }
.img-picker input[type=file] { display: none; }

/* Modal */
.imodal { position: fixed; inset: 0; background: rgba(28,25,51,.55); z-index: 200; display: none; align-items: center; justify-content: center; padding: 16px; }
.imodal.open { display: flex; }
.imodal .box {
    background: #fff; width: 100%; max-width: 880px;
    max-height: 92vh; border-radius: 18px; overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 28px 60px -16px rgba(0,0,0,.4);
}
.imodal .h { padding: 16px 20px; border-bottom: 1px solid var(--c-line); display: flex; justify-content: space-between; align-items: center; }
.imodal .h h3 { font-size: 1.05rem; }
.imodal .h .x { background: transparent; border: 0; font-size: 1.4rem; color: var(--c-muted); cursor: pointer; }
.imodal .tabs { display: flex; gap: 4px; padding: 12px 20px 0; border-bottom: 1px solid var(--c-line); }
.imodal .tabs button { background: transparent; border: 0; padding: 10px 14px; cursor: pointer; font-weight: 700; color: var(--c-muted); border-bottom: 3px solid transparent; }
.imodal .tabs button.is-active { color: var(--c-accent-d); border-color: var(--c-accent); }
.imodal .body { padding: 18px 20px 22px; overflow-y: auto; flex: 1; }

.tab-pane { display: none; }
.tab-pane.is-active { display: block; }
.tab-toolbar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
.tab-toolbar input[type=search] { flex: 1; min-width: 180px; }
.tab-toolbar select { min-width: 160px; }

.thumb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
.thumb {
    border: 2px solid transparent; border-radius: 12px; padding: 6px;
    background: #fff; cursor: pointer; transition: all .15s;
    display: flex; flex-direction: column; gap: 6px; align-items: center;
}
.thumb:hover { border-color: var(--c-line-2); background: #fff8f1; }
.thumb.is-selected { border-color: var(--c-accent); box-shadow: 0 0 0 3px rgba(255,107,53,.18); background: #fff8f1; }
.thumb img { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-radius: 8px; display: block; background: #f4f1ea; }
.thumb .name { font-size: .78rem; color: var(--c-text); font-weight: 600; text-align: center; line-height: 1.3; }
.thumb .cat { font-size: .68rem; color: var(--c-muted); text-transform: uppercase; letter-spacing: .04em; }

.upload-zone {
    border: 2px dashed var(--c-line-2); border-radius: 12px;
    padding: 36px 20px; text-align: center; cursor: pointer;
    transition: all .2s; background: #fafafe;
}
.upload-zone:hover { border-color: var(--c-accent); background: #fff8f1; }
.upload-zone.dragover { border-color: var(--c-accent); background: #fff3eb; }
.upload-zone i { font-size: 2.2rem; color: var(--c-accent-d); display: block; margin-bottom: 8px; }
.upload-zone p { color: var(--c-text-soft); font-size: .92rem; margin: 0; }
.upload-zone small { color: var(--c-muted); font-size: .82rem; }
.upload-preview { margin-top: 14px; display: flex; align-items: center; gap: 10px; padding: 10px; background: #fff; border: 1px solid var(--c-line); border-radius: 10px; }
.upload-preview img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
.upload-preview .nm { font-weight: 600; font-size: .88rem; }
.upload-preview .sz { color: var(--c-muted); font-size: .78rem; }

.imodal .foot { padding: 14px 20px; border-top: 1px solid var(--c-line); display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
.imodal .foot .hint { color: var(--c-muted); font-size: .82rem; }
.imodal .foot .actions { display: flex; gap: 8px; }
.empty-thumbs { text-align: center; padding: 40px 20px; color: var(--c-muted); }
.empty-thumbs i { font-size: 2rem; display: block; margin-bottom: 8px; color: #e2dccf; }
.loading { text-align: center; padding: 40px 20px; color: var(--c-muted); }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1>Menü Yönetimi</h1>
        <div class="sub">Kategoriler, ürünler, fiyatlar — hepsi burada.</div>
    </div>
</div>

<div class="menu-grid">
    {{-- Kategoriler --}}
    <div class="card">
        <h2><i class="fas fa-folder-tree"></i> Kategoriler ({{ $categories->count() }})</h2>
        <div class="cat-list" style="margin-bottom:14px">
            <a class="is-active">Tümü <small style="float:right;color:rgba(255,255,255,.85)">{{ $products->count() }}</small></a>
            @foreach($categories as $c)
                <a>{{ $c->name }} <small style="float:right;color:var(--c-muted)">{{ $c->products_count }}</small></a>
            @endforeach
        </div>

        <details>
            <summary><i class="fas fa-plus"></i> Yeni kategori ekle</summary>
            <form method="POST" action="{{ route('cekirdex.panel.menu.category.store') }}" style="margin-top:10px">
                @csrf
                <div class="form-block">
                    <label>Kategori adı</label>
                    <input type="text" name="name" required maxlength="120" placeholder="Örn. Ana Yemekler">
                </div>
                <div class="form-block">
                    <label>Açıklama</label>
                    <input type="text" name="description" maxlength="1000">
                </div>
                <div class="form-block">
                    <label>Sıralama</label>
                    <input type="number" name="sort_order" value="0" min="0" max="999">
                </div>
                <label style="display:flex;align-items:center;gap:6px;font-size:.9rem"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
                <button type="submit" class="btn" style="margin-top:10px;width:100%"><i class="fas fa-plus"></i> Ekle</button>
            </form>
        </details>

        @if($categories->isNotEmpty())
        <details style="margin-top:8px">
            <summary><i class="fas fa-pen"></i> Kategorileri düzenle</summary>
            <div style="margin-top:10px">
                @foreach($categories as $c)
                <div style="border:1px solid var(--c-line);border-radius:10px;padding:10px 12px;margin-bottom:8px">
                    <form method="POST" action="{{ route('cekirdex.panel.menu.category.update', $c->id) }}">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $c->name }}" required style="margin-bottom:6px">
                        <input type="number" name="sort_order" value="{{ $c->sort_order }}" min="0" max="999" style="margin-bottom:6px;max-width:100px">
                        <label style="display:flex;align-items:center;gap:6px;font-size:.85rem;margin:6px 0">
                            <input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Aktif
                        </label>
                        <div style="display:flex;gap:6px">
                            <button type="submit" class="btn btn-sm" style="flex:1">Güncelle</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('cekirdex.panel.menu.category.destroy', $c->id) }}" style="margin-top:6px" onsubmit="return confirm('Bu kategoriyi silmek istiyor musunuz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" style="width:100%"><i class="fas fa-trash"></i> Sil</button>
                    </form>
                </div>
                @endforeach
            </div>
        </details>
        @endif
    </div>

    {{-- Ürünler --}}
    <div class="card">
        <h2><i class="fas fa-utensils"></i> Ürünler ({{ $products->count() }})</h2>

        <details {{ $products->count() === 0 ? 'open' : '' }}>
            <summary><i class="fas fa-plus"></i> Yeni ürün ekle</summary>
            @if($categories->isEmpty())
                <p style="color:var(--c-muted);margin-top:8px">Önce en az bir kategori eklemelisin.</p>
            @else
            <form method="POST" action="{{ route('cekirdex.panel.menu.product.store') }}" enctype="multipart/form-data" style="margin-top:14px">
                @csrf
                <div class="form-row">
                    <div>
                        <label>Ürün adı *</label>
                        <input type="text" name="name" required maxlength="160">
                    </div>
                    <div>
                        <label>Kategori *</label>
                        <select name="cekirdex_category_id" required>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="form-block">
                    <label>Açıklama</label>
                    <textarea name="description" rows="2" maxlength="2000"></textarea>
                </div>
                <div class="form-row">
                    <div>
                        <label>Fiyat (₺) *</label>
                        <input type="number" step="0.01" min="0" name="price" required>
                    </div>
                    <div>
                        <label>İndirimli fiyat</label>
                        <input type="number" step="0.01" min="0" name="discount_price">
                    </div>
                </div>
                <div class="form-block">
                    <label>Hazırlık süresi (dakika)</label>
                    <input type="number" min="0" max="600" name="preparation_minutes" value="0" style="max-width:200px">
                </div>

                <div class="form-block">
                    <label>Görsel</label>
                    <div class="img-picker" data-picker="new">
                        <div class="preview" data-preview><i class="fas fa-utensils"></i></div>
                        <div class="meta">
                            <div class="lbl">Görsel kaynağı</div>
                            <div class="val" data-value-label>Görsel yok</div>
                            <div class="actions">
                                <button type="button" class="pick" onclick="openImagePicker(this.closest('.img-picker'))"><i class="fas fa-image"></i> Görsel seç</button>
                                <button type="button" class="clear" onclick="clearImagePicker(this.closest('.img-picker'))">Temizle</button>
                            </div>
                        </div>
                        <input type="hidden" name="image_source" value="none">
                        <input type="hidden" name="image_value" value="">
                        <input type="file" name="image" accept="image/*" data-file>
                    </div>
                </div>

                <div style="display:flex;gap:14px;margin-top:8px;font-size:.88rem;flex-wrap:wrap">
                    <label><input type="checkbox" name="is_popular" value="1"> Popüler</label>
                    <label><input type="checkbox" name="is_new" value="1"> Yeni</label>
                    <label><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
                    <label><input type="checkbox" name="is_in_stock" value="1" checked> Stokta var</label>
                </div>
                <div class="form-row" style="margin-top:10px;align-items:flex-end;gap:14px">
                    <div>
                        <label style="display:flex;align-items:center;gap:8px;font-size:.88rem">
                            <input type="checkbox" name="track_stock" value="1"> Adet stok takibi
                        </label>
                        <small style="color:var(--c-muted)">İşaretlerseniz sipişler stoktan düşer; miktar 0 olunca ürün otomatik tükenir.</small>
                    </div>
                    <div>
                        <label>Stok adedi (opsiyonel)</label>
                        <input type="number" name="stock_quantity" min="0" max="999999" placeholder="Boş = sınırsız" style="max-width:160px">
                    </div>
                </div>

                <div class="form-block" style="margin-top:14px">
                    <label style="font-weight:700;margin-bottom:8px;display:block">Allergen / Diyet Etiketleri</label>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        @foreach (\App\Cekirdex\Models\CekirdexProduct::ALLERGENS as $key => $info)
                            <label style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid var(--c-line-2);border-radius:99px;font-size:.84rem;cursor:pointer">
                                <input type="checkbox" name="allergens[]" value="{{ $key }}" style="margin:0">
                                <span>{{ $info[1] }}</span><span>{{ $info[0] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-block">
                    <label style="font-weight:700;margin-bottom:8px;display:block">Varyasyonlar (opsiyonel)</label>
                    <small style="color:var(--c-muted);display:block;margin-bottom:8px">Ürünün boy/seçenek varyasyonları. Müşteri ürün eklerken seçer. Fiyat farkı ana fiyata uygulanır.</small>
                    <div data-variants-list></div>
                    <button type="button" class="btn btn-sm btn-ghost" onclick="addVariantRow(this)">
                        <i class="fas fa-plus"></i> Varyasyon ekle
                    </button>
                </div>

                <button type="submit" class="btn" style="margin-top:14px"><i class="fas fa-plus"></i> Ürünü ekle</button>
            </form>
            @endif
        </details>

        <div style="margin-top:18px">
            @if($products->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    <p>Henüz ürün yok. Yukarıdan ilk ürününüzü ekleyin.</p>
                </div>
            @else
                @foreach($products as $p)
                <details class="product-card-wrap" style="display:block;margin:0 0 10px">
                    <summary style="list-style:none;padding:0">
                        <div class="product-card">
                            <div class="img">
                                @if($p->image_url)
                                    <img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy">
                                @else
                                    <i class="fas fa-utensils"></i>
                                @endif
                            </div>
                            <div>
                                <div class="name">
                                    {{ $p->name }}
                                    @if(!($p->is_in_stock ?? true))
                                        <span style="background:#fee2e2;color:#991b1b;font-size:.7rem;padding:2px 8px;border-radius:99px;font-weight:700;margin-left:6px">Bugün Yok</span>
                                    @endif
                                    @if($p->variants->isNotEmpty())
                                        <span style="background:#ede9fe;color:#5b21b6;font-size:.7rem;padding:2px 8px;border-radius:99px;font-weight:700;margin-left:6px">{{ $p->variants->count() }} varyasyon</span>
                                    @endif
                                </div>
                                <div class="desc">{{ optional($p->category)->name }} · {{ Str::limit($p->description, 60) }}</div>
                                @if(!empty($p->allergens))
                                    <div style="margin-top:4px;display:flex;gap:4px;flex-wrap:wrap">
                                        @foreach($p->allergens as $a)
                                            @php $info = \App\Cekirdex\Models\CekirdexProduct::ALLERGENS[$a] ?? null; @endphp
                                            @if($info)
                                                <span style="background:#fff3eb;color:#7c2d12;font-size:.68rem;padding:2px 7px;border-radius:99px">{{ $info[1] }} {{ $info[0] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div style="text-align:right">
                                <div class="price">{{ number_format((float) ($p->discount_price ?: $p->price), 2, ',', '.') }} ₺</div>
                                @if(!$p->is_active)<small style="color:#94a3b8">Pasif</small>@endif
                                @if($p->is_popular)<small style="color:#d9531c"><i class="fas fa-star"></i></small>@endif
                                <button type="button"
                                    onclick="event.preventDefault(); event.stopPropagation(); toggleStock({{ $p->id }}, this);"
                                    title="{{ ($p->is_in_stock ?? true) ? 'Bugün için kaldır' : 'Tekrar satışa al' }}"
                                    style="display:block;margin-top:4px;background:{{ ($p->is_in_stock ?? true) ? 'transparent' : '#dcfce7' }};border:1px solid var(--c-line-2);border-radius:8px;padding:4px 8px;font-size:.72rem;cursor:pointer;color:{{ ($p->is_in_stock ?? true) ? 'var(--c-text-soft)' : '#166534' }};font-weight:600">
                                    <i class="fas fa-{{ ($p->is_in_stock ?? true) ? 'circle-pause' : 'circle-check' }}"></i>
                                    {{ ($p->is_in_stock ?? true) ? 'Yok yap' : 'Var yap' }}
                                </button>
                            </div>
                        </div>
                    </summary>
                    <form method="POST" action="{{ route('cekirdex.panel.menu.product.update', $p->id) }}" enctype="multipart/form-data" style="background:#fafafe;padding:18px;border:1px solid var(--c-line);border-top:0;border-radius:0 0 14px 14px;margin-top:-2px">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div><label>Ürün adı</label><input type="text" name="name" value="{{ $p->name }}" required></div>
                            <div><label>Kategori</label>
                                <select name="cekirdex_category_id" required>
                                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected($p->cekirdex_category_id == $c->id)>{{ $c->name }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-block"><label>Açıklama</label><textarea name="description" rows="2">{{ $p->description }}</textarea></div>
                        <div class="form-row">
                            <div><label>Fiyat</label><input type="number" step="0.01" name="price" value="{{ $p->price }}" required></div>
                            <div><label>İndirimli fiyat</label><input type="number" step="0.01" name="discount_price" value="{{ $p->discount_price }}"></div>
                        </div>
                        <div class="form-block">
                            <label>Hazırlık süresi</label>
                            <input type="number" name="preparation_minutes" value="{{ $p->preparation_minutes }}" style="max-width:200px">
                        </div>
                        <div class="form-block">
                            <label>Görsel</label>
                            <div class="img-picker" data-picker="edit-{{ $p->id }}">
                                <div class="preview" data-preview>
                                    @if($p->image_url)
                                        <img src="{{ $p->image_url }}" alt="">
                                    @else
                                        <i class="fas fa-utensils"></i>
                                    @endif
                                </div>
                                <div class="meta">
                                    <div class="lbl">Görsel kaynağı</div>
                                    <div class="val" data-value-label>
                                        @if($p->image)
                                            @if(str_starts_with($p->image, 'stock:'))Stok görsel @else Yüklenmiş görsel @endif
                                        @else
                                            Görsel yok
                                        @endif
                                    </div>
                                    <div class="actions">
                                        <button type="button" class="pick" onclick="openImagePicker(this.closest('.img-picker'))"><i class="fas fa-image"></i> Görsel seç</button>
                                        <button type="button" class="clear" onclick="clearImagePicker(this.closest('.img-picker'))">Temizle</button>
                                    </div>
                                </div>
                                <input type="hidden" name="image_source" value="none">
                                <input type="hidden" name="image_value" value="">
                                <input type="hidden" name="remove_image" value="0" data-remove>
                                <input type="file" name="image" accept="image/*" data-file>
                            </div>
                        </div>
                        <div style="display:flex;gap:14px;margin:8px 0;font-size:.88rem;flex-wrap:wrap">
                            <label><input type="checkbox" name="is_popular" value="1" @checked($p->is_popular)> Popüler</label>
                            <label><input type="checkbox" name="is_new" value="1" @checked($p->is_new)> Yeni</label>
                            <label><input type="checkbox" name="is_active" value="1" @checked($p->is_active)> Aktif</label>
                            <label><input type="checkbox" name="is_in_stock" value="1" @checked($p->is_in_stock ?? true)> Stokta var</label>
                        </div>
                        <div class="form-row" style="margin-top:10px;align-items:flex-end;gap:14px">
                            <div>
                                <label style="display:flex;align-items:center;gap:8px;font-size:.88rem">
                                    <input type="checkbox" name="track_stock" value="1" @checked($p->track_stock)> Adet stok takibi
                                </label>
                                <small style="color:var(--c-muted)">Siparişler stoktan düşer.</small>
                            </div>
                            <div>
                                <label>Stok adedi</label>
                                <input type="number" name="stock_quantity" min="0" max="999999" value="{{ $p->stock_quantity !== null ? $p->stock_quantity : '' }}" placeholder="Boş = sınırsız" style="max-width:160px">
                            </div>
                        </div>

                        <div class="form-block" style="margin-top:14px">
                            <label style="font-weight:700;margin-bottom:8px;display:block">Allergen / Diyet Etiketleri</label>
                            <div style="display:flex;flex-wrap:wrap;gap:8px">
                                @foreach (\App\Cekirdex\Models\CekirdexProduct::ALLERGENS as $key => $info)
                                    <label style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid var(--c-line-2);border-radius:99px;font-size:.84rem;cursor:pointer">
                                        <input type="checkbox" name="allergens[]" value="{{ $key }}" @checked(in_array($key, (array)($p->allergens ?? []))) style="margin:0">
                                        <span>{{ $info[1] }}</span><span>{{ $info[0] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-block">
                            <label style="font-weight:700;margin-bottom:8px;display:block">Varyasyonlar (opsiyonel)</label>
                            <div data-variants-list>
                                @foreach($p->variants as $i => $v)
                                    <div style="display:grid;grid-template-columns:1fr 130px auto auto;gap:6px;margin-bottom:6px;align-items:center">
                                        <input type="text" name="variants[{{ $i }}][name]" value="{{ $v->name }}" placeholder="Boy / seçenek (örn. Büyük)">
                                        <input type="number" step="0.01" name="variants[{{ $i }}][price_adjust]" value="{{ $v->price_adjust }}" placeholder="Fiyat farkı (₺)">
                                        <label style="font-size:.82rem;display:flex;align-items:center;gap:4px;white-space:nowrap"><input type="checkbox" name="variants[{{ $i }}][is_default]" value="1" @checked($v->is_default)> Varsayılan</label>
                                        <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2;color:#991b1b;border:0;border-radius:8px;padding:6px 10px;cursor:pointer"><i class="fas fa-trash"></i></button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="addVariantRow(this)">
                                <i class="fas fa-plus"></i> Varyasyon ekle
                            </button>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:8px">
                            <button type="submit" class="btn btn-sm" style="flex:1"><i class="fas fa-save"></i> Güncelle</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('cekirdex.panel.menu.product.destroy', $p->id) }}" style="margin-top:6px" onsubmit="return confirm('Bu ürünü silmek istiyor musunuz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" style="width:100%"><i class="fas fa-trash"></i> Sil</button>
                    </form>
                </details>
                @endforeach
            @endif
        </div>
    </div>
</div>

{{-- Görsel Seçici Modal --}}
<div class="imodal" id="img-modal" aria-hidden="true">
    <div class="box">
        <div class="h">
            <h3><i class="fas fa-image" style="color:var(--c-accent-d)"></i> Görsel seç</h3>
            <button class="x" onclick="closeImagePicker()"><i class="fas fa-times"></i></button>
        </div>
        <div class="tabs">
            <button type="button" class="is-active" data-tab="stock" onclick="switchImTab('stock')"><i class="fas fa-photo-film"></i> Stok</button>
            <button type="button" data-tab="gallery" onclick="switchImTab('gallery')"><i class="fas fa-images"></i> Galerim</button>
            <button type="button" data-tab="upload" onclick="switchImTab('upload')"><i class="fas fa-cloud-arrow-up"></i> Yükle</button>
        </div>
        <div class="body">
            <div class="tab-pane is-active" data-pane="stock">
                <div class="tab-toolbar">
                    <input type="search" id="im-q" placeholder="Ara: hamburger, kahve, salata…" oninput="searchStock()">
                    <select id="im-cat" onchange="searchStock()">
                        <option value="all">Tüm kategoriler</option>
                    </select>
                </div>
                <div class="thumb-grid" id="im-stock"><div class="loading"><i class="fas fa-spinner fa-spin"></i> Yükleniyor…</div></div>
            </div>
            <div class="tab-pane" data-pane="gallery">
                <p style="color:var(--c-muted);font-size:.88rem;margin-bottom:12px">Daha önce yüklediğiniz ürün görselleri.</p>
                <div class="thumb-grid" id="im-gallery"></div>
            </div>
            <div class="tab-pane" data-pane="upload">
                <div class="upload-zone" id="im-drop" onclick="document.getElementById('im-file').click()">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <p><strong>Tıkla ya da sürükleyip bırak</strong></p>
                    <small>JPG, PNG, WEBP · maks. 4 MB</small>
                </div>
                <input type="file" id="im-file" accept="image/*" hidden onchange="handleUploadFile(this.files[0])">
                <div id="im-upload-preview"></div>
            </div>
        </div>
        <div class="foot">
            <span class="hint" id="im-hint">Bir görsel seç ve "Onayla"ya bas.</span>
            <div class="actions">
                <button type="button" class="btn btn-ghost" onclick="closeImagePicker()">Vazgeç</button>
                <button type="button" class="btn" id="im-confirm" onclick="confirmImage()" disabled><i class="fas fa-check"></i> Onayla</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const STOCK_BROWSE_URL = @json(route('cekirdex.panel.stock.browse'));

let imState = {
    targetPicker: null,
    activeTab: 'stock',
    selection: null, // { source, value, url, label }
    stock: [],
    gallery: [],
    cats: [],
    pendingFile: null, // File obj
};

function openImagePicker(pickerEl) {
    imState.targetPicker = pickerEl;
    imState.selection = null;
    document.getElementById('im-modal').classList.add('open');
    document.getElementById('im-confirm').disabled = true;
    document.getElementById('im-hint').textContent = 'Bir görsel seç ve "Onayla"ya bas.';
    if (imState.stock.length === 0) loadBrowse('');
    else renderStock();
}
function closeImagePicker() {
    document.getElementById('im-modal').classList.remove('open');
    imState.pendingFile = null;
    document.getElementById('im-upload-preview').innerHTML = '';
}
function clearImagePicker(pickerEl) {
    setPreviewIcon(pickerEl);
    pickerEl.querySelector('[data-value-label]').textContent = 'Görsel yok';
    pickerEl.querySelector('input[name=image_source]').value = 'none';
    pickerEl.querySelector('input[name=image_value]').value = '';
    const fileInput = pickerEl.querySelector('input[type=file]');
    fileInput.value = '';
    const removeFlag = pickerEl.querySelector('input[data-remove]');
    if (removeFlag) removeFlag.value = '1';
}

function setPreviewIcon(pickerEl) {
    pickerEl.querySelector('[data-preview]').innerHTML = '<i class="fas fa-utensils"></i>';
}
function setPreviewImage(pickerEl, url) {
    pickerEl.querySelector('[data-preview]').innerHTML = '<img src="'+url+'" alt="">';
}

function switchImTab(tab) {
    imState.activeTab = tab;
    document.querySelectorAll('#img-modal .tabs button').forEach(b => b.classList.toggle('is-active', b.dataset.tab === tab));
    document.querySelectorAll('#img-modal .tab-pane').forEach(p => p.classList.toggle('is-active', p.dataset.pane === tab));
}

async function loadBrowse(q) {
    try {
        const url = STOCK_BROWSE_URL + (q ? ('?q=' + encodeURIComponent(q)) : '');
        const r = await fetch(url, { headers: { Accept: 'application/json' }});
        const d = await r.json();
        imState.stock = d.stock || [];
        imState.gallery = d.gallery || [];
        imState.cats = d.categories || [];
        const sel = document.getElementById('im-cat');
        if (sel.options.length <= 1) {
            imState.cats.forEach(c => {
                const o = document.createElement('option');
                o.value = c.key; o.textContent = c.label;
                sel.appendChild(o);
            });
        }
        renderStock();
        renderGallery();
    } catch(e) {
        document.getElementById('im-stock').innerHTML = '<div class="empty-thumbs"><i class="fas fa-circle-exclamation"></i> Yüklenemedi.</div>';
    }
}

function searchStock() {
    const q = document.getElementById('im-q').value.trim();
    const cat = document.getElementById('im-cat').value;
    let list = imState.stock;
    if (cat && cat !== 'all') list = list.filter(s => s.category === cat);
    if (q) {
        const lq = q.toLowerCase();
        list = list.filter(s => s.name.toLowerCase().includes(lq) || s.slug.toLowerCase().includes(lq));
    }
    renderStock(list);
}

function renderStock(list) {
    const wrap = document.getElementById('im-stock');
    const items = list || imState.stock;
    if (items.length === 0) {
        wrap.innerHTML = '<div class="empty-thumbs"><i class="fas fa-magnifying-glass"></i> Eşleşen görsel yok.</div>';
        return;
    }
    wrap.innerHTML = items.map(s => `
        <button type="button" class="thumb" data-source="stock" data-value="${s.value}" data-url="${s.url}" data-label="${escapeHtml(s.name)}" onclick="selectThumb(this)">
            <img src="${s.url}" alt="" loading="lazy">
            <span class="name">${escapeHtml(s.name)}</span>
            <span class="cat">${escapeHtml(s.cat_label)}</span>
        </button>
    `).join('');
}

function renderGallery() {
    const wrap = document.getElementById('im-gallery');
    if (!imState.gallery || imState.gallery.length === 0) {
        wrap.innerHTML = '<div class="empty-thumbs"><i class="fas fa-images"></i><p style="margin-top:6px">Henüz galeride görsel yok.<br><small>Bir ürüne kendi görselini yüklediğinde burada birikir.</small></p></div>';
        return;
    }
    wrap.innerHTML = imState.gallery.map(g => `
        <button type="button" class="thumb" data-source="gallery" data-value="${escapeHtml(g.value)}" data-url="${g.url}" data-label="Galeri görseli" onclick="selectThumb(this)">
            <img src="${g.url}" alt="" loading="lazy">
        </button>
    `).join('');
}

function selectThumb(el) {
    document.querySelectorAll('#img-modal .thumb').forEach(t => t.classList.remove('is-selected'));
    el.classList.add('is-selected');
    imState.selection = {
        source: el.dataset.source,
        value:  el.dataset.value,
        url:    el.dataset.url,
        label:  el.dataset.label || (el.dataset.source === 'gallery' ? 'Galeri görseli' : el.dataset.value),
    };
    imState.pendingFile = null;
    document.getElementById('im-upload-preview').innerHTML = '';
    document.getElementById('im-confirm').disabled = false;
    document.getElementById('im-hint').textContent = 'Seçim: ' + imState.selection.label;
}

function handleUploadFile(file) {
    if (!file) return;
    if (!file.type.startsWith('image/')) { alert('Sadece görsel dosyası seçin.'); return; }
    if (file.size > 4 * 1024 * 1024) { alert('Görsel 4 MB\'dan büyük olamaz.'); return; }
    imState.pendingFile = file;
    imState.selection = { source: 'upload', value: '', url: '', label: file.name };
    document.querySelectorAll('#img-modal .thumb').forEach(t => t.classList.remove('is-selected'));
    const url = URL.createObjectURL(file);
    document.getElementById('im-upload-preview').innerHTML = `
        <div class="upload-preview">
            <img src="${url}" alt="">
            <div>
                <div class="nm">${escapeHtml(file.name)}</div>
                <div class="sz">${(file.size/1024).toFixed(0)} KB · ${file.type}</div>
            </div>
        </div>`;
    document.getElementById('im-confirm').disabled = false;
    document.getElementById('im-hint').textContent = 'Yüklenecek: ' + file.name;
}

// Drag & drop
(function() {
    const drop = document.getElementById('im-drop');
    if (!drop) return;
    ['dragenter','dragover'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.add('dragover'); }));
    ['dragleave','drop'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.remove('dragover'); }));
    drop.addEventListener('drop', ev => { const f = ev.dataTransfer.files[0]; if (f) handleUploadFile(f); });
})();

function confirmImage() {
    const p = imState.targetPicker;
    if (!p || !imState.selection) return;
    const sel = imState.selection;
    const sourceInput = p.querySelector('input[name=image_source]');
    const valueInput  = p.querySelector('input[name=image_value]');
    const fileInput   = p.querySelector('input[type=file]');
    const removeFlag  = p.querySelector('input[data-remove]');

    if (sel.source === 'upload') {
        // Dosyayı form dosya inputuna aktar
        const dt = new DataTransfer();
        dt.items.add(imState.pendingFile);
        fileInput.files = dt.files;
        sourceInput.value = 'upload';
        valueInput.value  = '';
        const objUrl = URL.createObjectURL(imState.pendingFile);
        setPreviewImage(p, objUrl);
        p.querySelector('[data-value-label]').textContent = 'Yeni yükleme: ' + imState.pendingFile.name;
    } else {
        // Stock veya gallery — file input boş
        fileInput.value = '';
        sourceInput.value = sel.source;
        valueInput.value  = sel.value;
        setPreviewImage(p, sel.url);
        p.querySelector('[data-value-label]').textContent =
            sel.source === 'stock' ? ('Stok: ' + sel.label)
                                   : 'Galeri görseli';
    }
    if (removeFlag) removeFlag.value = '0';
    closeImagePicker();
}

function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

// Modal kapatma — overlay'a tıklayınca
document.getElementById('img-modal').addEventListener('click', (e) => {
    if (e.target.id === 'img-modal') closeImagePicker();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeImagePicker();
});

// ── Varyasyon satırı ekle ────────────────────────────────────────────
function addVariantRow(btn) {
    const list = btn.closest('form').querySelector('[data-variants-list]');
    const idx = list.children.length;
    const row = document.createElement('div');
    row.style.cssText = 'display:grid;grid-template-columns:1fr 130px auto auto;gap:6px;margin-bottom:6px;align-items:center';
    row.innerHTML = `
        <input type="text" name="variants[${idx}][name]" placeholder="Boy / seçenek (örn. Büyük)">
        <input type="number" step="0.01" name="variants[${idx}][price_adjust]" placeholder="Fiyat farkı (₺)">
        <label style="font-size:.82rem;display:flex;align-items:center;gap:4px;white-space:nowrap"><input type="checkbox" name="variants[${idx}][is_default]" value="1"> Varsayılan</label>
        <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2;color:#991b1b;border:0;border-radius:8px;padding:6px 10px;cursor:pointer"><i class="fas fa-trash"></i></button>
    `;
    list.appendChild(row);
}

// ── Ürün stok hızlı toggle ──────────────────────────────────────────
async function toggleStock(productId, btn) {
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    btn.disabled = true; const orig = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const r = await fetch(`/panel/menu/product/${productId}/toggle-stock`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const d = await r.json();
        if (!r.ok || !d.ok) throw new Error('İşlem başarısız');
        location.reload();
    } catch (e) {
        btn.disabled = false; btn.innerHTML = orig;
        alert('Stok güncellenemedi.');
    }
}
</script>
@endpush
@endsection
