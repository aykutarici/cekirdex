<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
        $reservation = $this->findReservation($request, $id);
        $reservation->load('table', 'customer');

        return response()->json(['data' => $this->reservationPayload($reservation)]);
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        $actor       = $this->actor($request);
        $reservation = $this->findReservation($request, $id);

        $data = $request->validate([
            'cekirdex_table_id' => 'nullable|integer|exists:cekirdex_tables,id',
            'admin_note'        => 'nullable|string|max:500',
        ]);

        if (!in_array($reservation->status, ['pending'], true)) {
            return response()->json(['message' => 'Bu rezervasyon artık onaylanamaz.'], 422);
        }

        $reservation->update([
            'status'               => 'confirmed',
            'cekirdex_table_id'    => $data['cekirdex_table_id'] ?? null,
            'admin_note'           => $data['admin_note'] ?? null,
            'confirmed_by_user_id' => $actor->id,
            'confirmed_at'         => now(),
        ]);

        return response()->json(['message' => 'Rezervasyon onaylandı.', 'status' => $reservation->status]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $reservation = $this->findReservation($request, $id);

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        if ($reservation->status === 'cancelled') {
            return response()->json(['message' => 'Rezervasyon zaten iptal edilmiş.'], 422);
        }

        $reservation->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => 'restaurant',
            'admin_note'   => $data['admin_note'] ?? null,
        ]);

        return response()->json(['message' => 'Rezervasyon iptal edildi.']);
    }

    public function noShow(Request $request, int $id): JsonResponse
    {
        $reservation = $this->findReservation($request, $id);

        if (!in_array($reservation->status, ['confirmed', 'pending'], true)) {
            return response()->json(['message' => 'Bu rezervasyonun durumu güncellenemez.'], 422);
        }

        $reservation->update(['status' => 'no_show']);

        return response()->json(['message' => 'Gelmedi olarak işaretlendi.']);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $reservation = $this->findReservation($request, $id);

        if (!in_array($reservation->status, ['confirmed', 'seated'], true)) {
            return response()->json(['message' => 'Bu rezervasyon tamamlandı olarak işaretlenemez.'], 422);
        }

        $reservation->update(['status' => 'completed']);

        return response()->json(['message' => 'Rezervasyon tamamlandı.']);
    }

    private function reservationPayload(CekirdexReservation $r): array
    {
        return [
            'id'            => $r->id,
            'public_code'   => $r->public_code,
            'contact_name'  => $r->contact_name,
            'contact_phone' => $r->contact_phone,
            'contact_email' => $r->contact_email,
            'reserved_for'  => $r->reserved_for?->toIso8601String(),
            'party_size'    => $r->party_size,
            'duration_minutes' => $r->duration_minutes,
            'status'        => $r->status,
            'status_label'  => $r->status_label,
            'note'          => $r->note,
            'admin_note'    => $r->admin_note,
            'table'         => $r->table?->name,
            'customer'      => $r->customer?->name,
            'confirmed_at'  => $r->confirmed_at?->toIso8601String(),
            'cancelled_at'  => $r->cancelled_at?->toIso8601String(),
            'created_at'    => $r->created_at?->toIso8601String(),
        ];
    }

    private function findReservation(Request $request, int $id): CekirdexReservation
    {
        return CekirdexReservation::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($id);
    }

    private function restaurantId(Request $request): int
    {
        return (int) $this->actor($request)->cekirdex_restaurant_id;
    }

    private function actor(Request $request): CekirdexUser
    {
        $actor = $request->attributes->get('api_actor');
        abort_unless($actor instanceof CekirdexUser, 403, 'Bu endpoint restoran personeli içindir.');
        return $actor;
    }
}
