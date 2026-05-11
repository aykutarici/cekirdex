<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(60));

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            if (method_exists($user, 'hasPermissionTo')) {
                return $user->hasPermissionTo($ability);
            }

            return null;
        });

        // MySQL/MariaDB uzak bağlantıda TCP takılırsa PHP varsayılanı uzun süre bekleyebilir.
        // PDO_MYSQL için PDO::ATTR_TIMEOUT desteklenmediğinden soket zaman aşımı kullanılır.
        $timeout = env('DB_CONNECT_TIMEOUT');
        if ($timeout !== null && $timeout !== '') {
            $seconds = max(1, min(120, (int) $timeout));
            ini_set('default_socket_timeout', (string) $seconds);
        }
    }
}
