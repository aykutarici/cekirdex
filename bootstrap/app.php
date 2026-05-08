<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\CekirdexDotComRequestRewrite::class);

        $middleware->alias([
            'cekirdex.auth' => \App\Cekirdex\Middleware\CekirdexAuthenticate::class,
            'cekirdex.guest' => \App\Cekirdex\Middleware\CekirdexRedirectIfAuthenticated::class,
        ]);

        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);

        $middleware->redirectGuestsTo(function ($request) {
            if (str_starts_with($request->path(), 'cekirdex/panel')) {
                return str_ends_with(strtolower($request->getHost()), 'cekirdex.com')
                    ? '/giris'
                    : '/cekirdex/giris';
            }

            return '/cekirdex/giris';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
