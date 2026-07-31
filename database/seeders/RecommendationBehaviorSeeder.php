<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use App\Models\UserSearchLog;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds realistic synthetic products and behaviour (searches, views,
 * wishlists, carts) so the recommendation engine has data to learn trends
 * and user profiles from, even before the shop sees real traffic.
 */
class RecommendationBehaviorSeeder extends Seeder
{
    public function run(): void
    {
        if (UserSearchLog::exists()) {
            $this->command?->warn('Behaviour data already exists — skipping synthetic seeding.');

            return;
        }

        $products = Product::with('categories', 'category')->where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->seedSyntheticProducts();
            $products = Product::with('categories', 'category')->where('is_active', true)->get();
        }

        // Realistic search terms built from product names + common fashion keywords.
        $keywords = [
            'dress', 'gown', 'skirt', 'jeans', 't-shirt', 'shirt', 'shoes', 'sneakers',
            'heels', 'sandals', 'boots', 'handbag', 'bag', 'belt', 'coat', 'jacket',
            'jewelry', 'necklace', 'earrings', 'bracelet', 'watch', 'perfume', 'makeup',
        ];

        $terms = $keywords;
        foreach ($products as $product) {
            $tokens = preg_split('/\s+/', mb_strtolower((string) $product->name)) ?: [];
            $significant = array_values(array_filter($tokens, fn ($t) => strlen($t) > 2));
            $terms[] = implode(' ', array_slice($significant, 0, 2));
        }
        $terms = array_values(array_unique(array_filter($terms)));

        // A small subset becomes "trendy" by receiving the bulk of the signals.
        $trendy = $products->take(8);

        // Common misspellings so fuzzy search has realistic training data.
        $typos = [
            'dress' => 'dres',
            'shoes' => 'shoses',
            'sneakers' => 'sneaker',
            'skirt' => 'skrit',
            'handbag' => 'handbga',
            'watch' => 'wtach',
        ];

        $days = (int) config('recommendations.trend_days', 30);

        // Synthetic users with distinct tastes.
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $email = "synthetic-user-{$i}@example.test";
            $users[] = User::firstOrCreate(
                ['email' => $email],
                ['name' => "Synthetic User {$i}", 'password' => Hash::make('password')]
            );
        }

        foreach ($users as $user) {
            $interestTerms = array_rand(array_flip($terms), min(5, count($terms)));
            if (!is_array($interestTerms)) {
                $interestTerms = [$interestTerms];
            }

            for ($i = 0; $i < rand(3, 8); $i++) {
                $term = $interestTerms[array_rand($interestTerms)];
                if (isset($typos[$term]) && rand(1, 5) === 1) {
                    $term = $typos[$term];
                }

                UserSearchLog::create([
                    'user_id' => $user->id,
                    'guest_id' => null,
                    'query' => $term,
                    'results_count' => rand(0, 20),
                    'created_at' => now()->subDays(rand(0, $days))->subHours(rand(0, 23)),
                ]);
            }

            foreach ($trendy->random(rand(2, 5)) as $product) {
                ProductView::create([
                    'user_id' => $user->id,
                    'guest_id' => null,
                    'product_id' => $product->id,
                    'created_at' => now()->subDays(rand(0, $days))->subHours(rand(0, 23)),
                ]);
            }

            if (rand(0, 1) === 1) {
                foreach ($trendy->random(rand(1, 3)) as $product) {
                    WishlistItem::firstOrCreate([
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ]);
                }
            }

            if (rand(0, 1) === 1) {
                foreach ($trendy->random(rand(1, 2)) as $product) {
                    CartItem::firstOrCreate(
                        ['user_id' => $user->id, 'product_id' => $product->id, 'color' => null, 'size' => null],
                        ['quantity' => rand(1, 2)]
                    );
                }
            }
        }

        // Heavy anonymous search volume so "trending" is meaningful.
        foreach ($trendy as $product) {
            $firstWord = strtolower(explode(' ', trim((string) $product->name))[0]);
            for ($i = 0; $i < rand(8, 15); $i++) {
                UserSearchLog::create([
                    'user_id' => null,
                    'guest_id' => 'synthetic-guest-'.rand(1, 50),
                    'query' => $firstWord,
                    'results_count' => rand(5, 20),
                    'created_at' => now()->subDays(rand(0, $days))->subHours(rand(0, 23)),
                ]);
            }
        }

        // Sprinkle random product views from anonymous guests.
        foreach ($products->random(min(20, $products->count())) as $product) {
            ProductView::create([
                'user_id' => null,
                'guest_id' => 'synthetic-guest-'.rand(1, 50),
                'product_id' => $product->id,
                'created_at' => now()->subDays(rand(0, $days))->subHours(rand(0, 23)),
            ]);
        }

        // Clear any cached trend rankings so they rebuild from the new data.
        \Illuminate\Support\Facades\Cache::flush();

        $this->command?->info(sprintf(
            'Seeded synthetic behaviour: %d search logs, %d product views, %d users.',
            UserSearchLog::count(),
            ProductView::count(),
            count($users)
        ));
    }

    /**
     * Create a small catalogue of realistic sample products so the engine
     * has something to learn from on an empty database.
     */
    private function seedSyntheticProducts(): void
    {
        Product::where('product_id', 'like', 'SYN%')->delete();

        $categoriesByName = Category::all()->keyBy('name');

        $findCategoryId = function (string $name) use ($categoriesByName) {
            return $categoriesByName->get($name)?->id;
        };

        $catalog = [
            ['name' => 'Red A-Line Dress', 'category' => 'Women', 'price' => 65000, 'stock' => 14],
            ['name' => 'Navy Maxi Gown', 'category' => 'Women', 'price' => 95000, 'stock' => 8],
            ['name' => 'Classic White Sneakers', 'category' => 'Shoes', 'price' => 120000, 'stock' => 20],
            ['name' => 'Black Leather Handbag', 'category' => 'Bags', 'price' => 145000, 'stock' => 5],
            ['name' => 'Gold Statement Necklace', 'category' => 'Jewelry', 'price' => 38000, 'stock' => 30],
            ['name' => 'Denim Blue Jeans', 'category' => 'Men', 'price' => 55000, 'stock' => 12],
            ['name' => 'White Cotton T-Shirt', 'category' => 'Men', 'price' => 25000, 'stock' => 40],
            ['name' => 'Black Leather Belt', 'category' => 'Accessories', 'price' => 28000, 'stock' => 25],
            ['name' => 'Silver Hoop Earrings', 'category' => 'Jewelry', 'price' => 22000, 'stock' => 35],
            ['name' => 'Ladies Sports Watch', 'category' => 'Jewelry', 'price' => 88000, 'stock' => 9],
            ['name' => 'Pink Floral Skirt', 'category' => 'Women', 'price' => 42000, 'stock' => 0],
            ['name' => 'Black Heeled Sandals', 'category' => 'Shoes', 'price' => 74000, 'stock' => 7],
            ['name' => 'Brown Leather Boots', 'category' => 'Shoes', 'price' => 160000, 'stock' => 4],
            ['name' => "Men's Oxford Shirt", 'category' => 'Men', 'price' => 48000, 'stock' => 18],
            ['name' => "Women's Denim Jacket", 'category' => 'Women', 'price' => 78000, 'stock' => 6],
            ['name' => 'Floral Print Summer Dress', 'category' => 'Women', 'price' => 58000, 'stock' => 16],
            ['name' => 'Beige Trench Coat', 'category' => 'Women', 'price' => 135000, 'stock' => 3],
            ['name' => 'Rose Perfume', 'category' => 'Fragrances', 'price' => 62000, 'stock' => 11],
            ['name' => 'Velvet Clutch Bag', 'category' => 'Bags', 'price' => 69000, 'stock' => 2],
            ['name' => "Men's Casual Loafers", 'category' => 'Shoes', 'price' => 98000, 'stock' => 10],
            ['name' => 'Press-On Nails', 'category' => 'Beauty', 'price' => 15000, 'stock' => 50],
        ];

        foreach ($catalog as $index => $item) {
            $categoryId = $findCategoryId($item['category']);

            Product::create([
                'product_id' => 'SYN'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'category_id' => $categoryId,
                'description' => "Synthetic sample product: {$item['name']}.",
                'price' => $item['price'],
                'cost_price' => (int) round($item['price'] * 0.6),
                'stock' => $item['stock'],
                'is_active' => true,
                'non_returnable' => false,
            ]);
        }

        $this->command?->info('Created '.count($catalog).' synthetic products for recommendation training.');
    }
}
