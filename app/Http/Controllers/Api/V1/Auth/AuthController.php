<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Cekirdex\Models\ApiAccessToken;
use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Role;
use App\Cekirdex\Services\ApiTokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function staffLogin(Request $request, ApiTokenService $tokens): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = CekirdexUser::query()
            ->where('email', $credentials['email'])
            ->first();

        if (!$user || !$user->is_active || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Giriş bilgileri hatalı veya hesap aktif değil.',
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        [$plainToken] = $tokens->issue($user, 'staff-api', ['*']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainToken,
            'actor' => $this->actorPayload($user),
        ]);
    }

    public function staffRegister(Request $request, ApiTokenService $tokens): JsonResponse
    {
        $data = $request->validate([
            'restaurant_name' => ['required', 'string', 'max:160'],
            'city' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:cekirdex_users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        [$restaurant, $user] = DB::transaction(function () use ($data) {
            $restaurant = CekirdexRestaurant::create([
                'slug' => CekirdexRestaurant::generateSlug($data['restaurant_name']),
                'name' => $data['restaurant_name'],
                'city' => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
                'is_active' => true,
            ]);

            $user = CekirdexUser::create([
                'cekirdex_restaurant_id' => $restaurant->id,
                'role' => CekirdexUser::ROLE_OWNER,
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'is_active' => true,
            ]);

            foreach ([['Ana Yemekler', 1], ['İçecekler', 2], ['Tatlılar', 3]] as [$name, $sort]) {
                CekirdexCategory::create([
                    'cekirdex_restaurant_id' => $restaurant->id,
                    'name' => $name,
                    'slug' => CekirdexCategory::generateSlug($restaurant->id, $name),
                    'sort_order' => $sort,
                    'is_active' => true,
                ]);
            }

            for ($i = 1; $i <= 3; $i++) {
                CekirdexTable::create([
                    'cekirdex_restaurant_id' => $restaurant->id,
                    'name' => 'Masa '.$i,
                    'code' => (string) $i,
                    'qr_token' => CekirdexTable::newQrToken(),
                    'capacity' => 4,
                    'is_active' => true,
                ]);
            }

            $ownerRole = Role::query()->where('key', 'restaurant.owner')->first();
            if ($ownerRole) {
                ModelRole::query()->create([
                    'model_type' => $user::class,
                    'model_id' => $user->id,
                    'role_id' => $ownerRole->id,
                    'cekirdex_restaurant_id' => $restaurant->id,
                ]);
            }

            return [$restaurant, $user];
        });

        [$plainToken] = $tokens->issue($user, 'staff-api', ['*']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainToken,
            'restaurant' => [
                'id' => $restaurant->id,
                'slug' => $restaurant->slug,
                'name' => $restaurant->name,
            ],
            'actor' => $this->actorPayload($user),
        ], 201);
    }

    public function guestLogin(Request $request, ApiTokenService $tokens): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = CekirdexCustomerUser::normalizePhone($credentials['login']);

        $user = CekirdexCustomerUser::query()
            ->where('email', $credentials['login'])
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !$user->is_active || !$user->password || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Giriş bilgileri hatalı veya hesap aktif değil.',
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        [$plainToken] = $tokens->issue($user, 'guest-api', ['guest:*']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainToken,
            'actor' => $this->actorPayload($user),
        ]);
    }

    public function guestRegister(Request $request, ApiTokenService $tokens): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160', 'unique:cekirdex_customer_users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $phone = CekirdexCustomerUser::normalizePhone($data['phone']);

        if (CekirdexCustomerUser::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Bu telefon numarası ile kayıtlı bir hesap var.',
            ]);
        }

        $user = CekirdexCustomerUser::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $phone,
            'password' => Hash::make($data['password']),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'last_login_at' => now(),
            'is_active' => true,
        ]);

        $guestRole = Role::query()->where('key', 'guest.member')->first();
        if ($guestRole) {
            ModelRole::query()->firstOrCreate([
                'model_type' => $user::class,
                'model_id' => $user->id,
                'role_id' => $guestRole->id,
                'cekirdex_restaurant_id' => null,
            ]);
        }

        [$plainToken] = $tokens->issue($user, 'guest-api', ['guest:*']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainToken,
            'actor' => $this->actorPayload($user),
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'actor' => $this->actorPayload($request->attributes->get('api_actor')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var ApiAccessToken|null $token */
        $token = $request->attributes->get('api_access_token');
        $token?->delete();

        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    private function actorPayload(CekirdexUser|CekirdexCustomerUser $actor): array
    {
        if ($actor instanceof CekirdexCustomerUser) {
            return [
                'id' => $actor->id,
                'account_type' => 'guest',
                'name' => $actor->name,
                'email' => $actor->email,
                'phone' => $actor->phone,
            ];
        }

        return [
            'id' => $actor->id,
            'account_type' => $actor->isSuperAdmin() ? 'admin' : 'staff',
            'restaurant_id' => $actor->cekirdex_restaurant_id,
            'role' => $actor->role,
            'name' => $actor->name,
            'email' => $actor->email,
            'phone' => $actor->phone,
        ];
    }
}
