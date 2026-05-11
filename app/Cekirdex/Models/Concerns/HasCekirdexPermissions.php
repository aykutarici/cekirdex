<?php

namespace App\Cekirdex\Models\Concerns;

use App\Cekirdex\Services\PermissionService;

trait HasCekirdexPermissions
{
    public function hasPermissionTo(string $permission, ?int $restaurantId = null): bool
    {
        $restaurantId ??= $this->cekirdex_restaurant_id ?? null;

        return app(PermissionService::class)->can($this, $permission, $restaurantId);
    }
}
