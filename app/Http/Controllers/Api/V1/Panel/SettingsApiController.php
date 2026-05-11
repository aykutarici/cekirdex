<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsApiController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $actor      = $this->actor($request);
        $restaurant = CekirdexRestaurant::findOrFail($actor->cekirdex_restaurant_id);

        $data = $request->validate([
            'name'                              => 'sometimes|required|string|max:160',
            'description'                       => 'nullable|string|max:2000',
            'address'                           => 'nullable|string|max:255',
            'city'                              => 'nullable|string|max:80',
            'phone'                             => 'nullable|string|max:32',
            'email'                             => 'nullable|email|max:160',
            'website'                           => 'nullable|url|max:255',
            'currency'                          => 'nullable|string|size:3',
            'tax_rate'                          => 'nullable|numeric|min:0|max:50',
            'service_charge_rate'               => 'nullable|numeric|min:0|max:50',
            'primary_color'                     => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color'                   => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'slug'                              => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9\-]+$/', Rule::unique('cekirdex_restaurants', 'slug')->ignore($restaurant->id)],
            'accepts_takeaway'                  => 'nullable|boolean',
            'accepts_delivery'                  => 'nullable|boolean',
            'accepts_reservations'              => 'nullable|boolean',
            'delivery_radius_km'                => 'nullable|numeric|min:0|max:100',
            'delivery_min_amount'               => 'nullable|numeric|min:0|max:5000',
            'delivery_fee'                      => 'nullable|numeric|min:0|max:1000',
            'reservation_slot_minutes'          => 'nullable|integer|min:30|max:480',
            'reservation_slot_interval_minutes' => 'nullable|integer|min:15|max:120',
            'reservation_capacity_mode'         => 'nullable|in:tables,total,counts',
            'reservation_total_capacity'        => 'nullable|integer|min:1|max:500',
            'reservation_table_count'           => 'nullable|integer|min:0|max:200',
            'reservation_seat_count'            => 'nullable|integer|min:0|max:1000',
            'reservation_advance_days'          => 'nullable|integer|min:1|max:365',
            'latitude'                          => 'nullable|numeric|between:-90,90',
            'longitude'                         => 'nullable|numeric|between:-180,180',
            'opening_hours'                     => 'nullable|array',
            'logo'                              => 'nullable|image|max:2048',
            'cover_image'                       => 'nullable|image|max:4096',
            'remove_logo'                       => 'nullable|boolean',
            'remove_cover'                      => 'nullable|boolean',
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

        $textFields = ['name', 'description', 'address', 'city', 'phone', 'email', 'website', 'tax_rate', 'service_charge_rate', 'primary_color', 'secondary_color', 'delivery_radius_km', 'delivery_min_amount', 'delivery_fee', 'reservation_slot_minutes', 'reservation_slot_interval_minutes', 'latitude', 'longitude'];
        foreach ($textFields as $k) {
            if (array_key_exists($k, $data)) $restaurant->{$k} = $data[$k];
        }

        if (!empty($data['currency'])) $restaurant->currency = strtoupper($data['currency']);

        if (!empty($data['slug'])) {
            $restaurant->slug = $data['slug'];
        } elseif (!$restaurant->slug) {
            $restaurant->slug = CekirdexRestaurant::generateSlug($restaurant->name);
        }

        foreach (['accepts_takeaway', 'accepts_delivery', 'accepts_reservations'] as $k) {
            if (array_key_exists($k, $data)) $restaurant->{$k} = (bool) $data[$k];
        }

        if (array_key_exists('reservation_capacity_mode', $data)) {
            $restaurant->reservation_capacity_mode = $data['reservation_capacity_mode'];
        }

        foreach (['reservation_total_capacity', 'reservation_table_count', 'reservation_seat_count', 'reservation_advance_days'] as $k) {
            if (!array_key_exists($k, $data)) continue;
            $restaurant->{$k} = ($data[$k] === '' || $data[$k] === null) ? null : (int) $data[$k];
        }

        if (array_key_exists('opening_hours', $data)) {
            $hours = [];
            foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $d) {
                $row = $data['opening_hours'][$d] ?? null;
                if (is_array($row) && !empty($row[0]) && !empty($row[1])) {
                    $open  = preg_match('/^\d{2}:\d{2}$/', $row[0]) ? $row[0] : null;
                    $close = preg_match('/^\d{2}:\d{2}$/', $row[1]) ? $row[1] : null;
                    if ($open && $close && $open < $close) {
                        $hours[$d] = [$open, $close];
                    }
                }
            }
            $restaurant->opening_hours = empty($hours) ? null : $hours;
        }

        $restaurant->save();

        return response()->json(['message' => 'Ayarlar kaydedildi.', 'data' => $restaurant]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $actor = $this->actor($request);

        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $actor->password)) {
            return response()->json(['message' => 'Mevcut şifre hatalı.', 'errors' => ['current_password' => ['Mevcut şifre hatalı.']]], 422);
        }

        $actor->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Şifreniz güncellendi.']);
    }

    public function stockImages(): JsonResponse
    {
        $images = collect(config('cekirdex_stock', []))->map(fn ($item) => [
            'slug'  => $item['slug'] ?? null,
            'label' => $item['label'] ?? $item['slug'] ?? null,
            'url'   => isset($item['slug']) ? url('/stock-image/'.$item['slug'].'.svg') : null,
        ])->values();

        return response()->json(['data' => $images]);
    }

    private function actor(Request $request): CekirdexUser
    {
        $actor = $request->attributes->get('api_actor');
        abort_unless($actor instanceof CekirdexUser, 403, 'Bu endpoint restoran personeli içindir.');
        return $actor;
    }
}
