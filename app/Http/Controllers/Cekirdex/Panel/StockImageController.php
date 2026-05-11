<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Çekirdex stok görselleri + restoran galeri (önceden yüklenmiş ürün görselleri)
 *
 * - Stok görseller `config/cekirdex_stock.php` üzerinden yönetiliyor.
 * - Her stok öğesi dinamik SVG veya (varsa) `photo` URL’sine yönlendirme ile gösterilir.
 * - Ürünün `image` alanına `stock:slug` formatında kaydediliyor.
 * - Görüntüleme tarafında `CekirdexProduct@image_url` accessor'ı doğru URL'e çevirir.
 */
class StockImageController extends Controller
{
    /** Stok kategorileri için kullanıcı dostu Türkçe etiketler. */
    public const CATEGORY_LABELS = [
        'burger'      => 'Burger & Sandviç',
        'pizza'       => 'Pizza & Pide',
        'meat'        => 'Et & Tavuk',
        'seafood'     => 'Deniz Ürünleri',
        'salad'       => 'Salata & Sebze',
        'soup'        => 'Çorba',
        'pasta'       => 'Makarna & Pilav',
        'side'        => 'Yan Ürünler',
        'breakfast'   => 'Kahvaltı',
        'dessert'     => 'Tatlılar',
        'drink-hot'   => 'Sıcak İçecekler',
        'drink-cold'  => 'Soğuk İçecekler',
        'other'       => 'Diğer',
    ];

    /**
     * Listeleme — stok ve galeri öğelerini birleştirip frontend'e döner.
     * Auth gerektirir; modal açılırken çağrılır.
     */
    public function browse(Request $request)
    {
        $rid = (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
        $q   = mb_strtolower(trim((string) $request->query('q', '')));
        $cat = (string) $request->query('category', '');

        $stock = collect(config('cekirdex_stock', []));
        if ($cat !== '' && $cat !== 'all') {
            $stock = $stock->where('category', $cat)->values();
        }
        if ($q !== '') {
            $stock = $stock->filter(function ($item) use ($q) {
                if (str_contains(mb_strtolower($item['name']), $q))     return true;
                if (str_contains(mb_strtolower($item['slug']), $q))     return true;
                foreach (($item['tags'] ?? []) as $t) {
                    if (str_contains(mb_strtolower($t), $q)) return true;
                }
                return false;
            })->values();
        }

        $stockOut = $stock->map(fn ($it) => [
            'slug'     => $it['slug'],
            'name'     => $it['name'],
            'category' => $it['category'],
            'cat_label'=> self::CATEGORY_LABELS[$it['category']] ?? $it['category'],
            'value'    => 'stock:'.$it['slug'],
            'url'      => !empty($it['photo'])
                ? $it['photo']
                : route('cekirdex.stock.image', ['slug' => $it['slug']]),
        ])->values();

        // Galeri: bu restoranın önceden yüklediği eşsiz ürün görselleri (storage path'leri).
        $gallery = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
            ->whereNotNull('image')
            ->where('image', 'not like', 'stock:%')
            ->orderByDesc('updated_at')
            ->pluck('image')
            ->unique()
            ->take(80)
            ->map(fn ($path) => [
                'value' => $path,
                'url'   => asset('storage/'.$path),
                'name'  => basename($path),
            ])
            ->values();

        $cats = collect(self::CATEGORY_LABELS)
            ->map(fn ($lbl, $key) => ['key' => $key, 'label' => $lbl])
            ->values();

        return response()->json([
            'ok'         => true,
            'categories' => $cats,
            'stock'      => $stockOut,
            'gallery'    => $gallery,
        ]);
    }

    /**
     * Bir stok görselin SVG render'ı.
     * URL: /stock-image/{slug}.svg
     * Public; CDN ve tarayıcı cache'leyebilir.
     */
    public function image(string $slug)
    {
        $manifest = collect(config('cekirdex_stock', []))->firstWhere('slug', $slug);
        if (!$manifest) abort(404);

        if (!empty($manifest['photo']) && filter_var($manifest['photo'], FILTER_VALIDATE_URL)) {
            return redirect()->away($manifest['photo'], 302)
                ->header('Cache-Control', 'public, max-age=86400');
        }

        $bg1 = $manifest['bg'][0] ?? '#fff1cf';
        $bg2 = $manifest['bg'][1] ?? '#ffd089';
        $emoji = $manifest['emoji'] ?? '🍽️';
        $name  = $manifest['name'] ?? '';

        $svg = $this->renderSvg($bg1, $bg2, $emoji, $name);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=2592000, immutable', // 30 gün
            'ETag'          => '"'.md5($svg).'"',
        ]);
    }

    private function renderSvg(string $bg1, string $bg2, string $emoji, string $name): string
    {
        $bg1e = htmlspecialchars($bg1, ENT_QUOTES);
        $bg2e = htmlspecialchars($bg2, ENT_QUOTES);
        $emE  = htmlspecialchars($emoji, ENT_QUOTES);
        $nmE  = htmlspecialchars($name, ENT_QUOTES);

        // Geniş emoji desteği için sistem emoji fontları.
        $fontStack = "'Apple Color Emoji','Segoe UI Emoji','Noto Color Emoji','Segoe UI Symbol','Twemoji Mozilla','EmojiOne Color',sans-serif";

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 540" preserveAspectRatio="xMidYMid slice">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bg1e}"/>
      <stop offset="100%" stop-color="{$bg2e}"/>
    </linearGradient>
    <radialGradient id="halo" cx="50%" cy="50%" r="50%">
      <stop offset="0%"  stop-color="rgba(255,255,255,.55)"/>
      <stop offset="60%" stop-color="rgba(255,255,255,0)"/>
    </radialGradient>
  </defs>
  <rect width="960" height="540" fill="url(#g)"/>
  <ellipse cx="480" cy="300" rx="320" ry="200" fill="url(#halo)"/>
  <text x="480" y="340" text-anchor="middle"
        font-family="{$fontStack}" font-size="200">{$emE}</text>
  <text x="480" y="500" text-anchor="middle"
        font-family="Inter, system-ui, sans-serif" font-weight="700" font-size="24"
        fill="rgba(28,25,51,.55)" letter-spacing="0.5">{$nmE}</text>
</svg>
SVG;
    }
}
