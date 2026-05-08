<?php

namespace App\Cekirdex\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CekirdexUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'cekirdex_users';

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_OWNER       = 'owner';
    public const ROLE_MANAGER     = 'manager';
    public const ROLE_WAITER      = 'waiter';
    public const ROLE_KITCHEN     = 'kitchen';

    protected $fillable = [
        'cekirdex_restaurant_id',
        'role',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'locale',
        'is_active',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isOwner(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_SUPER_ADMIN], true);
    }

    public function canManagePanel(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
        ], true);
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }
}
