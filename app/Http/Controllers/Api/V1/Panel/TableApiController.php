<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $data = $request->validate([
            'name'                 => 'required|string|max:80',
            'code'                 => 'nullable|string|max:32',
            'capacity'             => 'nullable|integer|min:1|max:100',
            'accepts_reservations' => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'internal_note'        => 'nullable|string|max:500',
        ]);

        $table = CekirdexTable::create([
            'cekirdex_restaurant_id' => $restaurantId,
            'name'                   => $data['name'],
            'code'                   => $data['code'] ?? null,
            'qr_token'               => CekirdexTable::newQrToken(),
            'capacity'               => $data['capacity'] ?? 4,
            'accepts_reservations'   => (bool) ($data['accepts_reservations'] ?? true),
            'is_active'              => (bool) ($data['is_active'] ?? true),
            'internal_note'          => $data['internal_note'] ?? null,
        ]);

        return response()->json([
            'message' => 'Masa oluşturuldu.',
            'data'    => $table,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $table = $this->findTable($request, $id);

        $data = $request->validate([
            'name'                 => 'required|string|max:80',
            'code'                 => 'nullable|string|max:32',
            'capacity'             => 'nullable|integer|min:1|max:100',
            'accepts_reservations' => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'internal_note'        => 'nullable|string|max:500',
        ]);

        $table->update([
            'name'                 => $data['name'],
            'code'                 => $data['code'] ?? null,
            'capacity'             => $data['capacity'] ?? $table->capacity,
            'accepts_reservations' => (bool) ($data['accepts_reservations'] ?? false),
            'is_active'            => (bool) ($data['is_active'] ?? false),
            'internal_note'        => $data['internal_note'] ?? null,
        ]);

        return response()->json(['message' => 'Masa güncellendi.', 'data' => $table]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $table = $this->findTable($request, $id);

        $hasActiveOrders = CekirdexOrder::query()
            ->where('cekirdex_table_id', $table->id)
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->exists();

        if ($hasActiveOrders) {
            return response()->json(['message' => 'Bu masada aktif siparişler var. Önce hesabı kapatın.'], 422);
        }

        $table->delete();

        return response()->json(null, 204);
    }

    public function regenerateQr(Request $request, int $id): JsonResponse
    {
        $table          = $this->findTable($request, $id);
        $table->qr_token = CekirdexTable::newQrToken();
        $table->save();

        return response()->json([
            'message'   => 'QR kodu yenilendi.',
            'qr_token'  => $table->qr_token,
            'menu_url'  => $table->menu_url,
        ]);
    }

    private function findTable(Request $request, int $id): CekirdexTable
    {
        return CekirdexTable::query()
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
