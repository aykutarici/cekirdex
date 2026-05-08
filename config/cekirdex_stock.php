<?php

/*
|--------------------------------------------------------------------------
| Çekirdex — Stok ürün görsel kataloğu
|--------------------------------------------------------------------------
| - slug / name / category / emoji / bg / tags: aynı şekilde kullanılır.
| - photo: Gerçek fotoğraf URL’si (Unsplash — Unsplash License, ticari kullanıma uygun).
|   https://unsplash.com/license — Atıf iyi bir uygulama; lisans gereği zorunlu değildir.
| - photo yoksa StockImageController yalnızca emoji tabanlı SVG üretir.
| - Üründe: cekirdex_products.image = "stock:slug" — URL .svg ile açılır; fotoğraf varsa oraya yönlendirilir.
*/

$p = static function (string $photoId): string {
    return 'https://images.unsplash.com/photo-'.$photoId.'?auto=format&fit=crop&w=1400&q=82';
};

/** Tekrar kullanılan Unsplash photo-id (hepsi HTTP 200 doğrulandı) */
$U = [
    'burger'     => '1568901346375-23c9450c58cd',
    'pizza'      => '1513104890138-7c749659a591',
    'sandwich'   => '1512621776951-a57141f2eefd',
    'coffee'     => '1497935586351-b67a49e012bf',
    'latte'      => '1461023058943-07fcbe16d735',
    'tea'        => '1497935586351-b67a49e012bf',
    'salad'      => '1512621776951-a57141f2eefd',
    'steak'      => '1544025162-d76694265947',
    'pasta'      => '1513104890138-7c749659a591',
    'fries'      => '1573080496219-bb080dd4f877',
    'soup'       => '1547592166-23ac45744acd',
    'ramen'      => '1569718212165-3a8278d5f624',
    'sushi'      => '1579584425555-c3ce17fd4351',
    'seafood'    => '1559339352-11d035aa65de',
    'breakfast'  => '1506617420156-8e4536971650',
    'pancake'    => '1578985545062-69928b1d9587',
    'cake'       => '1578985545062-69928b1d9587',
    'icecream'   => '1563805042-7684c019e1cb',
    'donut'      => '1551024601-bec78aea704b',
    'cookie'     => '1551024601-bec78aea704b',
    'waffle'     => '1578985545062-69928b1d9587',
    'croissant'  => '1509440159596-0249088772ff',
    'beer'       => '1510812431401-41d2bd2722f3',
    'wine'       => '1510812431401-41d2bd2722f3',
    'cocktail'   => '1510812431401-41d2bd2722f3',
    'juice'      => '1490474418585-ba9bad8fd0ea',
    'water'      => '1490474418585-ba9bad8fd0ea',
    'chicken'    => '1544025162-d76694265947',
    'kebab'      => '1565299585323-38d6b0865b47',
    'taco'       => '1565299585323-38d6b0865b47',
    'wrap'       => '1565299585323-38d6b0865b47',
    'bread'      => '1509440159596-0249088772ff',
    'egg'        => '1506617420156-8e4536971650',
    'baklava'    => '1578985545062-69928b1d9587',
    'fish'       => '1579584425555-c3ce17fd4351',
    'cola'       => '1573080496219-bb080dd4f877',
    'hotchoc'    => '1461023058943-07fcbe16d735',
    'fruit'      => '1490474418585-ba9bad8fd0ea',
    'chocolate'  => '1563805042-7684c019e1cb',
];

return [

    /* ── BURGERLAR & EKMEK ARASI ─────────────────────────────────── */
    ['slug' => 'hamburger',       'name' => 'Hamburger',          'category' => 'burger',     'emoji' => '🍔', 'bg' => ['#fff1cf','#ffd089'], 'tags' => ['burger','köfte','sandviç','fastfood'],       'photo' => $p($U['burger'])],
    ['slug' => 'cheeseburger',    'name' => 'Cheeseburger',       'category' => 'burger',     'emoji' => '🍔', 'bg' => ['#ffe6b3','#ffb86b'], 'tags' => ['burger','peynirli','cheese'],                'photo' => $p($U['burger'])],
    ['slug' => 'sandvic',         'name' => 'Sandviç',            'category' => 'burger',     'emoji' => '🥪', 'bg' => ['#fff4e0','#ffd6a3'], 'tags' => ['sandwich','tost','ekmek arası'],            'photo' => $p($U['sandwich'])],
    ['slug' => 'tost',            'name' => 'Tost',               'category' => 'burger',     'emoji' => '🥪', 'bg' => ['#fff0d3','#ffc789'], 'tags' => ['tost','sandviç','kahvaltı'],               'photo' => $p($U['sandwich'])],
    ['slug' => 'durum',           'name' => 'Dürüm',              'category' => 'burger',     'emoji' => '🌯', 'bg' => ['#fff4cf','#ffd185'], 'tags' => ['dürüm','wrap','döner','köfte'],             'photo' => $p($U['wrap'])],
    ['slug' => 'wrap',            'name' => 'Wrap / Sarma',       'category' => 'burger',     'emoji' => '🌯', 'bg' => ['#fff4d0','#f3c97a'], 'tags' => ['wrap','dürüm','sarma','tortilla'],          'photo' => $p($U['wrap'])],
    ['slug' => 'burrito',         'name' => 'Burrito',            'category' => 'burger',     'emoji' => '🌯', 'bg' => ['#ffe8c8','#e8a55c'], 'tags' => ['burrito','meksika'],                      'photo' => $p($U['wrap'])],
    ['slug' => 'taco',            'name' => 'Taco',               'category' => 'meat',       'emoji' => '🌮', 'bg' => ['#ffe9c4','#f0a85a'], 'tags' => ['taco','meksika'],                       'photo' => $p($U['taco'])],
    ['slug' => 'quesadilla',      'name' => 'Quesadilla',         'category' => 'burger',     'emoji' => '🧀', 'bg' => ['#fff4d6','#f0c97a'], 'tags' => ['quesadilla','tortilla','peynir'],        'photo' => $p($U['taco'])],

    /* ── PİZZA & İTALYAN ─────────────────────────────────────────── */
    ['slug' => 'pizza',           'name' => 'Pizza',              'category' => 'pizza',      'emoji' => '🍕', 'bg' => ['#ffe1c7','#ffb87a'], 'tags' => ['pizza','italyan'],                       'photo' => $p($U['pizza'])],
    ['slug' => 'pizza-margarita', 'name' => 'Margherita Pizza',   'category' => 'pizza',      'emoji' => '🍕', 'bg' => ['#ffe2bb','#ffae6a'], 'tags' => ['pizza','margherita','margarita'],        'photo' => $p($U['pizza'])],
    ['slug' => 'lahmacun',        'name' => 'Lahmacun',           'category' => 'pizza',      'emoji' => '🫓', 'bg' => ['#ffe0c0','#ffaa6e'], 'tags' => ['lahmacun','türk pizzası'],               'photo' => $p($U['pizza'])],
    ['slug' => 'pide',            'name' => 'Pide',               'category' => 'pizza',      'emoji' => '🫓', 'bg' => ['#fff1d4','#f5b97a'], 'tags' => ['pide','türk','peynirli','kıymalı'],        'photo' => $p($U['pizza'])],
    ['slug' => 'ravioli',         'name' => 'Ravioli',            'category' => 'pasta',      'emoji' => '🍝', 'bg' => ['#ffe6cc','#ffb578'], 'tags' => ['ravioli','makarna'],                    'photo' => $p($U['pasta'])],
    ['slug' => 'risotto',         'name' => 'Risotto',            'category' => 'pasta',      'emoji' => '🍚', 'bg' => ['#fff0dd','#f0c090'], 'tags' => ['risotto','italyan'],                   'photo' => $p($U['pasta'])],

    /* ── ET / KEBAP / TAVUK ─────────────────────────────────────── */
    ['slug' => 'kebap',           'name' => 'Kebap',              'category' => 'meat',       'emoji' => '🍢', 'bg' => ['#ffe2cc','#ffa97a'], 'tags' => ['kebap','şiş','adana','urfa'],            'photo' => $p($U['kebab'])],
    ['slug' => 'sis-kofte',       'name' => 'Şiş Köfte',          'category' => 'meat',       'emoji' => '🍢', 'bg' => ['#ffd5b3','#ff9d6e'], 'tags' => ['köfte','şiş','et'],                    'photo' => $p($U['kebab'])],
    ['slug' => 'tavuk',           'name' => 'Tavuk',              'category' => 'meat',       'emoji' => '🍗', 'bg' => ['#fff0d3','#ffcb88'], 'tags' => ['tavuk','chicken','kanat'],              'photo' => $p($U['chicken'])],
    ['slug' => 'tavuk-but',       'name' => 'Tavuk But',          'category' => 'meat',       'emoji' => '🍗', 'bg' => ['#ffe9c3','#ffbd76'], 'tags' => ['tavuk','but','chicken'],               'photo' => $p($U['chicken'])],
    ['slug' => 'biftek',          'name' => 'Biftek / Steak',     'category' => 'meat',       'emoji' => '🥩', 'bg' => ['#ffd0d0','#ff8a8a'], 'tags' => ['et','biftek','steak','dana'],            'photo' => $p($U['steak'])],
    ['slug' => 'sucuk',           'name' => 'Sucuk',              'category' => 'meat',       'emoji' => '🌭', 'bg' => ['#ffd2cc','#ff977d'], 'tags' => ['sucuk','kahvaltı','sosis'],            'photo' => $p($U['steak'])],

    /* ── DENİZ ÜRÜNLERİ ─────────────────────────────────────────── */
    ['slug' => 'balik',           'name' => 'Balık',              'category' => 'seafood',    'emoji' => '🐟', 'bg' => ['#cfe9ff','#82c5ff'], 'tags' => ['balık','seafood','fish'],                'photo' => $p($U['fish'])],
    ['slug' => 'karides',         'name' => 'Karides',            'category' => 'seafood',    'emoji' => '🦐', 'bg' => ['#ffd9d2','#ff9c87'], 'tags' => ['karides','shrimp','seafood'],          'photo' => $p($U['seafood'])],
    ['slug' => 'kalamar',         'name' => 'Kalamar',            'category' => 'seafood',    'emoji' => '🦑', 'bg' => ['#fff2d8','#ffd084'], 'tags' => ['kalamar','squid','seafood'],           'photo' => $p($U['seafood'])],
    ['slug' => 'sushi-roll',      'name' => 'Suşi',               'category' => 'seafood',    'emoji' => '🍣', 'bg' => ['#e8f5e9','#a5d6a7'], 'tags' => ['suşi','sushi','japon'],                  'photo' => $p($U['sushi'])],

    /* ── SALATA & SEBZE ─────────────────────────────────────────── */
    ['slug' => 'salata',          'name' => 'Salata',             'category' => 'salad',      'emoji' => '🥗', 'bg' => ['#d5f3d6','#86d68a'], 'tags' => ['salata','salad','sebze','sağlıklı'],       'photo' => $p($U['salad'])],
    ['slug' => 'sezar-salata',    'name' => 'Sezar Salata',       'category' => 'salad',      'emoji' => '🥗', 'bg' => ['#d8f1d6','#7ed080'], 'tags' => ['sezar','caesar','tavuk salata'],          'photo' => $p($U['salad'])],
    ['slug' => 'avokado',         'name' => 'Avokado',            'category' => 'salad',      'emoji' => '🥑', 'bg' => ['#dcefc6','#9ec76e'], 'tags' => ['avokado','avocado','sağlıklı'],         'photo' => $p($U['salad'])],

    /* ── ÇORBA ───────────────────────────────────────────────────── */
    ['slug' => 'corba',           'name' => 'Çorba',              'category' => 'soup',       'emoji' => '🍲', 'bg' => ['#fff1cf','#ffc887'], 'tags' => ['çorba','soup','mercimek','ezogelin'],      'photo' => $p($U['soup'])],
    ['slug' => 'mercimek-corba',  'name' => 'Mercimek Çorbası',   'category' => 'soup',       'emoji' => '🥣', 'bg' => ['#ffe9bd','#ffc678'], 'tags' => ['mercimek','çorba','soup'],                'photo' => $p($U['soup'])],
    ['slug' => 'ramen',           'name' => 'Ramen / Noodle',     'category' => 'soup',       'emoji' => '🍜', 'bg' => ['#ffe2c4','#ffae72'], 'tags' => ['ramen','noodle','japonca','asya'],        'photo' => $p($U['ramen'])],
    ['slug' => 'pho',             'name' => 'Pho',                'category' => 'soup',       'emoji' => '🍜', 'bg' => ['#e8f5e9','#81c784'], 'tags' => ['pho','vietnam','noodle'],                'photo' => $p($U['ramen'])],

    /* ── MAKARNA ─────────────────────────────────────────────────── */
    ['slug' => 'makarna',         'name' => 'Makarna',            'category' => 'pasta',      'emoji' => '🍝', 'bg' => ['#ffe0bd','#ffae66'], 'tags' => ['makarna','pasta','spagetti'],            'photo' => $p($U['pasta'])],
    ['slug' => 'spaghetti',       'name' => 'Spaghetti',          'category' => 'pasta',      'emoji' => '🍝', 'bg' => ['#ffe4c2','#ffb371'], 'tags' => ['spaghetti','makarna','italyan'],         'photo' => $p($U['pasta'])],
    ['slug' => 'pirinc',          'name' => 'Pilav',              'category' => 'pasta',      'emoji' => '🍚', 'bg' => ['#fff5e0','#ffd498'], 'tags' => ['pilav','pirinç','rice'],                 'photo' => $p($U['pasta'])],

    /* ── YAN ÜRÜNLER ─────────────────────────────────────────────── */
    ['slug' => 'patates-kizart',  'name' => 'Patates Kızartması', 'category' => 'side',       'emoji' => '🍟', 'bg' => ['#fff1c9','#ffcd6a'], 'tags' => ['patates','fries','kızartma'],            'photo' => $p($U['fries'])],
    ['slug' => 'soğan-halkasi',   'name' => 'Soğan Halkası',      'category' => 'side',       'emoji' => '🧅', 'bg' => ['#ffe8c2','#ffba6c'], 'tags' => ['soğan','onion ring','kızartma'],          'photo' => $p($U['fries'])],
    ['slug' => 'simit',           'name' => 'Simit',              'category' => 'side',       'emoji' => '🥨', 'bg' => ['#ffe5b8','#f0a85a'], 'tags' => ['simit','pretzel','kahvaltı'],             'photo' => $p($U['bread'])],
    ['slug' => 'ekmek',           'name' => 'Ekmek',              'category' => 'side',       'emoji' => '🍞', 'bg' => ['#fff0d0','#ffcc7c'], 'tags' => ['ekmek','bread'],                       'photo' => $p($U['bread'])],
    ['slug' => 'nachos',          'name' => 'Nachos',             'category' => 'side',       'emoji' => '🧀', 'bg' => ['#fff8dc','#f5d76e'], 'tags' => ['nachos','aperatif'],                    'photo' => $p($U['taco'])],
    ['slug' => 'mozzarella',      'name' => 'Mozzarella Çubuk',   'category' => 'side',       'emoji' => '🧀', 'bg' => ['#fff9e6','#ffe08a'], 'tags' => ['mozzarella','kızartma'],                 'photo' => $p($U['fries'])],

    /* ── KAHVALTI ─────────────────────────────────────────────────── */
    ['slug' => 'menemen',         'name' => 'Menemen',            'category' => 'breakfast',  'emoji' => '🍳', 'bg' => ['#ffd9b3','#ff9e5c'], 'tags' => ['menemen','yumurta','kahvaltı'],          'photo' => $p($U['egg'])],
    ['slug' => 'omlet',           'name' => 'Omlet',              'category' => 'breakfast',  'emoji' => '🍳', 'bg' => ['#ffefcb','#ffce80'], 'tags' => ['omlet','yumurta','kahvaltı'],           'photo' => $p($U['egg'])],
    ['slug' => 'pankek',          'name' => 'Pankek',             'category' => 'breakfast',  'emoji' => '🥞', 'bg' => ['#ffeacb','#ffc385'], 'tags' => ['pankek','pancake','tatlı','kahvaltı'],   'photo' => $p($U['pancake'])],
    ['slug' => 'kahvalti-tabagi', 'name' => 'Kahvaltı Tabağı',    'category' => 'breakfast',  'emoji' => '🍳', 'bg' => ['#fff1cf','#ffd089'], 'tags' => ['kahvaltı','serpme','tabak'],            'photo' => $p($U['breakfast'])],
    ['slug' => 'waffle',          'name' => 'Waffle',             'category' => 'breakfast',  'emoji' => '🧇', 'bg' => ['#fff3d6','#ffd59a'], 'tags' => ['waffle','kahvaltı'],                   'photo' => $p($U['waffle'])],
    ['slug' => 'bagel',           'name' => 'Bagel',              'category' => 'breakfast',  'emoji' => '🥯', 'bg' => ['#fff2e0','#e8c49a'], 'tags' => ['bagel','sandviç'],                     'photo' => $p($U['croissant'])],

    /* ── TATLILAR ─────────────────────────────────────────────────── */
    ['slug' => 'pasta',           'name' => 'Pasta / Cake',       'category' => 'dessert',    'emoji' => '🍰', 'bg' => ['#ffd9e7','#ff8db8'], 'tags' => ['pasta','cake','tatlı'],                 'photo' => $p($U['cake'])],
    ['slug' => 'cookie',          'name' => 'Kurabiye',           'category' => 'dessert',    'emoji' => '🍪', 'bg' => ['#ffe5c1','#ffc173'], 'tags' => ['cookie','kurabiye','tatlı'],           'photo' => $p($U['cookie'])],
    ['slug' => 'baklava',         'name' => 'Baklava',            'category' => 'dessert',    'emoji' => '🍯', 'bg' => ['#fff0c7','#ffce6e'], 'tags' => ['baklava','tatlı','türk tatlısı'],        'photo' => $p($U['baklava'])],
    ['slug' => 'dondurma',        'name' => 'Dondurma',           'category' => 'dessert',    'emoji' => '🍦', 'bg' => ['#ffe5f0','#ff9ecf'], 'tags' => ['dondurma','ice cream','tatlı'],         'photo' => $p($U['icecream'])],
    ['slug' => 'cikolata',        'name' => 'Çikolata',           'category' => 'dessert',    'emoji' => '🍫', 'bg' => ['#e8d3bf','#a87c54'], 'tags' => ['çikolata','chocolate','tatlı'],          'photo' => $p($U['chocolate'])],
    ['slug' => 'kunefe',          'name' => 'Künefe',             'category' => 'dessert',    'emoji' => '🧆', 'bg' => ['#ffd49a','#ff9c4f'], 'tags' => ['künefe','tatlı','peynirli'],             'photo' => $p($U['baklava'])],
    ['slug' => 'donut',           'name' => 'Donut',              'category' => 'dessert',    'emoji' => '🍩', 'bg' => ['#ffe0ec','#ff96c4'], 'tags' => ['donut','tatlı','çörek'],                 'photo' => $p($U['donut'])],
    ['slug' => 'brownie',         'name' => 'Brownie',            'category' => 'dessert',    'emoji' => '🍫', 'bg' => ['#5d4037','#8d6e63'], 'tags' => ['brownie','çikolata'],                  'photo' => $p($U['chocolate'])],

    /* ── SICAK İÇECEKLER ─────────────────────────────────────────── */
    ['slug' => 'turk-kahvesi',    'name' => 'Türk Kahvesi',       'category' => 'drink-hot',  'emoji' => '☕', 'bg' => ['#dec6a8','#8b5a2b'], 'tags' => ['kahve','türk kahvesi','coffee'],         'photo' => $p($U['coffee'])],
    ['slug' => 'espresso',        'name' => 'Espresso',           'category' => 'drink-hot',  'emoji' => '☕', 'bg' => ['#d8bf99','#7a4f25'], 'tags' => ['espresso','kahve','coffee'],             'photo' => $p($U['coffee'])],
    ['slug' => 'latte',           'name' => 'Latte',              'category' => 'drink-hot',  'emoji' => '☕', 'bg' => ['#efd9b8','#b88347'], 'tags' => ['latte','kahve','sütlü'],                'photo' => $p($U['latte'])],
    ['slug' => 'cappuccino',      'name' => 'Cappuccino',         'category' => 'drink-hot',  'emoji' => '☕', 'bg' => ['#ecd2ad','#a87035'], 'tags' => ['cappuccino','kahve','sütlü'],           'photo' => $p($U['latte'])],
    ['slug' => 'filtre-kahve',    'name' => 'Filtre Kahve',       'category' => 'drink-hot',  'emoji' => '☕', 'bg' => ['#e8c89e','#9c5a2a'], 'tags' => ['filtre','kahve','coffee'],             'photo' => $p($U['coffee'])],
    ['slug' => 'cay',             'name' => 'Çay',                'category' => 'drink-hot',  'emoji' => '🍵', 'bg' => ['#ffd9b3','#d97e2c'], 'tags' => ['çay','tea','türk çayı'],                'photo' => $p($U['tea'])],
    ['slug' => 'sicak-cikolata',  'name' => 'Sıcak Çikolata',     'category' => 'drink-hot',  'emoji' => '☕', 'bg' => ['#e0c5a4','#7e4a25'], 'tags' => ['sıcak çikolata','chocolate'],          'photo' => $p($U['hotchoc'])],
    ['slug' => 'salep',           'name' => 'Salep',              'category' => 'drink-hot',  'emoji' => '🥛', 'bg' => ['#fff0e2','#e2b78f'], 'tags' => ['salep','sıcak içecek'],                 'photo' => $p($U['latte'])],

    /* ── SOĞUK İÇECEKLER ─────────────────────────────────────────── */
    ['slug' => 'limonata',        'name' => 'Limonata',           'category' => 'drink-cold', 'emoji' => '🍋', 'bg' => ['#fff5b5','#f0c842'], 'tags' => ['limonata','lemonade','soğuk'],          'photo' => $p($U['juice'])],
    ['slug' => 'soguk-kahve',     'name' => 'Soğuk Kahve / Latte','category' => 'drink-cold', 'emoji' => '🧋', 'bg' => ['#e8d0a8','#a87838'], 'tags' => ['soğuk kahve','iced coffee'],          'photo' => $p($U['latte'])],
    ['slug' => 'kola',            'name' => 'Kola / Cola',        'category' => 'drink-cold', 'emoji' => '🥤', 'bg' => ['#9b3030','#5a1212'], 'tags' => ['kola','cola','gazoz'],                'photo' => $p($U['cola'])],
    ['slug' => 'gazoz',           'name' => 'Gazoz',              'category' => 'drink-cold', 'emoji' => '🥤', 'bg' => ['#a3d8ff','#3692db'], 'tags' => ['gazoz','soda'],                       'photo' => $p($U['cola'])],
    ['slug' => 'meyve-suyu',      'name' => 'Meyve Suyu',         'category' => 'drink-cold', 'emoji' => '🧃', 'bg' => ['#ffd0a8','#ff8c3c'], 'tags' => ['meyve suyu','juice','portakal'],       'photo' => $p($U['juice'])],
    ['slug' => 'ayran',           'name' => 'Ayran',              'category' => 'drink-cold', 'emoji' => '🥛', 'bg' => ['#ffffff','#dde6f0'], 'tags' => ['ayran','yoğurt'],                   'photo' => $p($U['juice'])],
    ['slug' => 'milkshake',       'name' => 'Milkshake',          'category' => 'drink-cold', 'emoji' => '🥤', 'bg' => ['#ffd9eb','#ff7eb6'], 'tags' => ['milkshake','tatlı'],                'photo' => $p($U['icecream'])],
    ['slug' => 'su',              'name' => 'Su',                 'category' => 'drink-cold', 'emoji' => '💧', 'bg' => ['#d5ebff','#5fb0ff'], 'tags' => ['su','water'],                       'photo' => $p($U['water'])],
    ['slug' => 'maden-suyu',      'name' => 'Maden Suyu',         'category' => 'drink-cold', 'emoji' => '💧', 'bg' => ['#cfe6ff','#56a0e8'], 'tags' => ['maden','soda','soğuk'],              'photo' => $p($U['water'])],
    ['slug' => 'smoothie',        'name' => 'Smoothie',           'category' => 'drink-cold', 'emoji' => '🥤', 'bg' => ['#e8f5e9','#81c784'], 'tags' => ['smoothie','meyve'],                 'photo' => $p($U['fruit'])],
    ['slug' => 'ice-tea',         'name' => 'Soğuk Çay',          'category' => 'drink-cold', 'emoji' => '🧊', 'bg' => ['#e3f2fd','#64b5f6'], 'tags' => ['ice tea','soğuk çay'],                'photo' => $p($U['tea'])],

    /* ── ALKOLLÜ (görsel temsil — menüde yaş doğrulaması ayrı) ──── */
    ['slug' => 'bira',            'name' => 'Bira',               'category' => 'drink-cold', 'emoji' => '🍺', 'bg' => ['#fff9e6','#ffcc33'], 'tags' => ['bira','alkol'],                     'photo' => $p($U['beer'])],
    ['slug' => 'sarap',           'name' => 'Şarap',              'category' => 'drink-cold', 'emoji' => '🍷', 'bg' => ['#fce4ec','#ad1457'], 'tags' => ['şarap','alkol'],                    'photo' => $p($U['wine'])],
    ['slug' => 'kokteyl',         'name' => 'Kokteyl',            'category' => 'drink-cold', 'emoji' => '🍸', 'bg' => ['#e1f5fe','#0277bd'], 'tags' => ['kokteyl','alkol'],                  'photo' => $p($U['cocktail'])],

    /* ── DİĞER ─────────────────────────────────────────────────────── */
    ['slug' => 'meyve-tabagi',    'name' => 'Meyve Tabağı',       'category' => 'other',      'emoji' => '🍇', 'bg' => ['#ffd6e2','#ff7aa6'], 'tags' => ['meyve','fruit'],                    'photo' => $p($U['fruit'])],
    ['slug' => 'cips',            'name' => 'Cips',               'category' => 'side',       'emoji' => '🍿', 'bg' => ['#fff0c2','#ffc864'], 'tags' => ['cips','chips','aperatif'],            'photo' => $p($U['fries'])],

];
