<?php

namespace App\Http\Controllers\Api\V1;

use App\Cekirdex\Models\ApiAccessToken;
use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Services\ApiTokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function register(Request $request, ApiTokenService $tokens, string $qrToken): JsonResponse
    {
        CekirdexTable::query()
            ->where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'phone'    => ['required', 'string', 'max:32'],
            'email'    => ['nullable', 'email', 'max:160', 'unique:cekirdex_customer_users,email'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $phone = CekirdexCustomerUser::normalizePhone($data['phone']);

        if (CekirdexCustomerUser::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Bu telefon numarası ile kayıtlı bir hesap var.',
            ]);
        }

        $user = CekirdexCustomerUser::create([
            'name'          => $data['name'],
            'phone'         => $phone,
            'email'         => $data['email'] ?? null,
            'password'      => Hash::make($data['password'] ?? Str::random(32)),
            'ip_address'    => $request->ip(),
            'user_agent'    => substr((string) $request->userAgent(), 0, 500),
            'last_login_at' => now(),
            'is_active'     => true,
        ]);

        [$plainToken] = $tokens->issue($user, 'customer-qr', ['guest:*']);

        return response()->json([
            'token_type'   => 'Bearer',
            'access_token' => $plainToken,
            'actor'        => $this->actorPayload($user),
        ], 201);
    }

    public function login(Request $request, ApiTokenService $tokens, string $qrToken): JsonResponse
    {
        CekirdexTable::query()
            ->where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $phone = CekirdexCustomerUser::normalizePhone($data['phone']);

        $user = CekirdexCustomerUser::query()
            ->where('phone', $phone)
            ->where('is_active', true)
            ->firstOrFail();

        $user->forceFill(['last_login_at' => now()])->save();

        [$plainToken] = $tokens->issue($user, 'customer-qr', ['guest:*']);

        return response()->json([
            'token_type'   => 'Bearer',
            'access_token' => $plainToken,
            'actor'        => $this->actorPayload($user),
        ]);
    }

    public function logout(Request $request, string $qrToken): JsonResponse
    {
        /** @var ApiAccessToken|null $token */
        $token = $request->attributes->get('api_access_token');
        $token?->delete();

        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    public function me(Request $request, string $qrToken): JsonResponse
    {
        $actor = $request->attributes->get('api_actor');

        return response()->json(['actor' => $this->actorPayload($actor)]);
    }

    private function actorPayload(CekirdexCustomerUser $actor): array
    {
        return [
            'id'           => $actor->id,
            'account_type' => 'guest',
            'name'         => $actor->name,
            'email'        => $actor->email,
            'phone'        => $actor->phone,
        ];
    }
}
