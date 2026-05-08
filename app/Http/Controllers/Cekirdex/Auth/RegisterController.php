<?php

namespace App\Http\Controllers\Cekirdex\Auth;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('cekirdex.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'restaurant_name' => 'required|string|max:160',
            'city'            => 'nullable|string|max:80',
            'phone'           => 'nullable|string|max:32',
            'name'            => 'required|string|max:120',
            'email'           => 'required|email|max:160|unique:cekirdex_users,email',
            'password'        => 'required|string|min:6|confirmed',
            'terms'           => 'accepted',
        ], [
            'terms.accepted' => 'Devam etmek için kullanım koşullarını kabul etmelisiniz.',
        ]);

        $restaurant = DB::transaction(function () use ($data) {
            $restaurant = CekirdexRestaurant::create([
                'slug'  => CekirdexRestaurant::generateSlug($data['restaurant_name']),
                'name'  => $data['restaurant_name'],
                'city'  => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status'    => 'active',
                'is_active' => true,
            ]);

            CekirdexUser::create([
                'cekirdex_restaurant_id' => $restaurant->id,
                'role'        => CekirdexUser::ROLE_OWNER,
                'name'        => $data['name'],
                'email'       => strtolower($data['email']),
                'password'    => Hash::make($data['password']),
                'phone'       => $data['phone'] ?? null,
                'is_active'   => true,
            ]);

            // Hızlı başlangıç: 3 örnek kategori + 3 örnek masa
            foreach ([
                ['Ana Yemekler', 1],
                ['İçecekler', 2],
                ['Tatlılar', 3],
            ] as [$name, $sort]) {
                CekirdexCategory::create([
                    'cekirdex_restaurant_id' => $restaurant->id,
                    'name'       => $name,
                    'slug'       => CekirdexCategory::generateSlug($restaurant->id, $name),
                    'sort_order' => $sort,
                    'is_active'  => true,
                ]);
            }

            for ($i = 1; $i <= 3; $i++) {
                CekirdexTable::create([
                    'cekirdex_restaurant_id' => $restaurant->id,
                    'name'      => 'Masa '.$i,
                    'code'      => (string) $i,
                    'qr_token'  => CekirdexTable::newQrToken(),
                    'capacity'  => 4,
                    'is_active' => true,
                ]);
            }

            return $restaurant;
        });

        Auth::guard('cekirdex')->login(
            CekirdexUser::where('cekirdex_restaurant_id', $restaurant->id)->first(),
            true
        );

        return redirect()->route('cekirdex.panel.dashboard')
            ->with('success', 'Hoş geldin! Restoranını kuruldu, menünü oluşturarak başlayabilirsin.');
    }
}
