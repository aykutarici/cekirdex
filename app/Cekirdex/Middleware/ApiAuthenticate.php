<?php

namespace App\Cekirdex\Middleware;

use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Services\ApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (!$plainToken) {
            return response()->json(['message' => 'Kimlik doğrulama gerekli.'], 401);
        }

        $accessToken = app(ApiTokenService::class)->find($plainToken);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json(['message' => 'Geçersiz veya süresi dolmuş token.'], 401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        $actor = $accessToken->tokenable;

        $request->attributes->set('api_access_token', $accessToken);
        $request->attributes->set('api_actor', $actor);

        $this->setSentryContext($actor);

        return $next($request);
    }

    /**
     * Bir hata oluştuğunda Sentry'e gidecek event'e
     * kim olduğunu ve hangi restorana ait olduğunu ekler.
     */
    private function setSentryContext(CekirdexUser|CekirdexCustomerUser $actor): void
    {
        if (!app()->bound('sentry')) {
            return;
        }

        \Sentry\configureScope(function (Scope $scope) use ($actor): void {

            if ($actor instanceof CekirdexUser) {
                // ── Personel / restoran çalışanı ──────────────────────────
                $scope->setUser([
                    'id'       => $actor->id,
                    'username' => $actor->name,
                    'email'    => $actor->email,
                ]);

                $scope->setTag('actor.type', 'staff');
                $scope->setTag('actor.role', $actor->role ?? 'unknown');

                if ($actor->cekirdex_restaurant_id) {
                    $scope->setTag('restaurant.id', (string) $actor->cekirdex_restaurant_id);

                    // Restoran adı: önce yüklüyse ilişkiyi kullan, yoksa tek sorgu at
                    $restaurant = $actor->relationLoaded('restaurant')
                        ? $actor->restaurant
                        : $actor->restaurant()->select('id', 'name', 'slug')->first();

                    if ($restaurant) {
                        $scope->setTag('restaurant.name', $restaurant->name);
                        $scope->setTag('restaurant.slug', $restaurant->slug);
                    }
                }

            } elseif ($actor instanceof CekirdexCustomerUser) {
                // ── Misafir / son kullanıcı ────────────────────────────────
                $scope->setUser([
                    'id'       => $actor->id,
                    'username' => $actor->name,
                    'email'    => $actor->email ?? null,
                ]);

                $scope->setTag('actor.type', 'guest');
            }
        });
    }
}
