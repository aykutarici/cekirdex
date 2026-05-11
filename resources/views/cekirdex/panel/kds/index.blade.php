@extends('cekirdex.panel.layout')

@section('title', 'Mutfak Ekranı (KDS)')

@push('styles')
<style>
/* Açık tema — Çekirdex panel ile uyumlu */
.kds-page { margin-top: -8px; }
.kds-head { align-items: flex-start; }
.kds-head h1 { display: flex; align-items: center; gap: 10px; }
.kds-head h1 .kds-ic {
    width: 42px; height: 42px; border-radius: 12px;
    background: linear-gradient(135deg, #ff8a4c, #ff6b35);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; box-shadow: 0 6px 14px -4px rgba(255, 107, 53, .4);
}
.kds-toolbar {
    display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
    margin-bottom: 20px;
}
.kds-toolbar .kds-counter {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: .85rem; font-weight: 600; color: var(--c-text-soft);
    padding: 8px 14px; background: #fff; border: 1px solid var(--c-line);
    border-radius: 99px; box-shadow: var(--c-shadow);
}
.kds-toolbar .kds-counter .pulse {
    width: 8px; height: 8px; border-radius: 50%; background: #10b981;
    animation: kds-pulse 2s infinite;
}
@keyframes kds-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
.kds-toolbar .kds-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-left: auto; }
.kds-toolbar .kds-actions button {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 10px; font-size: .85rem; font-weight: 600;
    cursor: pointer; border: 1px solid var(--c-line-2); background: #fff;
    color: var(--c-text); transition: background .15s, border-color .15s;
}
.kds-toolbar .kds-actions button:hover { background: var(--c-bg); border-color: var(--c-accent); color: var(--c-accent-d); }
.kds-toolbar .kds-actions button.sound.is-on {
    background: linear-gradient(135deg, #ff8a4c, #ff6b35);
    border-color: transparent; color: #fff !important;
    box-shadow: 0 6px 14px -4px rgba(255, 107, 53, .4);
}
.kds-toolbar .kds-actions button.sound.is-on:hover { filter: brightness(1.05); }

.kds-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 18px;
}
.kds-empty {
    text-align: center; padding: 64px 24px;
    background: #fff; border: 1px dashed var(--c-line-2); border-radius: 18px;
    color: var(--c-muted);
}
.kds-empty i {
    font-size: 2.75rem; color: var(--c-line-2); display: block; margin-bottom: 14px;
}
.kds-empty h2 { font-size: 1.1rem; color: var(--c-text-soft); font-weight: 700; margin-bottom: 6px; }
.kds-empty p { font-size: .92rem; max-width: 320px; margin: 0 auto; line-height: 1.5; }

.kds-card {
    background: var(--c-card); border: 1px solid var(--c-line); border-radius: 16px;
    padding: 16px 18px; display: flex; flex-direction: column; gap: 12px;
    box-shadow: var(--c-shadow);
    border-left: 4px solid var(--c-line-2);
    transition: transform .18s ease, box-shadow .18s ease;
}
.kds-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px -12px rgba(28, 25, 51, .18); }
.kds-card.is-new {
    border-left-color: #ff6b35;
    animation: kds-ring-new 2.5s infinite;
}
.kds-card.is-confirmed { border-left-color: #f59e0b; }
.kds-card.is-preparing { border-left-color: #0ea5e9; }
.kds-card.is-ready {
    border-left-color: #10b981;
    animation: kds-ring-ready 2s infinite;
}
.kds-card.is-old {
    background: #fffbeb;
    border-color: #fde68a;
    border-left-color: #d97706;
}
@keyframes kds-ring-new {
    0% { box-shadow: var(--c-shadow), 0 0 0 0 rgba(255, 107, 53, .35); }
    70% { box-shadow: var(--c-shadow), 0 0 0 10px rgba(255, 107, 53, 0); }
    100% { box-shadow: var(--c-shadow), 0 0 0 0 rgba(255, 107, 53, 0); }
}
@keyframes kds-ring-ready {
    0% { box-shadow: var(--c-shadow), 0 0 0 0 rgba(16, 185, 129, .35); }
    70% { box-shadow: var(--c-shadow), 0 0 0 12px rgba(16, 185, 129, 0); }
    100% { box-shadow: var(--c-shadow), 0 0 0 0 rgba(16, 185, 129, 0); }
}

.kds-card .head {
    display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;
    padding-bottom: 10px; border-bottom: 1px solid var(--c-line);
}
.kds-card .head .table { font-size: 1.35rem; font-weight: 800; color: var(--c-text); line-height: 1.1; }
.kds-card .head .no { font-size: .75rem; color: var(--c-muted); margin-top: 4px; font-weight: 500; }
.kds-card .head .ago {
    font-size: .78rem; font-weight: 700; padding: 5px 11px; border-radius: 99px;
    background: var(--c-bg); color: var(--c-text-soft); border: 1px solid var(--c-line);
    white-space: nowrap;
}
.kds-card .head .ago.warn { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
.kds-card .head .ago.late { background: #fee2e2; color: #991b1b; border-color: #fecaca; }

.kds-card .items { list-style: none; padding: 0; margin: 0; }
.kds-card .items li {
    display: flex; gap: 12px; padding: 8px 0;
    border-bottom: 1px dashed var(--c-line);
    font-size: .94rem;
}
.kds-card .items li:last-child { border-bottom: 0; }
.kds-card .items li .qty {
    width: 32px; height: 32px; border-radius: 10px;
    background: #fff3eb; color: var(--c-accent-d);
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: .88rem; flex-shrink: 0;
}
.kds-card .items li .nm { color: var(--c-text); font-weight: 600; }
.kds-card .items li .nt { display: block; color: #b45309; font-size: .78rem; font-style: italic; margin-top: 2px; }

.kds-card .note {
    background: #fffbeb; border: 1px solid #fde68a;
    color: #92400e; padding: 8px 12px; border-radius: 10px; font-size: .84rem;
    display: flex; gap: 8px; align-items: flex-start; line-height: 1.4;
}
.kds-card .note i { margin-top: 2px; flex-shrink: 0; }

.kds-card .actions { display: flex; flex-wrap: wrap; gap: 8px; }
.kds-card .actions .kds-btn-main {
    flex: 1; min-width: 120px; padding: 11px 12px; border: 0; border-radius: 10px;
    font-size: .86rem; font-weight: 700; cursor: pointer;
    background: linear-gradient(135deg, #ff8a4c, #ff6b35); color: #fff;
    box-shadow: 0 6px 16px -6px rgba(255, 107, 53, .5);
    transition: filter .15s, transform .12s ease, box-shadow .12s ease;
}
.kds-card .actions .kds-btn-main:hover:not(:disabled) { filter: brightness(1.06); }
.kds-card .actions .kds-btn-main:active:not(:disabled) {
    transform: scale(0.97);
    box-shadow: 0 2px 8px -4px rgba(255, 107, 53, .6);
}
.kds-card .actions .kds-btn-main:disabled {
    cursor: wait;
    filter: grayscale(0.15) brightness(1.05);
    opacity: 0.95;
}
.kds-card .actions .kds-btn-cancel {
    padding: 11px 14px; border-radius: 10px; border: 1px solid #fecaca;
    background: #fef2f2; color: #991b1b; font-weight: 700; cursor: pointer;
    font-size: .86rem; transition: background .15s, transform .12s ease;
}
.kds-card .actions .kds-btn-cancel:hover:not(:disabled) { background: #fee2e2; }
.kds-card .actions .kds-btn-cancel:active:not(:disabled) { transform: scale(0.96); }
.kds-card .actions .kds-btn-cancel:disabled { cursor: wait; opacity: 0.7; }

/* İstek sırasında kart geri bildirimi */
.kds-card.is-busy {
    pointer-events: none;
    outline: 3px solid rgba(255, 107, 53, .45);
    outline-offset: 2px;
    animation: kds-busy-pulse 1.1s ease-in-out infinite;
}
@keyframes kds-busy-pulse {
    0%, 100% { outline-color: rgba(255, 107, 53, .35); }
    50% { outline-color: rgba(255, 107, 53, .75); }
}

.kds-toast {
    position: fixed; bottom: 28px; left: 50%; z-index: 10000;
    transform: translateX(-50%) translateY(100px);
    padding: 12px 22px; border-radius: 14px; font-size: .92rem; font-weight: 700;
    background: #1c1933; color: #fff; box-shadow: 0 12px 40px -12px rgba(28, 25, 51, .45);
    opacity: 0; transition: transform .28s ease, opacity .28s ease;
    pointer-events: none; max-width: min(420px, calc(100vw - 32px)); text-align: center;
}
.kds-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
.kds-toast.err { background: #991b1b; }
.kds-toast.ok { background: #166534; }

.kds-card .status-pill {
    align-self: flex-start; padding: 4px 11px; border-radius: 99px;
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
}
.kds-card.is-new       .status-pill { background: #fff3eb; color: #c2410c; }
.kds-card.is-confirmed .status-pill { background: #fef3c7; color: #92400e; }
.kds-card.is-preparing .status-pill { background: #e0f2fe; color: #0369a1; }
.kds-card.is-ready     .status-pill { background: #dcfce7; color: #166534; }

.kds-wait-hint {
    font-size: .84rem; font-weight: 600; color: var(--c-text-soft);
    padding: 10px 12px; background: var(--c-bg); border-radius: 10px;
    border: 1px dashed var(--c-line-2); width: 100%;
    display: flex; align-items: center; gap: 8px; line-height: 1.35;
}
.kds-wait-hint i { color: var(--c-accent-d); }
</style>
@endpush

@section('content')
<div class="kds-page">
    <div class="pp-head kds-head">
        <div>
            <h1>
                <span class="kds-ic"><i class="fas fa-fire"></i></span>
                Mutfak ekranı
            </h1>
            <div class="sub">Garson onayladıktan sonra mutfakta hazırlanan siparişler · ~5 sn yenileme</div>
        </div>
    </div>

    <div class="kds-toolbar">
        <span class="kds-counter"><span class="pulse"></span> <span id="kds-active">0 sipariş</span></span>
        <div class="kds-actions">
            <button type="button" id="snd" class="sound is-on" onclick="toggleSound()" title="Bildirim sesi">
                <i class="fas fa-volume-high"></i> Ses açık
            </button>
            <button type="button" onclick="refreshNow(this)" title="Şimdi yenile"><i class="fas fa-rotate"></i> Yenile</button>
            @if(Auth::guard('cekirdex')->user()->role === 'kitchen')
                <a href="{{ route('cekirdex.panel.profile') }}" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Profil</a>
            @else
                <a href="{{ route('cekirdex.panel.dashboard') }}" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Panele dön</a>
            @endif
        </div>
    </div>

    <div id="kds-grid" class="kds-grid"></div>
    <div id="kds-empty" class="kds-empty" style="display:none">
        <i class="fas fa-utensils"></i>
        <h2>Mutfakta bekleyen sipariş yok</h2>
        <p>Garson yeni siparişi onayladığında (mutfağa gönderdiğinde) kartlar burada görünür. “Hazır” olanlar servis ekranında teslim alınır.</p>
    </div>
</div>

<div id="kds-toast" class="kds-toast" role="status" aria-live="polite"></div>

<audio id="ping" preload="auto">
    <source src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU5vYW5pAA==" type="audio/wav">
</audio>

<script>
const FEED_URL = @json(route('cekirdex.panel.kds.feed'));
const ADV_URL  = (id) => '/panel/mutfak/'+id+'/ilerle';
const SET_URL  = (id) => '/panel/mutfak/'+id+'/durum';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

let knownIds = new Set();
let soundOn  = true;
let firstLoad = true;

const counter = document.getElementById('kds-active');
const grid    = document.getElementById('kds-grid');
const empty   = document.getElementById('kds-empty');
let kdsToastTimer = null;

function showKdsToast(msg, isError) {
    const el = document.getElementById('kds-toast');
    if (!el) return;
    clearTimeout(kdsToastTimer);
    el.textContent = msg;
    el.classList.remove('ok', 'err', 'show');
    el.classList.add(isError ? 'err' : 'ok');
    requestAnimationFrame(() => el.classList.add('show'));
    kdsToastTimer = setTimeout(() => {
        el.classList.remove('show');
    }, isError ? 3200 : 2200);
}

function setCardBusy(orderId, busy) {
    const card = document.querySelector('.kds-card[data-id="' + orderId + '"]');
    if (!card) return;
    card.classList.toggle('is-busy', busy);
    card.querySelectorAll('.actions button').forEach((btn) => {
        btn.disabled = !!busy;
    });
    const main = card.querySelector('.kds-btn-main');
    if (main) {
        if (busy) {
            if (!main.dataset.savedHtml) main.dataset.savedHtml = main.innerHTML;
            main.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';
        } else if (main.dataset.savedHtml) {
            main.innerHTML = main.dataset.savedHtml;
            delete main.dataset.savedHtml;
        }
    }
}

function toggleSound() {
    soundOn = !soundOn;
    const b = document.getElementById('snd');
    b.classList.toggle('is-on', soundOn);
    b.innerHTML = soundOn
        ? '<i class="fas fa-volume-high"></i> Ses açık'
        : '<i class="fas fa-volume-xmark"></i> Sessiz';
    if (soundOn) playPing();
}

function playPing() {
    if (!soundOn) return;
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o = ctx.createOscillator();
        const g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.frequency.value = 880;
        g.gain.setValueAtTime(0.0001, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.4, ctx.currentTime + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
        o.start();
        o.stop(ctx.currentTime + 0.55);
        setTimeout(() => {
            const o2 = ctx.createOscillator(); const g2 = ctx.createGain();
            o2.connect(g2); g2.connect(ctx.destination);
            o2.frequency.value = 1320;
            g2.gain.setValueAtTime(0.0001, ctx.currentTime);
            g2.gain.exponentialRampToValueAtTime(0.4, ctx.currentTime + 0.02);
            g2.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
            o2.start();
            o2.stop(ctx.currentTime + 0.55);
        }, 220);
    } catch(e) {}
}

function ageBadge(min) {
    if (min >= 15) return ['late', min + ' dk'];
    if (min >= 8)  return ['warn', min + ' dk'];
    return ['', min + ' dk'];
}

function actionsFor(o) {
    if (o.status === 'confirmed') return [['preparing','Hazırlamaya başla']];
    if (o.status === 'preparing') return [['ready','Hazır']];
    return [];
}

function render(data) {
    const arr = data.orders || [];
    counter.textContent = arr.length + ' sipariş';

    const currentIds = new Set(arr.map(x => x.id));
    if (!firstLoad) {
        for (const id of currentIds) if (!knownIds.has(id)) playPing();
    }
    firstLoad = false;
    knownIds = currentIds;

    if (arr.length === 0) {
        grid.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    empty.style.display = 'none';

    grid.innerHTML = arr.map(o => {
        const [cls, lbl] = ageBadge(o.minutes_ago);
        const isOld = o.minutes_ago >= 15;
        const itemsHtml = o.items.map(it => `
            <li>
                <span class="qty">${it.quantity}x</span>
                <span>
                    <span class="nm">${escape(it.name)}</span>
                    ${it.note ? '<span class="nt"><i class="fas fa-info-circle"></i> '+escape(it.note)+'</span>' : ''}
                </span>
            </li>
        `).join('');

        const primaryBtns = actionsFor(o).map(([s, label]) => `
            <button type="button" class="kds-btn-main" onclick="advance(${o.id})">${escape(label)} →</button>
        `).join('');
        const readyHint = o.status === 'ready'
            ? '<div class="kds-wait-hint"><i class="fas fa-hand-holding"></i> Hazır — garson servis ekranından teslim alacak</div>'
            : '';
        const cancelBtn = `<button type="button" class="kds-btn-cancel" onclick="cancelOrder(${o.id})" title="İptal"><i class="fas fa-times"></i></button>`;
        const actions = `${readyHint}${primaryBtns}${cancelBtn}`;

        return `
            <div class="kds-card is-${o.status} ${isOld ? 'is-old' : ''}" data-id="${o.id}">
                <div class="head">
                    <div>
                        <div class="table">${escape(o.table)}</div>
                        <div class="no">${escape(o.order_number)}</div>
                    </div>
                    <span class="ago ${cls}">${escape(lbl)}</span>
                </div>
                <span class="status-pill">${escape(o.status_label)}</span>
                <ul class="items">${itemsHtml}</ul>
                ${o.note ? '<div class="note"><i class="fas fa-pen"></i> '+escape(o.note)+'</div>' : ''}
                <div class="actions">${actions}</div>
            </div>
        `;
    }).join('');
}

function escape(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

async function fetchFeed() {
    try {
        const r = await fetch(FEED_URL, { headers: { 'Accept': 'application/json' }});
        const d = await r.json();
        render(d);
    } catch (e) { console.error(e); }
}

async function advance(id) {
    setCardBusy(id, true);
    try {
        const r = await fetch(ADV_URL(id), { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }});
        const d = await r.json().catch(() => ({}));
        if (!r.ok) {
            showKdsToast(d.message || 'İşlem yapılamadı', true);
            return;
        }
        showKdsToast('Tamam — sonraki adıma geçildi');
    } catch (e) {
        console.error(e);
        showKdsToast('Bağlantı hatası', true);
    } finally {
        await fetchFeed();
    }
}

async function cancelOrder(id) {
    if (!confirm('Bu siparişi iptal etmek istediğinizden emin misiniz?')) return;
    setCardBusy(id, true);
    try {
        const fd = new FormData();
        fd.append('status', 'cancelled');
        const r = await fetch(SET_URL(id), { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }});
        if (!r.ok) throw new Error('HTTP ' + r.status);
        showKdsToast('Sipariş iptal edildi');
    } catch (e) {
        console.error(e);
        showKdsToast('İptal edilemedi', true);
    } finally {
        await fetchFeed();
    }
}

function refreshNow(btn) {
    if (btn && btn.querySelector) {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.add('fa-spin');
            setTimeout(() => icon.classList.remove('fa-spin'), 600);
        }
    }
    fetchFeed();
}

fetchFeed();
setInterval(fetchFeed, 5000);

document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') fetchFeed(); });
</script>
@endsection
