<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index()
    {
        $rid = $this->rid();
        $categories = CekirdexCategory::where('cekirdex_restaurant_id', $rid)
            ->orderBy('sort_order')->orderBy('id')
            ->withCount('products')->get();
        $products = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
            ->with(['category', 'variants'])
            ->orderBy('cekirdex_category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('cekirdex.panel.menu.index', compact('categories', 'products'));
    }

    /** AJAX: Stok durumunu hızlı toggle (waiter da yapabilir). */
    public function toggleStock(Request $request, int $id)
    {
        $rid = $this->rid();
        $product = CekirdexProduct::where('cekirdex_restaurant_id', $rid)->findOrFail($id);
        $product->is_in_stock = !$product->is_in_stock;
        $product->save();
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'is_in_stock' => $product->is_in_stock,
            ]);
        }
        return back()->with('success', $product->is_in_stock ? $product->name.' tekrar satışta' : $product->name.' bugün için kaldırıldı');
    }

    // ── Kategoriler ─────────────────────────────────────────────────────
    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);
        $rid = $this->rid();
        CekirdexCategory::create([
            'cekirdex_restaurant_id' => $rid,
            'name'        => $data['name'],
            'slug'        => CekirdexCategory::generateSlug($rid, $data['name']),
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => (bool) ($data['is_active'] ?? true),
        ]);
        return back()->with('success', 'Kategori eklendi.');
    }

    public function updateCategory(Request $request, int $id)
    {
        $cat = CekirdexCategory::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);
        $cat->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? $cat->sort_order,
            'is_active'   => (bool) ($data['is_active'] ?? false),
        ]);
        return back()->with('success', 'Kategori güncellendi.');
    }

    public function destroyCategory(int $id)
    {
        $cat = CekirdexCategory::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        if (CekirdexProduct::where('cekirdex_category_id', $id)->exists()) {
            return back()->withErrors(['delete' => 'Bu kategoriye bağlı ürünler var. Önce ürünleri taşıyın veya silin.']);
        }
        $cat->delete();
        return back()->with('success', 'Kategori silindi.');
    }

    // ── Ürünler ─────────────────────────────────────────────────────────
    public function storeProduct(Request $request)
    {
        $rid = $this->rid();
        $data = $request->validate([
            'cekirdex_category_id' => 'required|exists:cekirdex_categories,id',
            'name'                 => 'required|string|max:160',
            'description'          => 'nullable|string|max:2000',
            'price'                => 'required|numeric|min:0',
            'discount_price'       => 'nullable|numeric|min:0',
            'preparation_minutes'  => 'nullable|integer|min:0|max:600',
            'is_popular'           => 'nullable|boolean',
            'is_new'               => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'is_in_stock'          => 'nullable|boolean',
            'track_stock'          => 'nullable|boolean',
            'stock_quantity'       => 'nullable|integer|min:0|max:999999',
            'allergens'            => 'nullable|array',
            'allergens.*'          => 'string|in:'.implode(',', array_keys(CekirdexProduct::ALLERGENS)),
            'variants'             => 'nullable|array|max:8',
            'variants.*.name'      => 'required_with:variants.*|string|max:100',
            'variants.*.price_adjust' => 'nullable|numeric|min:-10000|max:10000',
            'variants.*.is_default'   => 'nullable|boolean',
            'image'                => 'nullable|image|max:4096',
            'image_source'         => 'nullable|in:upload,stock,gallery,none',
            'image_value'          => 'nullable|string|max:255',
        ]);

        $catBelongs = CekirdexCategory::where('id', $data['cekirdex_category_id'])
            ->where('cekirdex_restaurant_id', $rid)->exists();
        if (!$catBelongs) {
            abort(403);
        }

        $imagePath = $this->resolveImage($request, null, $rid);

        $product = CekirdexProduct::create([
            'cekirdex_restaurant_id' => $rid,
            'cekirdex_category_id'   => $data['cekirdex_category_id'],
            'name'                   => $data['name'],
            'slug'                   => CekirdexProduct::generateSlug($rid, $data['name']),
            'description'            => $data['description'] ?? null,
            'price'                  => $data['price'],
            'discount_price'         => $data['discount_price'] ?? null,
            'preparation_minutes'    => $data['preparation_minutes'] ?? 0,
            'is_popular'             => (bool) ($data['is_popular'] ?? false),
            'is_new'                 => (bool) ($data['is_new'] ?? false),
            'is_active'              => (bool) ($data['is_active'] ?? true),
            'is_in_stock'            => (bool) ($data['is_in_stock'] ?? true),
            'track_stock'            => (bool) ($data['track_stock'] ?? false),
            'stock_quantity'         => array_key_exists('stock_quantity', $data) && $data['stock_quantity'] !== null && $data['stock_quantity'] !== ''
                ? (int) $data['stock_quantity'] : null,
            'allergens'              => array_values($data['allergens'] ?? []),
            'image'                  => $imagePath,
        ]);

        $this->syncVariants($product, $request->input('variants', []));

        return back()->with('success', 'Ürün eklendi.');
    }

    public function updateProduct(Request $request, int $id)
    {
        $rid = $this->rid();
        $product = CekirdexProduct::where('cekirdex_restaurant_id', $rid)->findOrFail($id);
        $data = $request->validate([
            'cekirdex_category_id' => 'required|exists:cekirdex_categories,id',
            'name'                 => 'required|string|max:160',
            'description'          => 'nullable|string|max:2000',
            'price'                => 'required|numeric|min:0',
            'discount_price'       => 'nullable|numeric|min:0',
            'preparation_minutes'  => 'nullable|integer|min:0|max:600',
            'is_popular'           => 'nullable|boolean',
            'is_new'               => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'is_in_stock'          => 'nullable|boolean',
            'track_stock'          => 'nullable|boolean',
            'stock_quantity'       => 'nullable|integer|min:0|max:999999',
            'allergens'            => 'nullable|array',
            'allergens.*'          => 'string|in:'.implode(',', array_keys(CekirdexProduct::ALLERGENS)),
            'variants'             => 'nullable|array|max:8',
            'variants.*.name'      => 'required_with:variants.*|string|max:100',
            'variants.*.price_adjust' => 'nullable|numeric|min:-10000|max:10000',
            'variants.*.is_default'   => 'nullable|boolean',
            'image'                => 'nullable|image|max:4096',
            'remove_image'         => 'nullable|boolean',
            'image_source'         => 'nullable|in:upload,stock,gallery,none',
            'image_value'          => 'nullable|string|max:255',
        ]);

        $catBelongs = CekirdexCategory::where('id', $data['cekirdex_category_id'])
            ->where('cekirdex_restaurant_id', $rid)->exists();
        if (!$catBelongs) {
            abort(403);
        }

        if (!empty($data['remove_image'])) {
            $this->forgetImage($product->image);
            $product->image = null;
        }

        $newImage = $this->resolveImage($request, $product, $rid);
        if ($newImage !== null) {
            // Eski yüklenmiş bir dosyayı sadece yenisi yüklendiğinde sil (galeride kullanılan diğer ürünleri bozmamak için).
            if (!empty($product->image)
                && !str_starts_with((string) $product->image, 'stock:')
                && !str_starts_with((string) $newImage, 'stock:')
                && $product->image !== $newImage
                && $request->hasFile('image')) {
                $this->forgetImageIfUnused($product->image, $product->id, $rid);
            }
            $product->image = $newImage;
        }

        $product->fill([
            'cekirdex_category_id' => $data['cekirdex_category_id'],
            'name'                 => $data['name'],
            'description'          => $data['description'] ?? null,
            'price'                => $data['price'],
            'discount_price'       => $data['discount_price'] ?? null,
            'preparation_minutes'  => $data['preparation_minutes'] ?? 0,
            'is_popular'           => (bool) ($data['is_popular'] ?? false),
            'is_new'                => (bool) ($data['is_new'] ?? false),
            'is_active'             => (bool) ($data['is_active'] ?? false),
            'is_in_stock'           => (bool) ($data['is_in_stock'] ?? true),
            'track_stock'           => (bool) ($data['track_stock'] ?? false),
            'stock_quantity'        => array_key_exists('stock_quantity', $data) && $data['stock_quantity'] !== null && $data['stock_quantity'] !== ''
                ? (int) $data['stock_quantity'] : null,
            'allergens'             => array_values($data['allergens'] ?? []),
        ])->save();

        $this->syncVariants($product, $request->input('variants', []));

        return back()->with('success', 'Ürün güncellendi.');
    }

    /**
     * Ürün varyasyonlarını formdaki listeye göre senkronize eder.
     * Form basit (ad + fiyat farkı + default). Varyant tablosu sıfırlanıp tekrar yazılır
     * çünkü düzenleme sırasında ID'lerle uğraşmak yerine basit bir senkronizasyon
     * yeterli; gelecekte stok/aktiflik için ayrı bir CRUD sayfası ekleyebiliriz.
     */
    private function syncVariants(CekirdexProduct $product, $variants): void
    {
        $variants = is_array($variants) ? $variants : [];
        $clean = [];
        $hasDefault = false;
        foreach ($variants as $i => $v) {
            $name = trim((string) ($v['name'] ?? ''));
            if ($name === '') continue;
            $isDefault = !empty($v['is_default']);
            if ($isDefault) {
                if ($hasDefault) $isDefault = false;
                else $hasDefault = true;
            }
            $clean[] = [
                'cekirdex_product_id' => $product->id,
                'name'                => mb_substr($name, 0, 100),
                'price_adjust'        => round((float) ($v['price_adjust'] ?? 0), 2),
                'is_default'          => $isDefault,
                'is_active'           => true,
                'sort_order'          => $i,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }
        // Hiç default yoksa ilk varyasyon default olsun
        if (!empty($clean) && !$hasDefault) $clean[0]['is_default'] = true;

        CekirdexProductVariant::where('cekirdex_product_id', $product->id)->delete();
        if (!empty($clean)) {
            CekirdexProductVariant::insert($clean);
        }
    }

    public function destroyProduct(int $id)
    {
        $product = CekirdexProduct::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $imagePath = $product->image;
        CekirdexProductVariant::where('cekirdex_product_id', $product->id)->delete();
        $product->delete();
        // Aynı görseli kullanan başka ürün yoksa storage'dan sil.
        if ($imagePath && !str_starts_with($imagePath, 'stock:')) {
            $this->forgetImageIfUnused($imagePath, null, $this->rid());
        }
        return back()->with('success', 'Ürün silindi.');
    }

    /**
     * Form'dan gelen görseli çözümle:
     *   - source=upload: dosya yüklenmiş ise storage'a kaydet, path döner.
     *   - source=stock:  "stock:slug" formatında manifest doğrulaması yapılır.
     *   - source=gallery: restoranın eski bir ürün görselinin path'i (validate edilir).
     *   - source=none:    null (mevcut korunur).
     *   - source belirtilmezse upload var mı kontrol et (geriye dönük uyumluluk).
     *
     * Mevcut görsel değiştirilmesin diyenler için null döner; çağıran kod o zaman dokunmaz.
     */
    private function resolveImage(Request $request, ?CekirdexProduct $product, int $rid): ?string
    {
        $source = (string) $request->input('image_source', '');
        $value  = (string) $request->input('image_value', '');

        // Yeni dosya yüklendiyse kesin upload yapalım
        if ($request->hasFile('image')) {
            return $request->file('image')->store('cekirdex/products', 'public');
        }

        if ($source === 'stock' && $value !== '') {
            if (!str_starts_with($value, 'stock:')) return null;
            $slug = substr($value, 6);
            $exists = collect(config('cekirdex_stock', []))->contains('slug', $slug);
            return $exists ? 'stock:'.$slug : null;
        }

        if ($source === 'gallery' && $value !== '') {
            // Galeri sadece bu restoranın eski path'lerini kabul eder.
            $isOwned = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
                ->where('image', $value)
                ->exists();
            return $isOwned ? $value : null;
        }

        return null;
    }

    private function forgetImage(?string $path): void
    {
        if (!$path || str_starts_with($path, 'stock:')) return;
        Storage::disk('public')->delete($path);
    }

    private function forgetImageIfUnused(string $path, ?int $excludeProductId, int $rid): void
    {
        if (str_starts_with($path, 'stock:')) return;
        $q = CekirdexProduct::where('cekirdex_restaurant_id', $rid)->where('image', $path);
        if ($excludeProductId) $q->where('id', '!=', $excludeProductId);
        if (!$q->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}
