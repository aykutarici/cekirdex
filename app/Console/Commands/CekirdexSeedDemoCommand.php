<?php

namespace App\Console\Commands;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductVariant;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * `php artisan cekirdex:seed-demo`
 * Production'da hızlı test için bir demo restoran oluşturur.
 * Idempotent — aynı slug varsa ürünler güncellenir; stok görseller atanır.
 */
class CekirdexSeedDemoCommand extends Command
{
    protected $signature = 'cekirdex:seed-demo {--email=demo@cekirdex.com} {--password=cekirdex123}';

    protected $description = 'Çekirdex için demo restoran, geniş örnek menü (Unsplash stok görselleri) ve masalar oluşturur.';

    public function handle(): int
    {
        $slug = 'demir-kafe';

        $rest = CekirdexRestaurant::firstOrCreate(
            ['slug' => $slug],
            [
                'name'                       => 'Demir Kafe',
                'description'                => 'Tek kaynak kahveler, geniş menü ve ev yapımı tatlılarla samimi bir mekân.',
                'address'                    => 'Bağdat Cad. No:123, Kadıköy',
                'city'                       => 'İstanbul',
                'phone'                      => '+90 555 123 45 67',
                'email'                      => 'merhaba@demirkafe.com',
                'currency'                   => 'TRY',
                'tax_rate'                   => 10,
                'service_charge_rate'        => 0,
                'accepts_takeaway'           => true,
                'accepts_delivery'           => true,
                'delivery_fee'               => 29,
                'delivery_min_amount'        => 120,
                'accepts_reservations'       => true,
                'is_active'                  => true,
                'status'                     => 'active',
                'primary_color'              => '#ff6b35',
                'secondary_color'            => '#d9531c',
            ]
        );

        $email = (string) $this->option('email');
        $owner = CekirdexUser::firstOrCreate(
            ['email' => strtolower($email)],
            [
                'cekirdex_restaurant_id' => $rest->id,
                'role'      => CekirdexUser::ROLE_OWNER,
                'name'      => 'Demo Sahip',
                'password'  => Hash::make($this->option('password')),
                'is_active' => true,
            ]
        );

        $catSpecs = [
            ['Sıcak İçecekler', 1],
            ['Soğuk İçecekler', 2],
            ['Kahvaltı', 3],
            ['Salata & Hafif', 4],
            ['Ana Yemekler', 5],
            ['Pizza & Fırın', 6],
            ['Tatlılar', 7],
            ['Atıştırmalık', 8],
        ];
        $catIds = [];
        foreach ($catSpecs as [$name, $sort]) {
            $cat = CekirdexCategory::firstOrCreate(
                ['cekirdex_restaurant_id' => $rest->id, 'name' => $name],
                ['slug' => CekirdexCategory::generateSlug($rest->id, $name), 'sort_order' => $sort, 'is_active' => true]
            );
            $catIds[$name] = $cat->id;
        }

        // [kategori, ürün adı, fiyat, açıklama, popüler, stock slug]
        $productSpecs = [
            ['Sıcak İçecekler', 'Espresso', 70, 'Kısa ve yoğun shot.', false, 'espresso'],
            ['Sıcak İçecekler', 'Filtre Kahve', 85, 'Tek kaynak Etiyopya, V60 demleme.', true, 'filtre-kahve'],
            ['Sıcak İçecekler', 'Latte', 105, 'Çift shot ve buharlı süt.', true, 'latte'],
            ['Sıcak İçecekler', 'Cappuccino', 100, 'Köpüklü klasik.', false, 'cappuccino'],
            ['Sıcak İçecekler', 'Türk Kahvesi', 65, 'Cezvede, lokum eşliğinde.', false, 'turk-kahvesi'],
            ['Sıcak İçecekler', 'Çay (İnce belli)', 35, 'Demlik çay.', false, 'cay'],
            ['Sıcak İçecekler', 'Salep', 90, 'Tarçın serpmeli.', false, 'salep'],
            ['Soğuk İçecekler', 'Limonata', 75, 'Ev yapımı.', true, 'limonata'],
            ['Soğuk İçecekler', 'Soğuk Latte', 110, 'Buz ve süt dengesi.', false, 'soguk-kahve'],
            ['Soğuk İçecekler', 'Smoothie (Çilek)', 120, 'Meyve ve yoğurt.', false, 'smoothie'],
            ['Soğuk İçecekler', 'Ayran', 40, 'Ev yapımı.', false, 'ayran'],
            ['Kahvaltı', 'Serpme Kahvaltı (kişi başı)', 290, 'Peynir, zeytin, reçel, domates, salatalık.', true, 'kahvalti-tabagi'],
            ['Kahvaltı', 'Menemen', 155, 'Soğanlı veya soğansız not düşebilirsiniz.', false, 'menemen'],
            ['Kahvaltı', 'Avokado Toast', 175, 'Çavdar ekmeği, çeri domates.', true, 'avokado'],
            ['Kahvaltı', 'Waffle & Maple', 165, 'Çikolata veya meyve seçeneği.', false, 'waffle'],
            ['Salata & Hafif', 'Sezar Salata', 185, 'Izgara tavuk ve parmesan.', true, 'sezar-salata'],
            ['Salata & Hafif', 'Akdeniz Salata', 155, 'Zeytinyağı-limon.', false, 'salata'],
            ['Ana Yemekler', 'Klasik Burger', 220, 'Köfte, cheddar, özel sos.', true, 'hamburger'],
            ['Ana Yemekler', 'Tavuk Wrap', 195, 'Akdeniz yeşillikleri ve yoğurt sos.', false, 'wrap'],
            ['Ana Yemekler', 'Izgara Somon', 325, 'Limonlu tereyağı ve roka.', false, 'balik'],
            ['Ana Yemekler', 'Makarna Alfredo', 210, 'Kremalı parmesan sosu.', false, 'makarna'],
            ['Pizza & Fırın', 'Margherita Pizza', 240, 'Mozzarella, fesleğen, domates sosu.', true, 'pizza-margarita'],
            ['Pizza & Fırın', 'Karışık Pizza', 275, 'Sucuk, mantar, mısır, zeytin.', true, 'pizza'],
            ['Pizza & Fırın', 'Lahmacun (3 adet)', 180, 'Acılı-acısız not.', false, 'lahmacun'],
            ['Tatlılar', 'San Sebastian Cheesecake', 165, 'Karamel sos opsiyonel.', true, 'pasta'],
            ['Tatlılar', 'Brownie', 120, 'Sıcak servis, dondurma +25 ₺.', false, 'brownie'],
            ['Tatlılar', 'Baklava (4 dilim)', 140, 'Antep fıstığı.', false, 'baklava'],
            ['Tatlılar', 'Dondurma (2 top)', 90, 'Vanilya / çikolata / fıstık.', false, 'dondurma'],
            ['Atıştırmalık', 'Patates Kızartması', 85, 'Acı sos dahil.', true, 'patates-kizart'],
            ['Atıştırmalık', 'Nachos', 120, 'Cheddar ve jalapeno.', false, 'nachos'],
            ['Atıştırmalık', 'Soğan Halkası', 95, 'Ranch dip.', false, 'soğan-halkasi'],
        ];

        $variantsByProductName = [
            'Latte' => [
                ['name' => 'Small (240 ml)', 'price_adjust' => -15, 'is_default' => true],
                ['name' => 'Large (360 ml)', 'price_adjust' => 20, 'is_default' => false],
            ],
            'Margherita Pizza' => [
                ['name' => 'Küçük (25 cm)', 'price_adjust' => -40, 'is_default' => false],
                ['name' => 'Orta (30 cm)', 'price_adjust' => 0, 'is_default' => true],
                ['name' => 'Büyük (35 cm)', 'price_adjust' => 55, 'is_default' => false],
            ],
            'Klasik Burger' => [
                ['name' => 'Tek köfte', 'price_adjust' => 0, 'is_default' => true],
                ['name' => 'Double köfte', 'price_adjust' => 65, 'is_default' => false],
            ],
        ];

        foreach ($productSpecs as [$catName, $name, $price, $desc, $popular, $stockSlug]) {
            if (!collect(config('cekirdex_stock', []))->contains('slug', $stockSlug)) {
                $this->warn("Bilinmeyen stok slug atlandı: {$stockSlug} ({$name})");
                continue;
            }
            $img = 'stock:'.$stockSlug;
            $product = CekirdexProduct::firstOrNew([
                'cekirdex_restaurant_id' => $rest->id,
                'name'                   => $name,
            ]);
            if (!$product->exists) {
                $product->slug = CekirdexProduct::generateSlug($rest->id, $name);
            }
            $product->fill([
                'cekirdex_category_id' => $catIds[$catName],
                'description'          => $desc,
                'price'                => $price,
                'image'                => $img,
                'is_active'            => true,
                'is_in_stock'          => true,
                'is_popular'           => $popular,
            ])->save();

            if (isset($variantsByProductName[$name])) {
                $this->syncVariants($product->id, $variantsByProductName[$name]);
            }
        }

        for ($i = 1; $i <= 6; $i++) {
            CekirdexTable::firstOrCreate(
                ['cekirdex_restaurant_id' => $rest->id, 'name' => 'Masa '.$i],
                [
                    'code'      => (string) $i,
                    'qr_token'  => CekirdexTable::newQrToken(),
                    'capacity'  => 4,
                    'is_active' => true,
                ]
            );
        }

        $this->info('✓ Demo restoran hazır: '.$rest->name);
        $this->line('  Panel:        '.url('/giris'));
        $this->line('  E-posta:      '.$owner->email);
        $this->line('  Şifre:        '.$this->option('password'));
        $firstQr = CekirdexTable::where('cekirdex_restaurant_id', $rest->id)->first();
        if ($firstQr) {
            $this->line('  Müşteri QR:   '.url('/m/'.$firstQr->qr_token));
        }
        $this->line('  Public:        '.route('cekirdex.public.show', $rest->slug));
        $this->comment('  Görseller: config/cekirdex_stock.php → Unsplash (ticari kullanıma uygun lisans).');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{name: string, price_adjust: float|int, is_default: bool}>  $rows
     */
    private function syncVariants(int $productId, array $rows): void
    {
        CekirdexProductVariant::where('cekirdex_product_id', $productId)->delete();
        $sort = 0;
        foreach ($rows as $row) {
            CekirdexProductVariant::create([
                'cekirdex_product_id' => $productId,
                'name'                => $row['name'],
                'price_adjust'        => $row['price_adjust'],
                'is_default'          => $row['is_default'],
                'is_active'           => true,
                'sort_order'          => $sort++,
            ]);
        }
    }
}
