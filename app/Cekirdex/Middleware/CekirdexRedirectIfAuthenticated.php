<?php

namespace App\Cekirdex\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekirdexRedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::guard('cekirdex')->check()) {
            return redirect()->route('cekirdex.panel.dashboard');
        }
        return $next($request);
    }
}
