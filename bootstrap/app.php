<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'cekirdex.auth' => \App\Cekirdex\Middleware\CekirdexAuthenticate::class,
            'cekirdex.guest' => \App\Cekirdex\Middleware\CekirdexRedirectIfAuthenticated::class,
            'api.actor' => \App\Cekirdex\Middleware\ApiAuthenticate::class,
            'api.permission' => \App\Cekirdex\Middleware\ApiAuthorizePermission::class,
        ]);

        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);

        $middleware->redirectGuestsTo(function ($request) {
            if (str_starts_with($request->path(), 'panel')) {
                return '/giris';
            }

            return '/giris';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Sentry — sadece beklenmedik hatalar (ignore_exceptions config'de tanımlı)
        \Sentry\Laravel\Integration::handles($exceptions);
    })->create();
