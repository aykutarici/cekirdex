@extends('cekirdex.panel.layout')

@section('title', $table->name . ' — Yeni Sipariş')

@push('styles')
<style>
.wo-cols { display:grid; grid-template-columns: 2fr 1fr; gap:16px; }
@media (max-width: 980px) { .wo-cols { grid-template-columns: 1fr; } }

.cat-tabs { display:flex; gap:8px; overflow-x:auto; padding:6px 0 14px; scrollbar-width:none; }
.cat-tabs::-webkit-scrollbar { display:none; }
.cat-tabs button { background:#fff; border:1.5px solid var(--c-line); border-radius:99px; padding:8px 16px; font-weight:600; font-size:.85rem; color:var(--c-text-soft); white-space:nowrap; cursor:pointer; }
.cat-tabs button.is-on { background:linear-gradient(135deg,#ff8a4c,#ff6b35); color:#fff; border-color:transparent; }

.wo-search { position: relative; margin-bottom: 12px; }
.wo-search input { width:100%; padding:11px 14px 11px 38px; border:1.5px solid var(--c-line); border-radius:12px; background:#fff; font-size:.92rem; }
.wo-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--c-muted); }

.prod-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap:10px; }
.prod-card { background:#fff; border:1px solid var(--c-line); border-radius:12px; padding:10px; cursor:pointer; transition:all .15s; }
.prod-card:hover { border-color:#ff6b35; transform:translateY(-2px); box-shadow:0 10px 24px -14px rgba(0,0,0,.18); }
.prod-card .ph { width:100%; height:100px; background:linear-gradient(135deg,#fff7ed,#fed7aa); border-radius:8px; margin-bottom:8px; display:flex;align-items:center;justify-content:center;color:#d9531c;font-size:1.6rem; overflow:hidden; }
.prod-card .ph img { width:100%; height:100%; object-fit:cover; }
.prod-card .nm { font-weight:700; font-size:.9rem; color:var(--c-text); }
.prod-card .nm small { color:var(--c-muted); display:block; font-weight:400; font-size:.78rem; }
.prod-card .pr { font-weight:800; color:#d9531c; font-size:1rem; margin-top:6px; }
.prod-card.hidden { display:none; }

.cart-card { background:#fff; border:1px solid var(--c-line); border-radius:14px; padding:14px; position:sticky; top:14px; }
.cart-card h3 { font-size:1rem; margin-bottom:10px; }
.cart-line { display:flex; align-items:center; gap:8px; padding:8px 0; border-bottom:1px dashed var(--c-line); }
.cart-line:last-child { border-bottom:0; }
.cart-line .nm { flex:1; font-size:.88rem; }
.cart-line .nm small { color:var(--c-muted); display:block; font-size:.74rem; }
.cart-line .qx { display:inline-flex; gap:4px; align-items:center; }
.cart-line .qx button { width:26px; height:26px; border-radius:6px; border:1px solid var(--c-line); background:#fff; font-weight:700; cursor:pointer; }
.cart-line .qx span { min-width:22px; text-align:center; font-weight:700; font-size:.85rem; }
.cart-line .pr { font-weight:700; min-width:70px; text-align:right; font-size:.85rem; }
.cart-line .rm { color:#ef4444; cursor:pointer; padding:4px; background:transparent; border:0; }
.cart-totals { padding:10px 0 0; border-top:1px solid var(--c-line); margin-top:8px; font-size:.85rem; }
.cart-totals .row { display:flex; justify-content:space-between; padding:3px 0; }
.cart-totals .row.total { font-weight:800; font-size:1.05rem; padding-top:8px; border-top:1px dashed var(--c-line); margin-top:6px; color:#d9531c; }

.note-modal, .var-modal { position:fixed; inset:0; background:rgba(28,25,51,.65); z-index:200; display:none; align-items:center; justify-content:center; padding:20px; }
.note-modal.is-open, .var-modal.is-open { display:flex; }
.note-modal-box, .var-modal-box { background:#fff; border-radius:14px; padding:18px; max-width:420px; width:100%; }
.var-modal select { width:100%; padding:10px 12px; border:1.5px solid var(--c-line); border-radius:10px; font-size:.92rem; margin-top:8px; }
</style>
@endpush

@section('content')
<div class="pp-head">
    <div>
        <h1><i class="fas fa-pen-to-square"></i> {{ $table->name }} — Yeni Sipariş</h1>
        <div class="sub">Müşterinin söylediği ürünleri seçip adisyona ekle. Sipariş onaylı şekilde mutfağa düşer.</div>
    </div>
    <a class="btn btn-ghost" href="{{ route('cekirdex.panel.bills.show', $table->id) }}"><i class="fas fa-arrow-left"></i> Hesap</a>
</div>

<div class="wo-cols">
    <div>
        <div class="wo-search">
            <i class="fas fa-search"></i>
            <input id="wo-q" type="text" placeholder="Ürün ara..." oninput="filterProd()">
        </div>
        <div class="cat-tabs">
            <button class="is-on" data-cat="all" onclick="setCat(this)">Tümü</button>
            @foreach($categories as $c)
                @if(($products[$c->id] ?? collect())->isNotEmpty())
                    <button data-cat="{{ $c->id }}" onclick="setCat(this)">{{ $c->name }}</button>
                @endif
            @endforeach
        </div>

        <div class="prod-grid" id="prod-grid">
            @foreach($categories as $c)
                @foreach(($products[$c->id] ?? collect()) as $p)
                    @php
                        $price = (float) ($p->discount_price ?: $p->price);
                        $varJson = $p->variants->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'adj' => (float) $v->price_adjust])->values();
                        $ok = $p->isAvailable();
                    @endphp
                    <div class="prod-card {{ $ok ? '' : 'is-oos' }}" data-cat="{{ $c->id }}" data-name="{{ \Illuminate\Support\Str::lower($p->name) }}"
                         data-id="{{ $p->id }}" data-pname="{{ $p->name }}" data-base-price="{{ $price }}"
                         data-variants="{{ e($varJson->toJson(JSON_UNESCAPED_UNICODE)) }}"
                         @if($ok) onclick="onProdClick(this)" @endif
                         style="{{ $ok ? '' : 'opacity:.55;pointer-events:none;' }}">
                        <div class="ph">
                            @if($p->image_url)<img src="{{ $p->image_url }}" alt="">
                            @else<i class="fas fa-utensils"></i>@endif
                        </div>
                        <div class="nm">{{ $p->name }}<small>{{ $c->name }}</small></div>
                        <div class="pr">{{ number_format($price,2,',','.') }} ₺</div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <div>
        <div class="cart-card">
            <h3><i class="fas fa-cart-shopping"></i> Sepet (<span id="cart-count">0</span>)</h3>
            <div id="cart-list"><div style="color:var(--c-muted);text-align:center;padding:20px;font-size:.85rem">Henüz ürün yok</div></div>
            <div class="cart-totals" id="cart-totals" style="display:none">
                <div class="row"><span>Ara toplam</span><span id="t-sub">0,00 ₺</span></div>
                <div class="row" id="t-tax-row"><span>KDV (%{{ rtrim(rtrim(number_format((float)$table->restaurant->tax_rate, 2, '.', ''), '0'), '.') }})</span><span id="t-tax">0,00 ₺</span></div>
                <div class="row" id="t-svc-row"><span>Servis (%{{ rtrim(rtrim(number_format((float)$table->restaurant->service_charge_rate, 2, '.', ''), '0'), '.') }})</span><span id="t-svc">0,00 ₺</span></div>
                <div class="row total"><span>Toplam</span><span id="t-total">0,00 ₺</span></div>
            </div>
            <div style="margin-top:10px">
                <input type="text" id="wo-guest" placeholder="Müşteri adı (opsiyonel)" maxlength="120" style="width:100%;padding:9px 12px;border:1.5px solid var(--c-line);border-radius:10px;font-size:.88rem;margin-bottom:8px">
                <textarea id="wo-note" placeholder="Sipariş notu (opsiyonel)" rows="2" maxlength="1000" style="width:100%;padding:9px 12px;border:1.5px solid var(--c-line);border-radius:10px;font-size:.88rem;font-family:inherit;resize:vertical"></textarea>
            </div>
            <button class="btn btn-primary" id="wo-submit" disabled style="width:100%;margin-top:10px" onclick="submitOrder()">
                <i class="fas fa-check"></i> Sepeti Adisyona Ekle
            </button>
        </div>
    </div>
</div>

{{-- Varyant seçimi --}}
<div class="var-modal" id="var-modal">
    <div class="var-modal-box">
        <h3 style="margin-bottom:6px"><span id="vm-title">Seçenek</span></h3>
        <div style="font-size:.82rem;color:var(--c-text-soft);margin-bottom:4px">Boyut / seçenek:</div>
        <select id="vm-select"></select>
        <div style="font-size:.82rem;color:var(--c-muted);margin-top:10px">Birim: <strong id="vm-unit">—</strong></div>
        <div style="display:flex;gap:8px;margin-top:14px;justify-content:flex-end">
            <button class="btn btn-ghost" type="button" onclick="closeVar()">Vazgeç</button>
            <button class="btn btn-primary" type="button" onclick="confirmVar()">Sepete ekle</button>
        </div>
    </div>
</div>

{{-- Not modal --}}
<div class="note-modal" id="note-modal">
    <div class="note-modal-box">
        <h3 style="margin-bottom:10px"><span id="nm-title">Ürün notu</span></h3>
        <div style="font-size:.82rem;color:var(--c-text-soft);margin-bottom:10px">Bu kalem için özel istek (örn. baharatsız, ekstra peynir, ketçap-mayonez):</div>
        <textarea id="nm-text" rows="3" maxlength="500" style="width:100%;padding:10px;border:1.5px solid var(--c-line);border-radius:10px;font-family:inherit"></textarea>
        <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
            <button class="btn btn-ghost" onclick="closeNote()">Vazgeç</button>
            <button class="btn btn-primary" onclick="saveNote()">Kaydet</button>
        </div>
    </div>
</div>

<script>
const TAX_RATE = {{ (float) $table->restaurant->tax_rate }};
const SVC_RATE = {{ (float) $table->restaurant->service_charge_rate }};
const CSRF = '{{ csrf_token() }}';
const STORE_URL = @json(route('cekirdex.panel.bills.waiter-order.store', $table->id));
const REDIRECT_URL = @json(route('cekirdex.panel.bills.show', $table->id));

const cart = new Map();
let noteFor = null;
let __vm = { pid: 0, pname: '', base: 0, vars: [] };

function lineKey(pid, variantId) { return pid + '_' + (variantId || 0); }

function fmtTL(v) { return v.toFixed(2).replace('.', ',') + ' ₺'; }

function onProdClick(el) {
    const pid = +el.dataset.id;
    const pname = el.dataset.pname;
    const base = parseFloat(el.dataset.basePrice || '0');
    let vars = [];
    try { vars = JSON.parse(el.dataset.variants || '[]'); } catch (e) { vars = []; }
    if (!vars.length) {
        addToCartLine(pid, null, '', pname, base);
        return;
    }
    __vm = { pid, pname, base, vars };
    document.getElementById('vm-title').textContent = pname;
    const sel = document.getElementById('vm-select');
    sel.innerHTML = vars.map(v => {
        const adj = parseFloat(v.adj) || 0;
        const unit = base + adj;
        const tag = adj === 0 ? '' : ' (+' + fmtTL(adj) + ' → ' + fmtTL(unit) + ')';
        return '<option value="' + v.id + '">' + escapeHtml(String(v.name)) + tag + '</option>';
    }).join('');
    sel.onchange = updateVmUnit;
    updateVmUnit();
    document.getElementById('var-modal').classList.add('is-open');
}
function updateVmUnit() {
    const sel = document.getElementById('vm-select');
    const vid = parseInt(sel.value, 10);
    const vdef = __vm.vars.find(x => parseInt(x.id, 10) === vid);
    const adj = vdef ? (parseFloat(vdef.adj) || 0) : 0;
    document.getElementById('vm-unit').textContent = fmtTL(__vm.base + adj);
}
function closeVar() { document.getElementById('var-modal').classList.remove('is-open'); }
function confirmVar() {
    const sel = document.getElementById('vm-select');
    const vid = parseInt(sel.value, 10);
    const vdef = __vm.vars.find(x => parseInt(x.id, 10) === vid);
    const vname = vdef ? String(vdef.name) : '';
    const adj = vdef ? (parseFloat(vdef.adj) || 0) : 0;
    const unit = __vm.base + adj;
    addToCartLine(__vm.pid, vid, vname, __vm.pname + ' · ' + vname, unit);
    closeVar();
}

function setCat(btn) {
    document.querySelectorAll('.cat-tabs button').forEach(b => b.classList.remove('is-on'));
    btn.classList.add('is-on');
    filterProd();
}
function filterProd() {
    const cat = document.querySelector('.cat-tabs button.is-on').dataset.cat;
    const q = document.getElementById('wo-q').value.toLowerCase().trim();
    document.querySelectorAll('.prod-card').forEach(c => {
        const okCat = (cat === 'all') || (c.dataset.cat === cat);
        const okQ = !q || c.dataset.name.includes(q);
        c.classList.toggle('hidden', !(okCat && okQ));
    });
}

function addToCartLine(pid, variantId, variantLabel, displayName, price) {
    const k = lineKey(pid, variantId);
    const cur = cart.get(k) || {
        key: k, id: pid, variant_id: variantId || null, variant_label: variantLabel || '',
        name: displayName, price: price, qty: 0, note: ''
    };
    cur.qty += 1;
    cart.set(k, cur);
    renderCart();
}
function changeQ(key, delta) {
    const cur = cart.get(key); if (!cur) return;
    cur.qty += delta;
    if (cur.qty <= 0) cart.delete(key);
    renderCart();
}
function removeLine(key) { cart.delete(key); renderCart(); }

function openNote(key) {
    const cur = cart.get(key); if (!cur) return;
    noteFor = key;
    document.getElementById('nm-title').textContent = cur.name + ' — not';
    document.getElementById('nm-text').value = cur.note || '';
    document.getElementById('note-modal').classList.add('is-open');
    setTimeout(() => document.getElementById('nm-text').focus(), 80);
}
function closeNote() { document.getElementById('note-modal').classList.remove('is-open'); noteFor = null; }
function saveNote() {
    if (!noteFor) return;
    const cur = cart.get(noteFor); if (!cur) return;
    cur.note = document.getElementById('nm-text').value.trim();
    closeNote();
    renderCart();
}

function renderCart() {
    const list = document.getElementById('cart-list');
    const tot = document.getElementById('cart-totals');
    const submit = document.getElementById('wo-submit');
    const cnt = document.getElementById('cart-count');

    if (cart.size === 0) {
        list.innerHTML = '<div style="color:var(--c-muted);text-align:center;padding:20px;font-size:.85rem">Henüz ürün yok</div>';
        tot.style.display = 'none';
        submit.disabled = true;
        cnt.textContent = '0';
        return;
    }
    submit.disabled = false;
    cnt.textContent = [...cart.values()].reduce((a,b)=>a+b.qty,0);
    let sub = 0;
    list.innerHTML = [...cart.values()].map(it => {
        const lineSub = it.qty * it.price; sub += lineSub;
        const k = escapeAttr(it.key);
        return `<div class="cart-line">
            <div class="qx">
                <button type="button" onclick="changeQ('${k}',-1)">−</button>
                <span>${it.qty}</span>
                <button type="button" onclick="changeQ('${k}',+1)">+</button>
            </div>
            <div class="nm">${escapeHtml(it.name)}
                ${it.note ? `<small>📝 ${escapeHtml(it.note)}</small>` : ''}
                <small style="color:#3730a3;cursor:pointer" onclick="openNote('${k}')">${it.note ? '✏️ Notu düzenle' : '➕ Not ekle'}</small>
            </div>
            <div class="pr">${fmtTL(lineSub)}</div>
            <button type="button" class="rm" onclick="removeLine('${k}')"><i class="fas fa-trash"></i></button>
        </div>`;
    }).join('');
    const tax = sub * TAX_RATE / 100;
    const svc = sub * SVC_RATE / 100;
    document.getElementById('t-sub').textContent = fmtTL(sub);
    document.getElementById('t-tax').textContent = fmtTL(tax);
    document.getElementById('t-svc').textContent = fmtTL(svc);
    document.getElementById('t-total').textContent = fmtTL(sub + tax + svc);
    document.getElementById('t-tax-row').style.display = TAX_RATE > 0 ? '' : 'none';
    document.getElementById('t-svc-row').style.display = SVC_RATE > 0 ? '' : 'none';
    tot.style.display = '';
}

async function submitOrder() {
    if (cart.size === 0) return;
    const btn = document.getElementById('wo-submit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gönderiliyor...';
    try {
        const items = [...cart.values()].map(it => {
            const row = { product_id: it.id, quantity: it.qty, note: it.note || null };
            if (it.variant_id) row.variant_id = it.variant_id;
            return row;
        });
        const res = await fetch(STORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                items,
                guest_name: document.getElementById('wo-guest').value || null,
                note: document.getElementById('wo-note').value || null,
            }),
        });
        const data = await res.json();
        if (!data.ok) throw new Error(data.message || 'Sipariş gönderilemedi');
        window.location.href = data.redirect || REDIRECT_URL;
    } catch (e) {
        alert(e.message || 'Hata oluştu');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Sepeti Adisyona Ekle';
    }
}

function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function escapeAttr(s) { return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'"); }
</script>
@endsection
