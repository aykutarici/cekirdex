<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductVariant;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuApiController extends Controller
{
    // ── Kategoriler ──────────────────────────────────────────────────────────

    public function storeCategory(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $category = CekirdexCategory::create([
            'cekirdex_restaurant_id' => $restaurantId,
            'name'                   => $data['name'],
            'slug'                   => CekirdexCategory::generateSlug($restaurantId, $data['name']),
            'description'            => $data['description'] ?? null,
            'sort_order'             => $data['sort_order'] ?? 0,
            'is_active'              => (bool) ($data['is_active'] ?? true),
        ]);

        return response()->json(['message' => 'Kategori oluşturuldu.', 'data' => $category], 201);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $category = CekirdexCategory::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? $category->sort_order,
            'is_active'   => (bool) ($data['is_active'] ?? false),
        ]);

        return response()->json(['message' => 'Kategori güncellendi.', 'data' => $category]);
    }

    public function destroyCategory(Request $request, int $id): JsonResponse
    {
        $category = CekirdexCategory::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($id);

        if (CekirdexProduct::query()->where('cekirdex_category_id', $id)->exists()) {
            return response()->json([
                'message' => 'Bu kategoriye bağlı ürünler var. Önce ürünleri taşıyın veya silin.',
            ], 422);
        }

        $category->delete();

        return response()->json(null, 204);
    }

    // ── Ürünler ──────────────────────────────────────────────────────────────

    public function storeProduct(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $data = $request->validate([
            'cekirdex_category_id'    => 'required|integer|exists:cekirdex_categories,id',
            'name'                    => 'required|string|max:160',
            'description'             => 'nullable|string|max:2000',
            'price'                   => 'required|numeric|min:0',
            'discount_price'          => 'nullable|numeric|min:0',
            'preparation_minutes'     => 'nullable|integer|min:0|max:600',
            'is_popular'              => 'nullable|boolean',
            'is_new'                  => 'nullable|boolean',
            'is_active'               => 'nullable|boolean',
            'is_in_stock'             => 'nullable|boolean',
            'track_stock'             => 'nullable|boolean',
            'stock_quantity'          => 'nullable|integer|min:0|max:999999',
            'allergens'               => 'nullable|array',
            'allergens.*'             => 'string|in:'.implode(',', array_keys(CekirdexProduct::ALLERGENS)),
            'sort_order'              => 'nullable|integer',
            'image_source'            => 'nullable|in:upload,stock,gallery,none',
            'image_value'             => 'nullable|string|max:255',
            'image'                   => 'nullable|image|max:4096',
            'variants'                => 'nullable|array|max:8',
            'variants.*.name'         => 'required_with:variants.*|string|max:100',
            'variants.*.price_adjust' => 'nullable|numeric|min:-10000|max:10000',
            'variants.*.is_default'   => 'nullable|boolean',
        ]);

        $catBelongs = CekirdexCategory::query()
            ->where('id', $data['cekirdex_category_id'])
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->exists();
        abort_unless($catBelongs, 403, 'Bu kategori size ait değil.');

        $imagePath = $this->resolveImage($request, null, $restaurantId);

        $product = CekirdexProduct::create([
            'cekirdex_restaurant_id' => $restaurantId,
            'cekirdex_category_id'   => $data['cekirdex_category_id'],
            'name'                   => $data['name'],
            'slug'                   => CekirdexProduct::generateSlug($restaurantId, $data['name']),
            'description'            => $data['description'] ?? null,
            'price'                  => $data['price'],
            'discount_price'         => $data['discount_price'] ?? null,
            'preparation_minutes'    => $data['preparation_minutes'] ?? 0,
            'is_popular'             => (bool) ($data['is_popular'] ?? false),
            'is_new'                 => (bool) ($data['is_new'] ?? false),
            'is_active'              => (bool) ($data['is_active'] ?? true),
            'is_in_stock'            => (bool) ($data['is_in_stock'] ?? true),
            'track_stock'            => (bool) ($data['track_stock'] ?? false),
            'stock_quantity'         => isset($data['stock_quantity']) && $data['stock_quantity'] !== null ? (int) $data['stock_quantity'] : null,
            'allergens'              => array_values($data['allergens'] ?? []),
            'sort_order'             => $data['sort_order'] ?? 0,
            'image'                  => $imagePath,
        ]);

        $this->syncVariants($product, $request->input('variants', []));

        return response()->json(['message' => 'Ürün oluşturuldu.', 'data' => $product->load('variants')], 201);
    }

    public function updateProduct(Request $request, int $id): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $product = CekirdexProduct::query()
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->findOrFail($id);

        $data = $request->validate([
            'cekirdex_category_id'    => 'required|integer|exists:cekirdex_categories,id',
            'name'                    => 'required|string|max:160',
            'description'             => 'nullable|string|max:2000',
            'price'                   => 'required|numeric|min:0',
            'discount_price'          => 'nullable|numeric|min:0',
            'preparation_minutes'     => 'nullable|integer|min:0|max:600',
            'is_popular'              => 'nullable|boolean',
            'is_new'                  => 'nullable|boolean',
            'is_active'               => 'nullable|boolean',
            'is_in_stock'             => 'nullable|boolean',
            'track_stock'             => 'nullable|boolean',
            'stock_quantity'          => 'nullable|integer|min:0|max:999999',
            'allergens'               => 'nullable|array',
            'allergens.*'             => 'string|in:'.implode(',', array_keys(CekirdexProduct::ALLERGENS)),
            'sort_order'              => 'nullable|integer',
            'image_source'            => 'nullable|in:upload,stock,gallery,none',
            'image_value'             => 'nullable|string|max:255',
            'image'                   => 'nullable|image|max:4096',
            'remove_image'            => 'nullable|boolean',
            'variants'                => 'nullable|array|max:8',
            'variants.*.name'         => 'required_with:variants.*|string|max:100',
            'variants.*.price_adjust' => 'nullable|numeric|min:-10000|max:10000',
            'variants.*.is_default'   => 'nullable|boolean',
        ]);

        $catBelongs = CekirdexCategory::query()
            ->where('id', $data['cekirdex_category_id'])
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->exists();
        abort_unless($catBelongs, 403, 'Bu kategori size ait değil.');

        if (!empty($data['remove_image'])) {
            $this->deleteImageIfUnused($product->image, $product->id, $restaurantId);
            $product->image = null;
        }

        $newImage = $this->resolveImage($request, $product, $restaurantId);
        if ($newImage !== null) {
            if (!empty($product->image) && !str_starts_with((string) $product->image, 'stock:') && !str_starts_with((string) $newImage, 'stock:') && $product->image !== $newImage && $request->hasFile('image')) {
                $this->deleteImageIfUnused($product->image, $product->id, $restaurantId);
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
            'is_new'               => (bool) ($data['is_new'] ?? false),
            'is_active'            => (bool) ($data['is_active'] ?? false),
            'is_in_stock'          => (bool) ($data['is_in_stock'] ?? true),
            'track_stock'          => (bool) ($data['track_stock'] ?? false),
            'stock_quantity'       => isset($data['stock_quantity']) && $data['stock_quantity'] !== null ? (int) $data['stock_quantity'] : null,
            'allergens'            => array_values($data['allergens'] ?? []),
            'sort_order'           => $data['sort_order'] ?? $product->sort_order,
        ])->save();

        $this->syncVariants($product, $request->input('variants', []));

        return response()->json(['message' => 'Ürün güncellendi.', 'data' => $product->load('variants')]);
    }

    public function destroyProduct(Request $request, int $id): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $product = CekirdexProduct::query()
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->findOrFail($id);

        $imagePath = $product->image;
        CekirdexProductVariant::query()->where('cekirdex_product_id', $product->id)->delete();
        $product->delete();

        if ($imagePath && !str_starts_with($imagePath, 'stock:')) {
            $this->deleteImageIfUnused($imagePath, null, $restaurantId);
        }

        return response()->json(null, 204);
    }

    public function toggleStock(Request $request, int $id): JsonResponse
    {
        $product = CekirdexProduct::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($id);

        $product->is_in_stock = !$product->is_in_stock;
        $product->save();

        return response()->json(['is_in_stock' => $product->is_in_stock]);
    }

    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $product = CekirdexProduct::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($id);

        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json(['is_active' => $product->is_active]);
    }

    private function syncVariants(CekirdexProduct $product, mixed $variants): void
    {
        $variants   = is_array($variants) ? $variants : [];
        $clean      = [];
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

        if (!empty($clean) && !$hasDefault) $clean[0]['is_default'] = true;

        CekirdexProductVariant::query()->where('cekirdex_product_id', $product->id)->delete();
        if (!empty($clean)) {
            CekirdexProductVariant::insert($clean);
        }
    }

    private function resolveImage(Request $request, ?CekirdexProduct $product, int $restaurantId): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('cekirdex/products', 'public');
        }

        $source = (string) $request->input('image_source', '');
        $value  = (string) $request->input('image_value', '');

        if ($source === 'stock' && $value !== '') {
            if (!str_starts_with($value, 'stock:')) return null;
            $slug   = substr($value, 6);
            $exists = collect(config('cekirdex_stock', []))->contains('slug', $slug);
            return $exists ? 'stock:'.$slug : null;
        }

        if ($source === 'gallery' && $value !== '') {
            $isOwned = CekirdexProduct::query()
                ->where('cekirdex_restaurant_id', $restaurantId)
                ->where('image', $value)
                ->exists();
            return $isOwned ? $value : null;
        }

        return null;
    }

    private function deleteImageIfUnused(string $path, ?int $excludeProductId, int $restaurantId): void
    {
        if (str_starts_with($path, 'stock:')) return;

        $query = CekirdexProduct::query()->where('cekirdex_restaurant_id', $restaurantId)->where('image', $path);
        if ($excludeProductId) $query->where('id', '!=', $excludeProductId);

        if (!$query->exists()) {
            Storage::disk('public')->delete($path);
        }
    }

    private function restaurantId(Request $request): int
    {
        return (int) $this->actor($request)->cekirdex_restaurant_id;
    }

    private function actor(Request $request): CekirdexUser
    {
        $actor = $request->attributes->get('api_actor');
        abort_unless($actor instanceof CekirdexUser, 403, 'Bu endpoint restoran personeli içindir.');
        return $actor;
    }
}
