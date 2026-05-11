<?php

namespace App\Cekirdex\Services;

use App\Cekirdex\Models\ApiAccessToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiTokenService
{
    public function issue(Model $actor, string $name, array $abilities = ['*']): array
    {
        $plainToken = Str::random(80);

        $token = ApiAccessToken::create([
            'tokenable_type' => $actor::class,
            'tokenable_id' => $actor->getKey(),
            'name' => $name,
            'token_hash' => hash('sha256', $plainToken),
            'abilities' => $abilities,
            'expires_at' => now()->addDays(30),
        ]);

        return [$plainToken, $token];
    }

    public function find(string $plainToken): ?ApiAccessToken
    {
        return ApiAccessToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}
