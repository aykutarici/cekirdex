<?php

namespace App\Http\Controllers\Cekirdex\Auth;

use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('cekirdex.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'cekirdex-login:'.strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Çok fazla başarısız deneme. Lütfen 1 dakika sonra tekrar deneyin.'],
            ]);
        }

        $user = CekirdexUser::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => ['Bu bilgilerle eşleşen bir hesap bulamadık.'],
            ]);
        }
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Hesabınız devre dışı bırakılmıştır. Destek ile iletişime geçin.'],
            ]);
        }

        Auth::guard('cekirdex')->login($user, true);
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();
        RateLimiter::clear($key);

        $target = match ($user->role) {
            'kitchen' => route('cekirdex.panel.kds.index'),
            'waiter'  => route('cekirdex.panel.service.index'),
            default   => route('cekirdex.panel.dashboard'),
        };
        return redirect()->intended($target);
    }

    public function logout(Request $request)
    {
        Auth::guard('cekirdex')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('cekirdex.landing')->with('success', 'Çıkış yapıldı.');
    }
}
