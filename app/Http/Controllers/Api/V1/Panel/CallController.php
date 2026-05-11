<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexCall;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);
        $status       = $request->query('status', 'pending');

        $query = CekirdexCall::query()
            ->with('table')
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $calls = $query->limit(100)->get()->map(fn (CekirdexCall $call) => $this->callPayload($call));

        return response()->json(['data' => $calls]);
    }

    public function feed(Request $request): JsonResponse
    {
        $calls = CekirdexCall::query()
            ->with('table')
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->where('status', 'pending')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (CekirdexCall $call) => $this->callPayload($call));

        return response()->json([
            'ok'    => true,
            'ts'    => now()->toIso8601String(),
            'calls' => $calls,
        ]);
    }

    public function respond(Request $request, int $callId): JsonResponse
    {
        $actor = $this->actor($request);
        $call  = $this->findCall($request, $callId);

        if ($call->status !== 'pending') {
            return response()->json(['message' => 'Çağrı zaten yanıtlanmış veya kapatılmış.'], 422);
        }

        $call->update([
            'status'              => 'responded',
            'responded_by_user_id' => $actor->id,
            'responded_at'         => now(),
        ]);

        return response()->json(['message' => 'Çağrı yanıtlandı.']);
    }

    public function close(Request $request, int $callId): JsonResponse
    {
        $call = $this->findCall($request, $callId);

        if ($call->status === 'closed') {
            return response()->json(['message' => 'Çağrı zaten kapatılmış.'], 422);
        }

        $call->update(['status' => 'closed']);

        return response()->json(['message' => 'Çağrı kapatıldı.']);
    }

    private function callPayload(CekirdexCall $call): array
    {
        return [
            'id'           => $call->id,
            'table'        => $call->table?->name,
            'table_id'     => $call->cekirdex_table_id,
            'call_type'    => $call->call_type,
            'type_label'   => $call->type_label,
            'message'      => $call->message,
            'status'       => $call->status,
            'status_label' => $call->status_label,
            'created_at'   => $call->created_at?->toIso8601String(),
            'responded_at' => $call->responded_at?->toIso8601String(),
        ];
    }

    private function findCall(Request $request, int $callId): CekirdexCall
    {
        return CekirdexCall::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($callId);
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
