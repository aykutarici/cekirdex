@extends('cekirdex.panel.layout')
@section('title', 'Rezervasyon')

@section('content')
<div class="pp-head">
    <div>
        <h1>Rezervasyon — {{ $reservation->contact_name }}</h1>
        <div class="sub">
            <span class="badge badge-{{ $reservation->status === 'pending' ? 'pending' : ($reservation->status === 'confirmed' ? 'ready' : ($reservation->status === 'cancelled' ? 'cancelled' : 'closed')) }}">
                {{ $reservation->status_label }}
            </span>
            <span class="muted" style="margin-left: 8px;">{{ $reservation->reserved_for->isoFormat('dddd, DD MMMM YYYY HH:mm') }}</span>
        </div>
    </div>
    <a href="{{ route('cekirdex.panel.reservations.index') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Listeye dön</a>
</div>

<div class="card-grid">
    <div class="card">
        <h2><i class="fas fa-circle-info"></i> Detaylar</h2>
        <table class="pp-table">
            <tr><th>Tarih</th><td>{{ $reservation->reserved_for->format('d.m.Y H:i') }}</td></tr>
            <tr><th>Süre</th><td>{{ $reservation->duration_minutes }} dk</td></tr>
            <tr><th>Kişi</th><td>{{ $reservation->party_size }}</td></tr>
            <tr><th>Masa</th><td>
                {{ $reservation->table?->name ?? '—' }}
                @if($reservation->table?->internal_note)
                    <div class="muted" style="margin-top: 8px; white-space: pre-wrap;"><strong>Masa iç notu:</strong> {{ $reservation->table->internal_note }}</div>
                @endif
            </td></tr>
            <tr><th>Ad</th><td>{{ $reservation->contact_name }}</td></tr>
            <tr><th>Telefon</th><td><a href="tel:{{ $reservation->contact_phone }}">{{ $reservation->contact_phone }}</a></td></tr>
            @if($reservation->contact_email)<tr><th>E-posta</th><td>{{ $reservation->contact_email }}</td></tr>@endif
            @if($reservation->note)<tr><th>Müşteri Notu</th><td>{{ $reservation->note }}</td></tr>@endif
            @if($reservation->admin_note)<tr><th>İç Not</th><td>{{ $reservation->admin_note }}</td></tr>@endif
            <tr><th>Takip kodu</th><td><code>{{ $reservation->public_code }}</code></td></tr>
        </table>
    </div>

    <div class="card">
        <h2><i class="fas fa-bolt"></i> İşlemler</h2>

        @if($reservation->status === 'pending')
            <form action="{{ route('cekirdex.panel.reservations.confirm', $reservation->id) }}" method="POST">
                @csrf
                <div class="form-block">
                    <label>Masa ata (opsiyonel)</label>
                    <select name="cekirdex_table_id">
                        <option value="">— Atama yapma —</option>
                        @foreach($tables as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-block">
                    <label>İç not (opsiyonel)</label>
                    <textarea name="admin_note" rows="2" maxlength="500" placeholder="Pencere kenarına yerleştir, doğum günü pastası..."></textarea>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn"><i class="fas fa-check"></i> Onayla</button>
                </div>
            </form>

            <form action="{{ route('cekirdex.panel.reservations.reject', $reservation->id) }}" method="POST" style="margin-top: 12px;"
                  onsubmit="return confirm('Rezervasyonu reddetmek istediğinize emin misiniz?')">
                @csrf
                <div class="form-block">
                    <label>Red sebebi (opsiyonel)</label>
                    <input type="text" name="admin_note" maxlength="500" placeholder="Müsait masa kalmadı...">
                </div>
                <button type="submit" class="btn btn-danger"><i class="fas fa-xmark"></i> Reddet</button>
            </form>
        @elseif($reservation->status === 'confirmed')
            <form action="{{ route('cekirdex.panel.reservations.set-status', $reservation->id) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="seated">
                <button class="btn"><i class="fas fa-chair"></i> Misafir Geldi</button>
            </form>
            <form action="{{ route('cekirdex.panel.reservations.set-status', $reservation->id) }}" method="POST" style="margin-top: 8px;">
                @csrf
                <input type="hidden" name="status" value="no_show">
                <button class="btn btn-danger"><i class="fas fa-user-slash"></i> Gelmedi (No-show)</button>
            </form>
        @elseif($reservation->status === 'seated')
            <form action="{{ route('cekirdex.panel.reservations.set-status', $reservation->id) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="completed">
                <button class="btn"><i class="fas fa-flag-checkered"></i> Tamamlandı</button>
            </form>
        @else
            <p class="muted">Bu rezervasyon için ek işlem yok.</p>
        @endif
    </div>
</div>
@endsection
