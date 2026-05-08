<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * QR menüye gelen son kullanıcının kayıt/giriş işlemleri.
 * Modal/AJAX için JSON döner; geleneksel form post da destekler.
 */
class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $key = 'cekirdex-c-reg:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return response()->json(['ok' => false, 'message' => 'Çok fazla deneme. Bir dakika sonra tekrar deneyin.'], 429);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'name'     => 'required|string|min:2|max:120',
            'phone'    => 'required|string|max:24',
            'email'    => 'nullable|email|max:160',
            'password' => 'required|string|min:6|max:120',
        ]);

        $phone = CekirdexCustomerUser::normalizePhone($data['phone']);
        if (strlen($phone) < 10) {
            return response()->json(['ok' => false, 'message' => 'Geçerli bir telefon numarası girin.'], 422);
        }

        if (CekirdexCustomerUser::where('phone', $phone)->exists()) {
            return response()->json(['ok' => false, 'message' => 'Bu telefon numarası zaten kayıtlı. Giriş yapın.'], 422);
        }

        $user = CekirdexCustomerUser::create([
            'phone'         => $phone,
            'name'          => trim($data['name']),
            'email'         => $data['email'] ?? null,
            'password'      => Hash::make($data['password']),
            'ip_address'    => $request->ip(),
            'user_agent'    => substr((string) $request->userAgent(), 0, 500),
            'last_login_at' => now(),
        ]);
        Auth::guard('cekirdex_customer')->login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'ok'   => true,
            'user' => $this->me($user),
        ]);
    }

    public function login(Request $request)
    {
        $key = 'cekirdex-c-login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return response()->json(['ok' => false, 'message' => 'Çok fazla deneme. Bir dakika sonra tekrar deneyin.'], 429);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'phone'    => 'required|string|max:24',
            'password' => 'required|string|max:120',
        ]);
        $phone = CekirdexCustomerUser::normalizePhone($data['phone']);

        $user = CekirdexCustomerUser::where('phone', $phone)->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Telefon veya şifre hatalı.'], 401);
        }
        if (!$user->is_active) {
            return response()->json(['ok' => false, 'message' => 'Hesabınız devre dışı.'], 403);
        }

        Auth::guard('cekirdex_customer')->login($user, true);
        $user->update([
            'last_login_at' => now(),
            'ip_address'    => $request->ip(),
        ]);
        $request->session()->regenerate();

        return response()->json([
            'ok'   => true,
            'user' => $this->me($user),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('cekirdex_customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['ok' => true]);
    }

    /** Mevcut müşterinin profil özeti. UI durum kontrolü için. */
    public function whoami(Request $request)
    {
        $u = Auth::guard('cekirdex_customer')->user();
        if (!$u) return response()->json(['ok' => false]);
        return response()->json(['ok' => true, 'user' => $this->me($u)]);
    }

    private function me(CekirdexCustomerUser $u): array
    {
        return [
            'id'    => $u->id,
            'name'  => $u->name,
            'phone' => $u->phone,
            'email' => $u->email,
        ];
    }
}
