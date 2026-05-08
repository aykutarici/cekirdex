<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index(Request $request)
    {
        $rid = $this->rid();

        // Tarih aralığı: bugün - 14 gün ileri
        $from = Carbon::parse($request->input('from', now()->toDateString()))->startOfDay();
        $to   = Carbon::parse($request->input('to', now()->addDays(14)->toDateString()))->endOfDay();

        $reservations = CekirdexReservation::where('cekirdex_restaurant_id', $rid)
            ->whereBetween('reserved_for', [$from, $to])
            ->with('table')
            ->orderBy('reserved_for')
            ->get();

        $byDate = $reservations->groupBy(fn ($r) => $r->reserved_for->toDateString());

        $stats = [
            'pending'   => CekirdexReservation::where('cekirdex_restaurant_id', $rid)
                ->where('status', 'pending')->count(),
            'today'     => CekirdexReservation::where('cekirdex_restaurant_id', $rid)
                ->whereIn('status', ['pending', 'confirmed', 'seated'])
                ->whereDate('reserved_for', now()->toDateString())->count(),
            'upcoming'  => CekirdexReservation::where('cekirdex_restaurant_id', $rid)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('reserved_for', '>', now())->count(),
        ];

        $tables = CekirdexTable::where('cekirdex_restaurant_id', $rid)
            ->where('is_active', true)->orderBy('name')->get();

        $restaurant = CekirdexRestaurant::findOrFail($rid);

        return view('cekirdex.panel.reservations.index', compact(
            'byDate', 'stats', 'tables', 'from', 'to', 'restaurant'
        ));
    }

    public function show(int $id)
    {
        $rid = $this->rid();
        $reservation = CekirdexReservation::where('cekirdex_restaurant_id', $rid)
            ->with(['table', 'customer'])
            ->findOrFail($id);
        $tables = CekirdexTable::where('cekirdex_restaurant_id', $rid)
            ->where('is_active', true)->orderBy('name')->get();
        return view('cekirdex.panel.reservations.show', compact('reservation', 'tables'));
    }

    public function confirm(Request $request, int $id)
    {
        $rid = $this->rid();
        $data = $request->validate([
            'cekirdex_table_id' => 'nullable|integer|exists:cekirdex_tables,id',
            'admin_note'        => 'nullable|string|max:500',
        ]);
        $reservation = CekirdexReservation::where('cekirdex_restaurant_id', $rid)->findOrFail($id);

        $reservation->update([
            'status'              => 'confirmed',
            'cekirdex_table_id'   => $data['cekirdex_table_id'] ?? null,
            'admin_note'          => $data['admin_note'] ?? null,
            'confirmed_by_user_id' => Auth::guard('cekirdex')->id(),
            'confirmed_at'        => now(),
        ]);
        $this->sendStatusEmail($reservation, 'confirmed');
        return back()->with('success', 'Rezervasyon onaylandı.');
    }

    public function reject(Request $request, int $id)
    {
        $rid = $this->rid();
        $data = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);
        $reservation = CekirdexReservation::where('cekirdex_restaurant_id', $rid)->findOrFail($id);
        $reservation->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
            'cancelled_by'  => 'restaurant',
            'admin_note'    => $data['admin_note'] ?? null,
        ]);
        $this->sendStatusEmail($reservation, 'cancelled');
        return back()->with('success', 'Rezervasyon reddedildi.');
    }

    public function setStatus(Request $request, int $id)
    {
        $rid = $this->rid();
        $data = $request->validate([
            'status' => 'required|in:seated,completed,no_show',
        ]);
        $reservation = CekirdexReservation::where('cekirdex_restaurant_id', $rid)->findOrFail($id);
        $reservation->update(['status' => $data['status']]);
        return back()->with('success', 'Durum güncellendi.');
    }

    private function sendStatusEmail(CekirdexReservation $r, string $kind): void
    {
        if (empty($r->contact_email)) return;
        try {
            $rest = $r->restaurant;
            $when = $r->reserved_for->format('d.m.Y H:i');
            $subject = $rest->name.' — Rezervasyon';
            if ($kind === 'confirmed') {
                $subject .= ' onaylandı';
                $body = "Sayın ".$r->contact_name.",\n\n".$when." için ".$r->party_size." kişilik rezervasyonunuz ONAYLANDI.\n\n";
                if ($r->admin_note) $body .= "Restoran notu: ".$r->admin_note."\n\n";
                $body .= "Sizi bekliyoruz!\n\n".$rest->name;
            } else {
                $subject .= ' iptal edildi';
                $body = "Sayın ".$r->contact_name.",\n\nMaalesef ".$when." rezervasyonunuz iptal edildi.\n";
                if ($r->admin_note) $body .= "Sebep: ".$r->admin_note."\n";
                $body .= "\n".$rest->name;
            }
            Mail::raw($body, function ($m) use ($r, $subject) {
                $m->to($r->contact_email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Çekirdex reservation status email failed: '.$e->getMessage());
        }
    }
}
