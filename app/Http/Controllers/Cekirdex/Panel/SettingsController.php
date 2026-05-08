<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    private function ensureOwnerOrManager(): void
    {
        $u = Auth::guard('cekirdex')->user();
        if (!in_array($u->role, [
            CekirdexUser::ROLE_OWNER,
            CekirdexUser::ROLE_MANAGER,
            CekirdexUser::ROLE_SUPER_ADMIN,
        ], true)) {
            abort(403);
        }
    }

    private function ensureOwner(): void
    {
        $u = Auth::guard('cekirdex')->user();
        if (!in_array($u->role, [
            CekirdexUser::ROLE_OWNER,
            CekirdexUser::ROLE_SUPER_ADMIN,
        ], true)) {
            abort(403);
        }
    }

    // ── RESTORAN AYARLARI ────────────────────────────────────────────
    public function general()
    {
        $this->ensureOwnerOrManager();
        $restaurant = CekirdexRestaurant::findOrFail($this->rid());
        return view('cekirdex.panel.settings.general', compact('restaurant'));
    }

    public function updateGeneral(Request $request)
    {
        $this->ensureOwnerOrManager();
        $restaurant = CekirdexRestaurant::findOrFail($this->rid());

        $data = $request->validate([
            'name'                => 'required|string|max:160',
            'description'         => 'nullable|string|max:2000',
            'address'             => 'nullable|string|max:255',
            'city'                => 'nullable|string|max:80',
            'phone'               => 'nullable|string|max:32',
            'email'               => 'nullable|email|max:160',
            'website'             => 'nullable|url|max:255',
            'currency'            => 'nullable|string|size:3',
            'tax_rate'            => 'nullable|numeric|min:0|max:50',
            'service_charge_rate' => 'nullable|numeric|min:0|max:50',
            'primary_color'       => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color'     => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'logo'                => 'nullable|image|max:2048',
            'cover_image'         => 'nullable|image|max:4096',
            'remove_logo'         => 'nullable|boolean',
            'remove_cover'        => 'nullable|boolean',
        ]);

        if (!empty($data['remove_logo']) && $restaurant->logo) {
            Storage::disk('public')->delete($restaurant->logo);
            $restaurant->logo = null;
        }
        if ($request->hasFile('logo')) {
            if ($restaurant->logo) Storage::disk('public')->delete($restaurant->logo);
            $restaurant->logo = $request->file('logo')->store('cekirdex/restaurants', 'public');
        }
        if (!empty($data['remove_cover']) && $restaurant->cover_image) {
            Storage::disk('public')->delete($restaurant->cover_image);
            $restaurant->cover_image = null;
        }
        if ($request->hasFile('cover_image')) {
            if ($restaurant->cover_image) Storage::disk('public')->delete($restaurant->cover_image);
            $restaurant->cover_image = $request->file('cover_image')->store('cekirdex/restaurants', 'public');
        }

        foreach (['name','description','address','city','phone','email','website','tax_rate','service_charge_rate','primary_color','secondary_color'] as $k) {
            if (array_key_exists($k, $data)) $restaurant->{$k} = $data[$k];
        }
        if (!empty($data['currency'])) $restaurant->currency = strtoupper($data['currency']);
        $restaurant->save();

        return back()->with('success', 'Restoran ayarları kaydedildi.');
    }

    // ── HİZMETLER (Paket sipariş, rezervasyon, çalışma saatleri) ─────────
    public function services()
    {
        $this->ensureOwnerOrManager();
        $restaurant = CekirdexRestaurant::findOrFail($this->rid());
        return view('cekirdex.panel.settings.services', compact('restaurant'));
    }

    public function updateServices(Request $request)
    {
        $this->ensureOwnerOrManager();
        $restaurant = CekirdexRestaurant::findOrFail($this->rid());

        $data = $request->validate([
            'slug'                     => ['nullable','string','max:80','regex:/^[a-z0-9\-]+$/', Rule::unique('cekirdex_restaurants','slug')->ignore($restaurant->id)],
            'accepts_takeaway'         => 'nullable|boolean',
            'accepts_delivery'         => 'nullable|boolean',
            'accepts_reservations'     => 'nullable|boolean',
            'delivery_radius_km'       => 'nullable|numeric|min:0|max:100',
            'delivery_min_amount'      => 'nullable|numeric|min:0|max:5000',
            'delivery_fee'             => 'nullable|numeric|min:0|max:1000',
            'reservation_slot_minutes' => 'nullable|integer|min:30|max:480',
            'reservation_slot_interval_minutes' => 'nullable|integer|min:15|max:120',
            'reservation_capacity_mode' => 'nullable|in:tables,total,counts',
            'reservation_total_capacity' => 'nullable|integer|min:1|max:500',
            'reservation_table_count'   => 'nullable|integer|min:0|max:200',
            'reservation_seat_count'    => 'nullable|integer|min:0|max:1000',
            'reservation_advance_days'  => 'nullable|integer|min:1|max:365',
            'latitude'                 => 'nullable|numeric|between:-90,90',
            'longitude'                => 'nullable|numeric|between:-180,180',
            'opening_hours'            => 'nullable|array',
            'opening_hours.*'          => 'nullable|array',
        ]);

        $restaurant->slug = !empty($data['slug']) ? $data['slug']
            : ($restaurant->slug ?: CekirdexRestaurant::generateSlug($restaurant->name));

        $restaurant->accepts_takeaway     = (bool) ($data['accepts_takeaway']     ?? false);
        $restaurant->accepts_delivery     = (bool) ($data['accepts_delivery']     ?? false);
        $restaurant->accepts_reservations = (bool) ($data['accepts_reservations'] ?? false);

        foreach (['delivery_radius_km','delivery_min_amount','delivery_fee','reservation_slot_minutes','reservation_slot_interval_minutes','latitude','longitude'] as $k) {
            if (array_key_exists($k, $data)) $restaurant->{$k} = $data[$k];
        }

        $restaurant->reservation_capacity_mode = $data['reservation_capacity_mode'] ?? ($restaurant->reservation_capacity_mode ?? 'tables');
        foreach (['reservation_total_capacity','reservation_table_count','reservation_seat_count','reservation_advance_days'] as $k) {
            if (!array_key_exists($k, $data)) continue;
            $restaurant->{$k} = ($data[$k] === '' || $data[$k] === null) ? null : (int) $data[$k];
        }

        // Çalışma saatleri: { mon: ['09:00','22:00'], tue: [...], ... }
        $hours = [];
        foreach (['mon','tue','wed','thu','fri','sat','sun'] as $d) {
            $row = $request->input('opening_hours.'.$d);
            if (is_array($row) && !empty($row[0]) && !empty($row[1])) {
                $open  = preg_match('/^\d{2}:\d{2}$/', $row[0]) ? $row[0] : null;
                $close = preg_match('/^\d{2}:\d{2}$/', $row[1]) ? $row[1] : null;
                if ($open && $close && $open < $close) {
                    $hours[$d] = [$open, $close];
                }
            }
        }
        $restaurant->opening_hours = empty($hours) ? null : $hours;

        $restaurant->save();
        return back()->with('success', 'Hizmet ayarları kaydedildi.');
    }

    // ── PERSONEL ─────────────────────────────────────────────────────
    public function staff()
    {
        $this->ensureOwner();
        $rid = $this->rid();
        $staff = CekirdexUser::where('cekirdex_restaurant_id', $rid)
            ->orderByRaw("FIELD(role, 'owner','manager','waiter','kitchen')")
            ->orderBy('name')
            ->get();
        return view('cekirdex.panel.settings.staff', compact('staff'));
    }

    public function storeStaff(Request $request)
    {
        $this->ensureOwner();
        $rid = $this->rid();
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|max:160|unique:cekirdex_users,email',
            'role'     => 'required|in:manager,waiter,kitchen',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:32',
        ]);
        CekirdexUser::create([
            'cekirdex_restaurant_id' => $rid,
            'name'      => $data['name'],
            'email'     => strtolower($data['email']),
            'role'      => $data['role'],
            'phone'     => $data['phone'] ?? null,
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);
        return back()->with('success', 'Personel eklendi.');
    }

    public function updateStaff(Request $request, int $id)
    {
        $this->ensureOwner();
        $u = CekirdexUser::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);

        // owner kendini düşürmesin diye safeguard: aynı kullanıcı kendi rolünü değiştiremez
        $current = Auth::guard('cekirdex')->user();
        if ($current->id === $u->id && $request->input('role') && $request->input('role') !== $u->role) {
            return back()->withErrors(['role' => 'Kendi rolünüzü değiştiremezsiniz.']);
        }

        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'email'     => ['required','email','max:160', Rule::unique('cekirdex_users','email')->ignore($u->id)],
            'role'      => 'required|in:owner,manager,waiter,kitchen',
            'phone'     => 'nullable|string|max:32',
            'is_active' => 'nullable|boolean',
            'password'  => 'nullable|string|min:6',
        ]);

        $u->name      = $data['name'];
        $u->email     = strtolower($data['email']);
        $u->role      = $data['role'];
        $u->phone     = $data['phone'] ?? null;
        $u->is_active = (bool) ($data['is_active'] ?? false);
        if (!empty($data['password'])) $u->password = Hash::make($data['password']);
        $u->save();

        return back()->with('success', 'Personel güncellendi.');
    }

    public function destroyStaff(int $id)
    {
        $this->ensureOwner();
        $u = CekirdexUser::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        if (Auth::guard('cekirdex')->id() === $u->id) {
            return back()->withErrors(['delete' => 'Kendi hesabınızı silemezsiniz.']);
        }
        if ($u->role === CekirdexUser::ROLE_OWNER) {
            $remainingOwners = CekirdexUser::where('cekirdex_restaurant_id', $this->rid())
                ->where('role', CekirdexUser::ROLE_OWNER)->where('id', '!=', $u->id)->count();
            if ($remainingOwners < 1) {
                return back()->withErrors(['delete' => 'En az bir restoran sahibi (owner) bırakmalısınız.']);
            }
        }
        $u->delete();
        return back()->with('success', 'Personel silindi.');
    }

    // ── PROFİL (kendi hesabı) ────────────────────────────────────────
    public function profile()
    {
        $user = Auth::guard('cekirdex')->user();
        return view('cekirdex.panel.settings.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('cekirdex')->user();
        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'email' => ['required','email','max:160', Rule::unique('cekirdex_users','email')->ignore($user->id)],
            'phone' => 'nullable|string|max:32',
        ]);
        $user->fill([
            'name'  => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
        ])->save();
        return back()->with('success', 'Profil bilgileri güncellendi.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('cekirdex')->user();
        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut şifre hatalı.']);
        }
        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Şifreniz güncellendi.');
    }
}
