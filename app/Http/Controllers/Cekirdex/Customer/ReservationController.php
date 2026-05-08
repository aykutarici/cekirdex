<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ReservationController extends Controller
{
    /**
     * Bir restoranın belirli bir gün için müsait saat aralıklarını hesapla.
     * Kapasite moduna göre eşzamanlı kişi sayısı üzerinden boşluk hesaplanır.
     */
    public function availability(Request $request, string $slug)
    {
        $restaurant = CekirdexRestaurant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        if (!$restaurant->accepts_reservations) {
            return response()->json(['ok' => false, 'message' => 'Bu restoran rezervasyon almıyor.'], 422);
        }
        $date = $request->input('date');
        try {
            $day = Carbon::parse($date)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Geçersiz tarih.'], 422);
        }
        $maxDays = $this->advanceDays($restaurant);
        if ($day->lt(now()->startOfDay()) || $day->gt(now()->addDays($maxDays)->endOfDay())) {
            return response()->json([
                'ok'      => false,
                'message' => 'Tarih bugünden en fazla '.$maxDays.' gün ileri seçilebilir.',
            ], 422);
        }

        $partySize = max(1, min(120, (int) $request->input('party_size', 2)));

        $slots = $this->buildSlots($restaurant, $day, $partySize);

        return response()->json([
            'ok'             => true,
            'date'           => $day->toDateString(),
            'slot_min'       => $restaurant->reservation_slot_minutes,
            'slot_interval'  => max(15, (int) ($restaurant->reservation_slot_interval_minutes ?? 30)),
            'capacity_seats' => $restaurant->effectiveReservationSeatCapacity(),
            'max_party'      => $restaurant->maxReservationPartySize(),
            'advance_days'   => $maxDays,
            'slots'          => $slots,
        ]);
    }

    /** Rezervasyon kaydı oluştur. */
    public function store(Request $request, string $slug)
    {
        $key = 'cekirdex-resv:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return response()->json(['ok' => false, 'message' => 'Çok hızlı rezervasyon gönderiliyor.'], 429);
        }
        RateLimiter::hit($key, 60);

        $restaurant = CekirdexRestaurant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        if (!$restaurant->accepts_reservations) {
            return response()->json(['ok' => false, 'message' => 'Bu restoran rezervasyon almıyor.'], 422);
        }

        $maxParty = $restaurant->maxReservationPartySize();

        $data = $request->validate([
            'contact_name'  => 'required|string|min:2|max:120',
            'contact_phone' => 'required|string|max:24',
            'contact_email' => 'nullable|email|max:160',
            'reserved_for'  => 'required|date|after:now',
            'party_size'    => 'required|integer|min:1|max:'.$maxParty,
            'note'          => 'nullable|string|max:2000',
        ]);

        $reservedFor = Carbon::parse($data['reserved_for']);
        if ($reservedFor->gt(now()->addDays($this->advanceDays($restaurant)))) {
            return response()->json(['ok' => false, 'message' => 'Seçilen tarih izin verilen aralığın dışında.'], 422);
        }

        $dur = max(30, (int) $restaurant->reservation_slot_minutes);
        if (!$this->hasSeatCapacity($restaurant, $reservedFor, $dur, (int) $data['party_size'], null)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Bu saatte seçilen kişi sayısı için yer kalmadı. Başka saat veya daha az kişi deneyin.',
            ], 422);
        }

        $reservation = CekirdexReservation::create([
            'cekirdex_restaurant_id'    => $restaurant->id,
            'cekirdex_customer_user_id' => Auth::guard('cekirdex_customer')->id(),
            'public_code'               => CekirdexReservation::newPublicCode(),
            'contact_name'              => $data['contact_name'],
            'contact_phone'             => $data['contact_phone'],
            'contact_email'             => $data['contact_email'] ?? null,
            'reserved_for'              => $reservedFor,
            'duration_minutes'          => $dur,
            'party_size'                => $data['party_size'],
            'status'                    => 'pending',
            'note'                      => $data['note'] ?? null,
            'ip_address'                => $request->ip(),
        ]);

        if (!empty($reservation->contact_email)) {
            $this->sendReservationEmail($reservation, $restaurant, 'pending');
        }

        return response()->json([
            'ok'           => true,
            'message'      => 'Rezervasyon talebiniz alındı. Restoran onayladıktan sonra bilgilendirileceksiniz.',
            'public_code'  => $reservation->public_code,
            'tracking_url' => url('/cekirdex/rsv/'.$reservation->public_code),
        ]);
    }

    /** Müşteri rezervasyon takip sayfası. */
    public function show(string $publicCode)
    {
        $reservation = CekirdexReservation::where('public_code', $publicCode)
            ->with('restaurant')
            ->firstOrFail();
        return view('cekirdex.customer.reservation-track', compact('reservation'));
    }

    /** Müşteri rezervasyonunu iptal eder (sadece henüz başlamadıysa). */
    public function cancel(string $publicCode)
    {
        $reservation = CekirdexReservation::where('public_code', $publicCode)->firstOrFail();
        if (in_array($reservation->status, ['cancelled', 'completed', 'no_show', 'seated'])) {
            return back()->with('error', 'Bu rezervasyon artık iptal edilemez.');
        }
        if ($reservation->reserved_for->lt(now()->addMinutes(30))) {
            return back()->with('error', 'Rezervasyon başlamak üzere — restoranı arayın.');
        }
        $reservation->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
            'cancelled_by'  => 'customer',
        ]);
        return back()->with('success', 'Rezervasyonunuz iptal edildi.');
    }

    /**
     * Rezervasyon takip sayfasının QR kodu (PNG). Tarayıcıda gösterim veya ?download=1 ile indirme.
     */
    public function qrPng(Request $request, string $publicCode)
    {
        CekirdexReservation::where('public_code', $publicCode)->firstOrFail();
        $trackUrl = url('/cekirdex/rsv/'.$publicCode);
        $remote = 'https://api.qrserver.com/v1/create-qr-code/?'.http_build_query([
            'size'   => '512x512',
            'margin' => 2,
            'format' => 'png',
            'data'   => $trackUrl,
        ]);

        $res = Http::timeout(15)->withHeaders(['Accept' => 'image/png'])->get($remote);
        if (! $res->successful()) {
            Log::warning('cekirdex.reservation.qr_remote_failed', ['code' => $publicCode, 'status' => $res->status()]);
            abort(502, 'QR oluşturulamadı.');
        }

        $filename = 'cekirdex-rezervasyon-'.$publicCode.'.png';
        $disposition = $request->boolean('download')
            ? 'attachment; filename="'.$filename.'"'
            : 'inline; filename="'.$filename.'"';

        return response($res->body(), 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => $disposition,
            'Cache-Control'       => 'public, max-age=3600',
        ]);
    }

    private function advanceDays(CekirdexRestaurant $restaurant): int
    {
        $d = (int) ($restaurant->reservation_advance_days ?? 30);

        return max(1, min(365, $d));
    }

    /**
     * @return list<array{time: string, iso: string, available: bool}>
     */
    private function buildSlots(CekirdexRestaurant $restaurant, Carbon $day, int $partySize): array
    {
        $hours = $restaurant->opening_hours;
        $key = strtolower($day->shortEnglishDayOfWeek);
        $today = is_array($hours) ? ($hours[$key] ?? null) : null;

        [$open, $close] = ($today && count($today) === 2) ? $today : ['09:00', '22:00'];

        $slotMin = max(15, (int) $restaurant->reservation_slot_minutes);
        $stepMin = max(15, min(120, (int) ($restaurant->reservation_slot_interval_minutes ?? 30)));

        $start = Carbon::parse($day->toDateString().' '.$open);
        $end   = Carbon::parse($day->toDateString().' '.$close);

        $capacity = $restaurant->effectiveReservationSeatCapacity();
        $partySize = min($partySize, $capacity);

        $existing = CekirdexReservation::where('cekirdex_restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->whereDate('reserved_for', $day->toDateString())
            ->get();

        $slots = [];
        $cursor = $start->copy();
        $now = now();
        while ($cursor->lt($end)) {
            $slotStart = $cursor->copy();
            if ($slotStart->copy()->addMinutes($slotMin)->gt($end)) {
                break;
            }
            if ($slotStart->lte($now->copy()->addMinutes(15))) {
                $cursor->addMinutes($stepMin);
                continue;
            }
            $used = $this->overlappingPartySum($existing, $slotStart, $slotMin);
            $remaining = $capacity - $used;
            $slots[] = [
                'time'      => $slotStart->format('H:i'),
                'iso'       => $slotStart->toIso8601String(),
                'available' => $remaining >= $partySize,
            ];
            $cursor->addMinutes($stepMin);
        }

        return $slots;
    }

    /** @param \Illuminate\Support\Collection<int, CekirdexReservation> $existing */
    private function overlappingPartySum($existing, Carbon $slotStart, int $slotDurationMin): int
    {
        $slotEnd = $slotStart->copy()->addMinutes($slotDurationMin);

        return (int) $existing->filter(function ($r) use ($slotStart, $slotEnd) {
            $rStart = $r->reserved_for;
            $rDur = max(30, (int) ($r->duration_minutes ?? 90));
            $rEnd = $r->reserved_for->copy()->addMinutes($rDur);

            return $rStart->lt($slotEnd) && $rEnd->gt($slotStart);
        })->sum('party_size');
    }

    /**
     * Yeni rezervasyon (excludeId hariç) bu zaman penceresine sığıyor mu?
     */
    private function hasSeatCapacity(
        CekirdexRestaurant $restaurant,
        Carbon $start,
        int $durationMinutes,
        int $partySize,
        ?int $excludeReservationId
    ): bool {
        $capacity = $restaurant->effectiveReservationSeatCapacity();
        $day = $start->copy()->startOfDay();

        $q = CekirdexReservation::where('cekirdex_restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->whereDate('reserved_for', $day->toDateString());
        if ($excludeReservationId) {
            $q->where('id', '!=', $excludeReservationId);
        }
        $existing = $q->get();

        $slotEnd = $start->copy()->addMinutes($durationMinutes);
        $used = (int) $existing->filter(function ($r) use ($start, $slotEnd) {
            $rDur = max(30, (int) ($r->duration_minutes ?? 90));
            $rEnd = $r->reserved_for->copy()->addMinutes($rDur);

            return $r->reserved_for->lt($slotEnd) && $rEnd->gt($start);
        })->sum('party_size');

        return ($used + $partySize) <= $capacity;
    }

    private function sendReservationEmail(CekirdexReservation $r, CekirdexRestaurant $restaurant, string $kind): void
    {
        try {
            $when = $r->reserved_for->format('d.m.Y H:i');
            $subject = $restaurant->name.' — Rezervasyon';
            if ($kind === 'pending') {
                $subject .= ' talebiniz alındı';
                $body = "Sayın ".$r->contact_name.",\n\n";
                $body .= $when." için ".$r->party_size." kişilik rezervasyon talebiniz alındı.\n";
                $body .= "Restoran onayladıktan sonra ek e-posta alacaksınız.\n\n";
            } elseif ($kind === 'confirmed') {
                $subject .= ' onaylandı';
                $body = "Sayın ".$r->contact_name.",\n\n";
                $body .= $when." için ".$r->party_size." kişilik rezervasyonunuz ONAYLANDI.\n";
                $body .= "Sizi bekliyoruz!\n\n";
            } else {
                $subject .= ' güncellemesi';
                $body = "Rezervasyonunuz: ".$r->status_label."\n\n";
            }
            $body .= "Takip linki: ".url('/cekirdex/rsv/'.$r->public_code)."\n\n";
            $body .= $restaurant->name;

            Mail::raw($body, function ($m) use ($r, $subject) {
                $m->to($r->contact_email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Çekirdex reservation email failed: '.$e->getMessage());
        }
    }
}
