<?php

namespace App\Cekirdex\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekirdexAuthenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::guard('cekirdex')->check()) {
            return redirect()->route('cekirdex.login')
                ->with('info', 'Çekirdex paneline erişmek için giriş yapın.');
        }

        $user = Auth::guard('cekirdex')->user();
        if (!$user->is_active) {
            Auth::guard('cekirdex')->logout();
            return redirect()->route('cekirdex.login')
                ->with('error', 'Hesabınız devre dışı bırakılmıştır.');
        }

        // Rol bazlı kısıtlama
        $route = $request->route()?->getName() ?? '';
        $role  = $user->role;

        // Mutfak personeli sadece KDS, profil ve dashboard feed'i görür (servis ekranı yok).
        if ($role === 'kitchen') {
            if (str_starts_with($route, 'cekirdex.panel.service.')) {
                return redirect()->route('cekirdex.panel.kds.index');
            }
            $allowed = ['cekirdex.panel.kds.', 'cekirdex.panel.profile', 'cekirdex.panel.dashboard.feed', 'cekirdex.logout'];
            $ok = false;
            foreach ($allowed as $p) {
                if (str_starts_with($route, $p) || $route === $p) { $ok = true; break; }
            }
            if (!$ok) {
                return redirect()->route('cekirdex.panel.kds.index');
            }
        }

        // Garson mutfak ekranına giremez — kendi servis ekranına yönlendirilir.
        if ($role === 'waiter' && str_starts_with($route, 'cekirdex.panel.kds.')) {
            return redirect()->route('cekirdex.panel.service.index')
                ->with('info', 'Mutfak ekranı yerine servis ekranını kullanın.');
        }

        // Garson: ayarlar / personel / menü / masalar / yorumlar sayfalarını göremez.
        // Ancak menu.product.toggle-stock garson da kullanabilsin (operasyonel ihtiyaç).
        if ($role === 'waiter') {
            $blocked = ['cekirdex.panel.settings.', 'cekirdex.panel.staff.', 'cekirdex.panel.menu.', 'cekirdex.panel.tables.', 'cekirdex.panel.reviews.'];
            $allowedExceptions = ['cekirdex.panel.menu.product.toggle-stock'];
            foreach ($blocked as $p) {
                if (str_starts_with($route, $p) && !in_array($route, $allowedExceptions, true)) {
                    return redirect()->route('cekirdex.panel.dashboard')->with('error', 'Bu sayfayı görüntüleme yetkiniz yok.');
                }
            }
        }

        // Manager: personel yönetimi sadece owner.
        if ($role === 'manager' && str_starts_with($route, 'cekirdex.panel.staff.')) {
            return redirect()->route('cekirdex.panel.dashboard')->with('error', 'Personel yönetimi sadece restoran sahibi tarafından yapılabilir.');
        }

        return $next($request);
    }
}
