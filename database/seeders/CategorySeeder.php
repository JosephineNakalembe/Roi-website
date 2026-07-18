<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Men',
            'Women',
            'Kids\' Clothing',
            'Shoes & Footwear',
            'Bags & Accessories',
            'Jewelry & Watches',
            'Electronics',
            'Sports & Outdoors',
            'Beauty & Personal Care',
            'Home & Living',
            'Books & Stationery',
            'Toys & Games',
            'Health & Wellness',
            'Automotive',
            'Pet Supplies',
            'Food & Beverages',
            'Office Supplies',
            'Arts & Crafts',
            'Baby & Maternity',
            'Furniture',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category],
                ['slug' => Str::slug($category)]
            );
        }
    }
}
