<?php

/**
 * Synthetic-data smoke test for the gender-aware recommendation shuffle.
 *
 * Runs entirely in-memory (SQLite) — no MySQL needed. It seeds a fake
 * catalogue plus synthetic global behaviour (views / searches), then
 * exercises the real RecommendationService to confirm:
 *   1. Cold-start (no gender) shuffle surfaces core trendy verticals.
 *   2. female/male trending biases toward that gender's global profile.
 *   3. orderProducts re-ranks the shop page per gender.
 *   4. Browsing history (personalization) can override gender defaults.
 *
 * Run with:  php tests/synthetic_recommendations_test.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\UserSearchLog;
use App\Models\WishlistItem;
use App\Services\RecommendationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// ---------------------------------------------------------------- helpers

$pass = 0;
$fail = 0;

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    $status = $ok ? 'PASS' : 'FAIL';
    echo "  [$status] $label".($detail !== '' ? " — $detail" : '').PHP_EOL;
    $ok ? $pass++ : $fail++;
}

function countWhere(array $ids, array $in): int
{
    return count(array_intersect($ids, $in));
}

// ------------------------------------------------------------ environment

// Point the whole app at a fresh in-memory SQLite database and keep the
// cache out of the database so it cannot interfere with the scenario data.
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);
config(['cache.default' => 'array']);

foreach (['rec_trending_8_all', 'rec_trending_8_female', 'rec_trending_8_male', 'rec_trending_10_all', 'rec_trending_10_female', 'rec_trending_10_male'] as $key) {
    Cache::forget($key);
}

// Minimal schema mirroring the real tables the engine touches.
Schema::create('categories', function ($t) {
    $t->id();
    $t->string('name')->unique();
    $t->string('slug')->unique();
    $t->timestamps();
});

Schema::create('products', function ($t) {
    $t->id();
    $t->unsignedBigInteger('category_id')->nullable();
    $t->string('product_id')->nullable();
    $t->string('name');
    $t->string('slug')->unique();
    $t->text('description')->nullable();
    $t->decimal('price', 10, 2)->default(0);
    $t->integer('stock')->default(10);
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('category_product', function ($t) {
    $t->id();
    $t->unsignedBigInteger('category_id');
    $t->unsignedBigInteger('product_id');
});

Schema::create('product_images', function ($t) {
    $t->id();
    $t->unsignedBigInteger('product_id');
    $t->string('path');
    $t->boolean('is_primary')->default(false);
    $t->timestamps();
});

Schema::create('users', function ($t) {
    $t->id();
    $t->string('name');
    $t->string('email')->unique();
    $t->string('gender')->nullable();
    $t->timestamps();
});

Schema::create('cart_items', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->unsignedBigInteger('product_id');
    $t->integer('quantity')->default(1);
    $t->timestamps();
});

Schema::create('wishlist_items', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->unsignedBigInteger('product_id');
    $t->timestamps();
});

Schema::create('orders', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id')->nullable();
    $t->string('status')->default('pending');
    $t->timestamps();
});

Schema::create('order_items', function ($t) {
    $t->id();
    $t->unsignedBigInteger('order_id');
    $t->unsignedBigInteger('product_id');
    $t->integer('quantity')->default(1);
    $t->timestamps();
});

Schema::create('product_views', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id')->nullable();
    $t->string('guest_id')->nullable();
    $t->unsignedBigInteger('product_id');
    $t->timestamps();
});

Schema::create('user_search_logs', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id')->nullable();
    $t->string('guest_id')->nullable();
    $t->string('query');
    $t->integer('results_count')->default(0);
    $t->timestamps();
});

// ------------------------------------------------------------- seed data

$cat = function (string $name, string $slug) {
    return Category::create(['name' => $name, 'slug' => $slug]);
};

$beauty = $cat('Beauty', 'beauty');
$women = $cat('Women', 'women');
$men = $cat('Men', 'men');
$fashion = $cat('Fashion & Apparel', 'fashion-apparel');
$electronics = $cat('Electronics & Gadgets', 'electronics-gadgets');
$fitness = $cat('Fitness & Sportswear', 'fitness-sportswear');
$home = $cat('Home & Lifestyle', 'home-lifestyle');
$wellness = $cat('Health & Wellness', 'health-wellness');
$bags = $cat('Bags', 'bags');
$jewelry = $cat('Jewelry', 'jewelry');

$product = function (string $name, Category $primary, array $extra = []) use ($fashion, $men, $women) {
    $p = Product::create([
        'category_id' => $primary->id,
        'product_id' => 'SKU-'.strtoupper(str_replace([' ', '-'], '', $name)),
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)),
        'description' => "Synthetic $name for testing",
        'price' => 25000,
        'stock' => 10,
        'is_active' => true,
    ]);
    $p->categories()->attach(array_merge([$primary->id], array_map(fn ($c) => $c->id, $extra)));

    return $p;
};

$gown = $product('Velvet Evening Gown', $women, [$fashion]);
$mascara = $product('Everyday Mascara & Eyeliner Set', $beauty);
$nailKit = $product('Silk Press-on Nail Kit', $beauty);
$handbag = $product('Classic Leather Handbag', $women, [$bags]);
$bracelet = $product('Diamond Tennis Bracelet', $women, [$jewelry]);
$perfume = $product('Rose Perfume Eau de Parfum', $beauty);
$headphones = $product('Wireless Noise-Cancel Headphones', $electronics);
$fitnessWatch = $product('Smart Fitness Watch', $electronics, [$fitness]);
$beardKit = $product('Beard Grooming Kit', $men, [$wellness]);
$wallet = $product("Men's Leather Wallet", $men);
$diveWatch = $product('Steel Waterproof Dive Watch', $electronics, [$men]);
$sneakers = $product('Athletic Running Sneakers', $fitness, [$men]);
$blouse = $product('Linen Casual Blouse', $women, [$fashion]);
$tshirt = $product('Cotton Crew T-Shirt', $fashion, [$men]);
$vase = $product('Ceramic Flower Vase', $home);
$serum = $product('Anti-Aging Face Serum', $beauty, [$wellness]);

$allProducts = Product::orderBy('id')->get();

// Synthetic "global" behaviour within the 30-day trend window.
$recent = fn ($h) => Carbon::now()->subHours($h);

$view = function (Product $p, int $count, int $hoursAgo) use ($recent) {
    for ($i = 0; $i < $count; $i++) {
        ProductView::create([
            'product_id' => $p->id,
            'user_id' => null,
            'guest_id' => 'global-'.mt_rand(1000, 9999),
            'created_at' => $recent($hoursAgo + $i),
        ]);
    }
};

$search = function (string $query, int $count, int $hoursAgo) use ($recent) {
    for ($i = 0; $i < $count; $i++) {
        UserSearchLog::create([
            'query' => $query,
            'user_id' => null,
            'guest_id' => 'global-'.mt_rand(1000, 9999),
            'results_count' => 5,
            'created_at' => $recent($hoursAgo + $i),
        ]);
    }
};

$view($headphones, 6, 2);
$view($fitnessWatch, 4, 3);
$view($nailKit, 5, 2);
$search('perfume', 4, 1);
$search('headphones', 3, 2);
$search('wallet', 2, 4);

$femaleIds = [$gown->id, $mascara->id, $nailKit->id, $handbag->id, $bracelet->id, $perfume->id, $blouse->id, $serum->id];
$maleIds = [$headphones->id, $fitnessWatch->id, $beardKit->id, $wallet->id, $diveWatch->id, $sneakers->id, $tshirt->id];

$svc = new RecommendationService();

// ------------------------------------------------------- scenario 1: cold start

echo "\n== 1. Cold-start shuffle (anonymous, no gender) ==\n";
$cold = $svc->trending(8)->pluck('id')->all();
$coreVerticalIds = [$gown->id, $mascara->id, $nailKit->id, $handbag->id, $bracelet->id, $perfume->id, $blouse->id, $serum->id, $beardKit->id, $wallet->id, $sneakers->id, $tshirt->id];
echo '   top8: '.implode(', ', $svc->trending(8)->pluck('name')->all()).PHP_EOL;
check('core verticals (beauty/fashion/personal-care) dominate the cold shuffle', countWhere($cold, $coreVerticalIds) >= 5, 'top8 contains '.countWhere($cold, $coreVerticalIds).' core-vertical items');
check('trending item (headphones) still surfaces', in_array($headphones->id, $cold, true));

// ------------------------------------------------------- scenario 2: gender trending

echo "\n== 2. Gender-aware trending ==\n";
$femaleTop = $svc->trending(8, 'female')->pluck('id')->all();
echo '   female top8: '.implode(', ', $svc->trending(8, 'female')->pluck('name')->all()).PHP_EOL;
$maleTop = $svc->trending(8, 'male')->pluck('id')->all();
echo '   male   top8: '.implode(', ', $svc->trending(8, 'male')->pluck('name')->all()).PHP_EOL;

$fF = countWhere($femaleTop, $femaleIds);
$fM = countWhere($femaleTop, $maleIds);
$mF = countWhere($maleTop, $femaleIds);
$mM = countWhere($maleTop, $maleIds);
check('female trending favors female-leaning items', $fF > $fM, "female: female=$fF male=$fM");
check('male trending favors male-leaning items', $mM > $mF, "male: male=$mM female=$mF");

// ------------------------------------------------------- scenario 3: orderProducts page shuffle

echo "\n== 3. Shop-page shuffle (orderProducts) per gender ==\n";

$femalePageCounts = [];
$malePageCounts = [];
for ($i = 0; $i < 6; $i++) {
    $femalePage = $svc->orderProducts($allProducts, null, 'guest-f-'.$i, 'female')->take(8)->pluck('id')->all();
    $malePage = $svc->orderProducts($allProducts, null, 'guest-m-'.$i, 'male')->take(8)->pluck('id')->all();
    $femalePageCounts[] = countWhere($femalePage, $femaleIds) - countWhere($femalePage, $maleIds);
    $malePageCounts[] = countWhere($malePage, $maleIds) - countWhere($malePage, $femaleIds);
}
$femaleWin = count(array_filter($femalePageCounts, fn ($v) => $v > 0));
$maleWin = count(array_filter($malePageCounts, fn ($v) => $v > 0));
echo '   female page: female-leaning margin per run = '.implode(', ', $femalePageCounts).PHP_EOL;
echo '   male   page: male-leaning margin per run   = '.implode(', ', $malePageCounts).PHP_EOL;
check('female shop page surfaces female items over male items (5/6 runs)', $femaleWin >= 5, "won $femaleWin/6");
check('male shop page surfaces male items over female items (5/6 runs)', $maleWin >= 5, "won $maleWin/6");

// ------------------------------------------------------- scenario 4: personalization overrides gender default

echo "\n== 4. Personalization can override gender defaults ==\n";
$browsingUser = \App\Models\User::create([
    'name' => 'Tech-Savvy Lady',
    'email' => 'techy@example.com',
    'gender' => 'female',
]);

for ($i = 0; $i < 2; $i++) {
    UserSearchLog::create([
        'user_id' => $browsingUser->id,
        'query' => 'wireless headphones',
        'results_count' => 3,
        'created_at' => $recent(1),
    ]);
}
ProductView::create(['user_id' => $browsingUser->id, 'product_id' => $headphones->id, 'created_at' => $recent(1)]);

$herPage = $svc->orderProducts($allProducts, $browsingUser->id, null, 'female')->take(6)->pluck('id')->all();
echo '   her top6: '.implode(', ', $svc->orderProducts($allProducts, $browsingUser->id, null, 'female')->take(6)->pluck('name')->all()).PHP_EOL;
check('electronics item she browses ranks top despite female default', $herPage[0] === $headphones->id, 'first = '.($herPage[0] === $headphones->id ? 'headphones' : $allProducts->firstWhere('id', $herPage[0])->name));

// ---------------------------------------------------------------- summary

echo PHP_EOL;
echo "RESULTS: $pass passed, $fail failed".PHP_EOL;
exit($fail > 0 ? 1 : 0);
