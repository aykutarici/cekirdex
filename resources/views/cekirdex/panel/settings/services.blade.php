@extends('cekirdex.panel.layout')
@section('title', 'Hizmet Ayarları')

@section('content')
<div class="pp-head">
    <div>
        <h1>Hizmet Ayarları</h1>
        <div class="sub">Halka açık sayfa, paket sipariş, rezervasyon ve çalışma saatleri.</div>
    </div>
    <a href="{{ route('cekirdex.panel.settings.general') }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Genel Ayarlar</a>
</div>

<form action="{{ route('cekirdex.panel.settings.services.update') }}" method="POST">
    @csrf

    <div class="card">
        <h2><i class="fas fa-globe"></i> Halka Açık Sayfa</h2>
        <p class="muted" style="margin-bottom: 14px;">Müşterileriniz QR olmadan da menünüze ulaşabilir.</p>
        <div class="form-row">
            <div>
                <label>URL Slug</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="muted">{{ rtrim(url('/cekirdex/r/'), '/') }}/</span>
                    <input type="text" name="slug" value="{{ old('slug', $restaurant->slug) }}"
                           pattern="[a-z0-9\-]+" placeholder="elma-kafe"
                           style="flex: 1;">
                </div>
                <small class="muted">Sadece küçük harf, rakam ve tire (-) kullanın.</small>
            </div>
            <div>
                <label>Mevcut URL</label>
                @if($restaurant->slug)
                    <a href="{{ url('/cekirdex/r/'.$restaurant->slug) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px; background: #fff8f1; border-radius: 8px; word-break: break-all;">
                        <i class="fas fa-external-link"></i> {{ url('/cekirdex/r/'.$restaurant->slug) }}
                    </a>
                @else
                    <em class="muted">Slug henüz atanmamış. Kaydet'e basınca otomatik oluşturulur.</em>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <h2><i class="fas fa-bag-shopping"></i> Paket Sipariş</h2>
        <div class="form-row">
            <div>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="accepts_takeaway" value="1" {{ old('accepts_takeaway', $restaurant->accepts_takeaway) ? 'checked' : '' }}>
                    Gel-al siparişlerini kabul et
                </label>
            </div>
            <div>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="accepts_delivery" value="1" {{ old('accepts_delivery', $restaurant->accepts_delivery) ? 'checked' : '' }}>
                    Adrese teslim siparişlerini kabul et
                </label>
            </div>
        </div>

        <div class="form-row" style="margin-top: 14px;">
            <div>
                <label>Min. sipariş tutarı (₺)</label>
                <input type="number" step="0.01" min="0" max="5000" name="delivery_min_amount"
                       value="{{ old('delivery_min_amount', $restaurant->delivery_min_amount) }}">
            </div>
            <div>
                <label>Teslim ücreti (₺)</label>
                <input type="number" step="0.01" min="0" max="1000" name="delivery_fee"
                       value="{{ old('delivery_fee', $restaurant->delivery_fee) }}">
            </div>
        </div>
        <div class="form-row" style="margin-top: 12px;">
            <div>
                <label>Teslim yarıçapı (km)</label>
                <input type="number" step="0.1" min="0" max="100" name="delivery_radius_km"
                       value="{{ old('delivery_radius_km', $restaurant->delivery_radius_km) }}">
            </div>
            <div></div>
        </div>
    </div>

    <div class="card">
        <h2><i class="fas fa-calendar-check"></i> Rezervasyon</h2>
        <div class="form-row">
            <div>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="accepts_reservations" value="1" {{ old('accepts_reservations', $restaurant->accepts_reservations) ? 'checked' : '' }}>
                    Rezervasyon talepleri al
                </label>
            </div>
            <div>
                <label>Rezervasyon süresi (dk)</label>
                <input type="number" min="30" max="480" step="15" name="reservation_slot_minutes"
                       value="{{ old('reservation_slot_minutes', $restaurant->reservation_slot_minutes) }}">
                <small class="muted">Bir misafir bloğu kapladığı süre (çakışma kontrolünde kullanılır).</small>
            </div>
            <div>
                <label>Saat aralığı (dk)</label>
                <input type="number" min="15" max="120" step="15" name="reservation_slot_interval_minutes"
                       value="{{ old('reservation_slot_interval_minutes', $restaurant->reservation_slot_interval_minutes ?? 30) }}">
                <small class="muted">Müşteriye sunulan saat dilimleri (örn. 15 veya 30).</small>
            </div>
            <div>
                <label>En fazla kaç gün ileri</label>
                <input type="number" min="1" max="365" name="reservation_advance_days"
                       value="{{ old('reservation_advance_days', $restaurant->reservation_advance_days ?? 30) }}">
            </div>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--c-line-2); margin: 18px 0;">

        <p class="muted" style="margin-bottom: 12px;">Kapasite — müşteri saat seçerken kaç kişinin aynı pencerede yer alabileceği. İsterseniz masalarla hesaplatın, sabit toplam kişi veya masa–koltuk adedi girin.</p>
        @php $capMode = old('reservation_capacity_mode', $restaurant->reservation_capacity_mode ?? 'tables'); @endphp
        <div class="form-row">
            <div>
                <label>Kapasite hesaplama</label>
                <select name="reservation_capacity_mode" id="reservation_capacity_mode" style="width: 100%; padding: 10px; border: 1.5px solid var(--c-line-2); border-radius: 8px;">
                    <option value="tables" {{ $capMode === 'tables' ? 'selected' : '' }}>Masalardan topla (aktif + rezervasyona açık masaların kapasitesi)</option>
                    <option value="total" {{ $capMode === 'total' ? 'selected' : '' }}>Sabit toplam kişi kapasitesi</option>
                    <option value="counts" {{ $capMode === 'counts' ? 'selected' : '' }}>Masa adedi / koltuk adedi (veya her ikisi)</option>
                </select>
            </div>
        </div>
        <div class="form-row" id="cap-total-wrap" style="display: none;">
            <div>
                <label>Toplam kişi kapasitesi</label>
                <input type="number" min="1" max="500" name="reservation_total_capacity"
                       value="{{ old('reservation_total_capacity', $restaurant->reservation_total_capacity) }}"
                       placeholder="Örn. 80">
            </div>
        </div>
        <div class="form-row" id="cap-counts-wrap" style="display: none;">
            <div>
                <label>Masa adedi (opsiyonel)</label>
                <input type="number" min="0" max="200" name="reservation_table_count"
                       value="{{ old('reservation_table_count', $restaurant->reservation_table_count) }}"
                       placeholder="Örn. 20 — sadece koltuk girerseniz boş kalabilir">
            </div>
            <div>
                <label>Koltuk / sandalye (toplam kişi)</label>
                <input type="number" min="0" max="1000" name="reservation_seat_count"
                       value="{{ old('reservation_seat_count', $restaurant->reservation_seat_count) }}"
                       placeholder="Örn. 72 — doluysa bu kullanılır; yoksa masa×4 tahmini">
            </div>
            <div class="muted" style="grid-column: 1 / -1; font-size: .82rem;">Koltuk sayısı girerseniz o kullanılır. Girmezsenız masa sayısı × 4 tahmin edilir; ikisi de boşsa tanımlı masalarınızdan hesaplanır.</div>
        </div>
        <script>
        (function() {
            var sel = document.getElementById('reservation_capacity_mode');
            var wTotal = document.getElementById('cap-total-wrap');
            var wCounts = document.getElementById('cap-counts-wrap');
            function sync() {
                var v = sel && sel.value;
                if (wTotal) wTotal.style.display = (v === 'total') ? '' : 'none';
                if (wCounts) wCounts.style.display = (v === 'counts') ? '' : 'none';
            }
            if (sel) { sel.addEventListener('change', sync); sync(); }
        })();
        </script>
    </div>

    <div class="card">
        <h2><i class="fas fa-clock"></i> Çalışma Saatleri</h2>
        <p class="muted" style="margin-bottom: 14px;">Boş bırakılan günler kapalı kabul edilir. Tümü boşsa restoran her zaman açık sayılır.</p>
        @php
            $days = ['mon' => 'Pazartesi', 'tue' => 'Salı', 'wed' => 'Çarşamba', 'thu' => 'Perşembe', 'fri' => 'Cuma', 'sat' => 'Cumartesi', 'sun' => 'Pazar'];
            $hours = $restaurant->opening_hours ?: [];
        @endphp
        <table class="pp-table">
            <thead><tr><th>Gün</th><th>Açılış</th><th>Kapanış</th></tr></thead>
            <tbody>
                @foreach($days as $k => $lbl)
                    <tr>
                        <td><strong>{{ $lbl }}</strong></td>
                        <td><input type="time" name="opening_hours[{{ $k }}][0]" value="{{ $hours[$k][0] ?? '' }}" style="padding: 8px; border: 1.5px solid var(--c-line-2); border-radius: 8px;"></td>
                        <td><input type="time" name="opening_hours[{{ $k }}][1]" value="{{ $hours[$k][1] ?? '' }}" style="padding: 8px; border: 1.5px solid var(--c-line-2); border-radius: 8px;"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2><i class="fas fa-location-dot"></i> Konum (opsiyonel)</h2>
        <div class="form-row">
            <div>
                <label>Enlem (Latitude)</label>
                <input type="number" step="0.0000001" min="-90" max="90" name="latitude" value="{{ old('latitude', $restaurant->latitude) }}">
            </div>
            <div>
                <label>Boylam (Longitude)</label>
                <input type="number" step="0.0000001" min="-180" max="180" name="longitude" value="{{ old('longitude', $restaurant->longitude) }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn"><i class="fas fa-save"></i> Hizmet Ayarlarını Kaydet</button>
</form>
@endsection
