<?php

namespace App\Cekirdex\Services;

use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelPermissionOverride;
use App\Cekirdex\Models\ModelRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PermissionService
{
    public function can(Model $actor, string $permission, ?int $restaurantId = null): bool
    {
        if ($actor instanceof CekirdexUser && $actor->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissionsFor($actor, $restaurantId);

        return $permissions->contains($permission);
    }

    public function permissionsFor(Model $actor, ?int $restaurantId = null): Collection
    {
        $rolePermissions = ModelRole::query()
            ->with('role.permissions')
            ->where('model_type', $actor::class)
            ->where('model_id', $actor->getKey())
            ->where(function ($query) use ($restaurantId) {
                $query->whereNull('cekirdex_restaurant_id');
                if ($restaurantId !== null) {
                    $query->orWhere('cekirdex_restaurant_id', $restaurantId);
                }
            })
            ->get()
            ->flatMap(fn (ModelRole $modelRole) => $modelRole->role?->permissions ?? [])
            ->pluck('key')
            ->unique()
            ->values();

        $overrides = ModelPermissionOverride::query()
            ->with('permission')
            ->where('model_type', $actor::class)
            ->where('model_id', $actor->getKey())
            ->where(function ($query) use ($restaurantId) {
                $query->whereNull('cekirdex_restaurant_id');
                if ($restaurantId !== null) {
                    $query->orWhere('cekirdex_restaurant_id', $restaurantId);
                }
            })
            ->get();

        $allowed = $overrides
            ->where('effect', 'allow')
            ->pluck('permission.key')
            ->filter();

        $denied = $overrides
            ->where('effect', 'deny')
            ->pluck('permission.key')
            ->filter();

        return $rolePermissions
            ->merge($allowed)
            ->unique()
            ->reject(fn (string $key) => $denied->contains($key))
            ->values();
    }
}
