<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            'Fashion & Apparel',
            'Accessories',
            'Home & Lifestyle',
            'Beauty & Personal Care',
            'Electronics & Gadgets',
            'Fitness & Sportswear',
            'Pet Supplies',
            'Seasonal Collections',
            'Kids & Baby',
            'Men',
            'Women',
            'Shoes',
            'Bags',
            'Jewelry',
            'Fragrances',
            'Toys & Games',
            'Office & Stationery',
            'Travel & Outdoor',
            'Party & Events',
            'Health & Wellness',
            'Kitchen & Dining',
            'Bedding & Bath',
            'Storage & Organization',
            'Lighting & Décor',
            'Garden & Outdoor Living',
            'Automotive Accessories',
            'Tech & Smart Devices',
            'Arts & Crafts',
            'Music & Instruments',
        ];

        // Insert each category, skip if it already exists (by name)
        foreach ($categories as $name) {
            $slug = Str::slug($name);
            DB::table('categories')->updateOrInsert(
                ['slug' => $slug],
                ['name' => $name, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $slugs = array_map(fn($name) => Str::slug($name), [
            'Fashion & Apparel', 'Accessories', 'Home & Lifestyle', 'Beauty & Personal Care',
            'Electronics & Gadgets', 'Fitness & Sportswear', 'Pet Supplies', 'Seasonal Collections',
            'Kids & Baby', 'Men', 'Women', 'Shoes', 'Bags', 'Jewelry',
            'Fragrances', 'Toys & Games', 'Office & Stationery', 'Travel & Outdoor', 'Party & Events',
            'Health & Wellness', 'Kitchen & Dining', 'Bedding & Bath', 'Storage & Organization',
            'Lighting & Décor', 'Garden & Outdoor Living', 'Automotive Accessories',
            'Tech & Smart Devices', 'Arts & Crafts', 'Music & Instruments',
        ]);
        DB::table('categories')->whereIn('slug', $slugs)->forceDelete();
    }
};