<?php

namespace App\Cekirdex\Middleware;

use App\Cekirdex\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthorizePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $actor = $request->attributes->get('api_actor');

        if (!$actor) {
            return response()->json(['message' => 'Kimlik doğrulama gerekli.'], 401);
        }

        $restaurantId = $actor->cekirdex_restaurant_id ?? null;

        if (!app(PermissionService::class)->can($actor, $permission, $restaurantId)) {
            return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
        }

        return $next($request);
    }
}
