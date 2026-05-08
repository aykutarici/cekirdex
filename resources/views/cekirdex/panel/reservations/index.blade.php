@extends('cekirdex.panel.layout')
@section('title', 'Rezervasyonlar')

@section('content')
<div class="pp-head">
    <div>
        <h1><i class="fas fa-calendar-check"></i> Rezervasyonlar</h1>
        <div class="sub">Misafirlerinizin rezervasyonlarını yönetin.</div>
    </div>
    <form method="GET" action="{{ route('cekirdex.panel.reservations.index') }}" style="display: flex; gap: 8px; align-items: center;">
        <label style="font-size: .82rem; font-weight: 600;">Aralık:</label>
        <input type="date" name="from" value="{{ $from->toDateString() }}"
               style="padding: 8px; border: 1.5px solid var(--c-line-2); border-radius: 8px;">
        <span>—</span>
        <input type="date" name="to" value="{{ $to->toDateString() }}"
               style="padding: 8px; border: 1.5px solid var(--c-line-2); border-radius: 8px;">
        <button class="btn btn-sm">Filtre</button>
    </form>
</div>

@if($restaurant->accepts_reservations && $restaurant->slug)
    <div class="card" style="margin-bottom: 16px;">
        <h2 style="margin-bottom: 8px;"><i class="fas fa-link"></i> Müşteri rezervasyon linki</h2>
        <p class="muted" style="margin-bottom: 12px; font-size: .9rem;">Bu adresi WhatsApp veya SMS ile paylaşabilirsiniz. Açıldığında doğrudan rezervasyon formuna gider.</p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: stretch;">
            <input type="text" readonly id="rez-booking-url" value="{{ $restaurant->reservation_booking_url }}"
                   style="flex: 1; min-width: 220px; padding: 10px 12px; border: 1.5px solid var(--c-line-2); border-radius: 8px; font-size: .85rem; background: var(--c-bg);">
            <button type="button" class="btn btn-sm" id="rez-copy-url" title="Panoya kopyala"><i class="fas fa-copy"></i> Kopyala</button>
            <a href="{{ $restaurant->reservation_booking_url }}" target="_blank" class="btn btn-sm btn-ghost"><i class="fas fa-external-link"></i> Aç</a>
        </div>
    </div>
    @push('scripts')
    <script>
    (function() {
        var inp = document.getElementById('rez-booking-url');
        var btn = document.getElementById('rez-copy-url');
        if (!inp || !btn) return;
        btn.addEventListener('click', function() {
            var url = inp.value;
            function done() {
                var prev = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Kopyalandı';
                setTimeout(function() { btn.innerHTML = prev; }, 2000);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(function() {
                    inp.select();
                    try { document.execCommand('copy'); } catch (e) {}
                    done();
                });
                return;
            }
            inp.select();
            inp.setSelectionRange(0, 99999);
            try { document.execCommand('copy'); } catch (e) {}
            done();
        });
    })();
    </script>
    @endpush
@elseif($restaurant->accepts_reservations && !$restaurant->slug)
    <div class="card" style="margin-bottom: 16px; border-left: 4px solid #f59e0b;">
        <p class="muted" style="margin: 0;"><i class="fas fa-triangle-exclamation"></i> Müşteri linki için önce <a href="{{ route('cekirdex.panel.settings.services') }}">Hizmet Ayarları</a> üzerinden bir <strong>slug</strong> (kısa adres) tanımlayın.</p>
    </div>
@endif

<div class="kpi-grid">
    <div class="kpi"><div class="ic" style="background: #f59e0b;"><i class="fas fa-clock"></i></div><div class="label">Onay Bekleyen</div><div class="v">{{ $stats['pending'] }}</div></div>
    <div class="kpi"><div class="ic" style="background: #ff6b35;"><i class="fas fa-calendar-day"></i></div><div class="label">Bugün</div><div class="v">{{ $stats['today'] }}</div></div>
    <div class="kpi"><div class="ic" style="background: #10b981;"><i class="fas fa-calendar-week"></i></div><div class="label">Yaklaşan</div><div class="v">{{ $stats['upcoming'] }}</div></div>
    <div class="kpi"><div class="ic" style="background: #8b5cf6;"><i class="fas fa-list"></i></div><div class="label">Aralık Toplam</div><div class="v">{{ $byDate->flatten()->count() }}</div></div>
</div>

@if($byDate->isEmpty())
    <div class="card" style="text-align: center; padding: 40px; color: var(--c-muted);">
        <i class="fas fa-calendar-xmark" style="font-size: 2.4rem; margin-bottom: 12px;"></i>
        <div>Bu tarih aralığında rezervasyon yok.</div>
    </div>
@else
    @foreach($byDate as $date => $list)
        @php $d = \Illuminate\Support\Carbon::parse($date); @endphp
        <div class="card">
            <h2>
                <i class="fas fa-calendar-day"></i>
                {{ $d->isoFormat('dddd, DD MMMM YYYY') }}
                <span class="muted" style="font-size: .82rem; font-weight: 500;">{{ $list->count() }} rezervasyon</span>
            </h2>
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>Saat</th>
                        <th>Misafir</th>
                        <th>Kişi</th>
                        <th>Masa</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($list as $r)
                        <tr>
                            <td><strong>{{ $r->reserved_for->format('H:i') }}</strong></td>
                            <td>
                                <strong>{{ $r->contact_name }}</strong>
                                <div class="muted">{{ $r->contact_phone }}</div>
                                @if($r->note)<div class="muted" style="margin-top:4px;">"{{ \Illuminate\Support\Str::limit($r->note, 80) }}"</div>@endif
                            </td>
                            <td><strong>{{ $r->party_size }} kişi</strong></td>
                            <td>{{ $r->table?->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $r->status === 'pending' ? 'pending' : ($r->status === 'confirmed' ? 'ready' : ($r->status === 'cancelled' ? 'cancelled' : 'closed')) }}">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('cekirdex.panel.reservations.show', $r->id) }}" class="btn btn-sm btn-ghost">Yönet</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endif
@endsection
