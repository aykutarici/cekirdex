<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|max:160|unique:cekirdex_users,email',
            'role'     => 'required|in:manager,waiter,kitchen',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:32',
            'is_active' => 'nullable|boolean',
        ]);

        $user = CekirdexUser::create([
            'cekirdex_restaurant_id' => $restaurantId,
            'name'                   => $data['name'],
            'email'                  => strtolower($data['email']),
            'role'                   => $data['role'],
            'phone'                  => $data['phone'] ?? null,
            'password'               => Hash::make($data['password']),
            'is_active'              => (bool) ($data['is_active'] ?? true),
        ]);

        return response()->json([
            'message' => 'Personel oluşturuldu.',
            'data'    => $user->only(['id', 'name', 'email', 'role', 'phone', 'is_active']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        $user  = CekirdexUser::query()
            ->where('cekirdex_restaurant_id', $actor->cekirdex_restaurant_id)
            ->findOrFail($id);

        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'email'     => ['required', 'email', 'max:160', Rule::unique('cekirdex_users', 'email')->ignore($user->id)],
            'role'      => 'required|in:owner,manager,waiter,kitchen',
            'phone'     => 'nullable|string|max:32',
            'is_active' => 'nullable|boolean',
            'password'  => 'nullable|string|min:6',
        ]);

        if ($actor->id === $user->id && array_key_exists('role', $data) && $data['role'] !== $user->role) {
            return response()->json(['message' => 'Kendi rolünüzü değiştiremezsiniz.'], 422);
        }

        $user->name      = $data['name'];
        $user->email     = strtolower($data['email']);
        $user->role      = $data['role'];
        $user->phone     = $data['phone'] ?? null;
        $user->is_active = (bool) ($data['is_active'] ?? false);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Personel güncellendi.',
            'data'    => $user->only(['id', 'name', 'email', 'role', 'phone', 'is_active']),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        $user  = CekirdexUser::query()
            ->where('cekirdex_restaurant_id', $actor->cekirdex_restaurant_id)
            ->findOrFail($id);

        if ($actor->id === $user->id) {
            return response()->json(['message' => 'Kendi hesabınızı silemezsiniz.'], 422);
        }

        if ($user->role === CekirdexUser::ROLE_OWNER) {
            $remainingOwners = CekirdexUser::query()
                ->where('cekirdex_restaurant_id', $actor->cekirdex_restaurant_id)
                ->where('role', CekirdexUser::ROLE_OWNER)
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingOwners < 1) {
                return response()->json(['message' => 'En az bir restoran sahibi (owner) bırakmalısınız.'], 422);
            }
        }

        $user->delete();

        return response()->json(null, 204);
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
