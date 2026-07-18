<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            "Women's Clothing" => ['name' => 'Women', 'slug' => 'women'],
            "Men's Clothing" => ['name' => 'Men', 'slug' => 'men'],
        ];

        foreach ($renames as $oldName => $new) {
            DB::table('categories')
                ->where('name', $oldName)
                ->update(['name' => $new['name'], 'slug' => $new['slug']]);
        }
    }

    public function down(): void
    {
        $renames = [
            'Women' => ['name' => "Women's Clothing", 'slug' => 'womens-clothing'],
            'Men' => ['name' => "Men's Clothing", 'slug' => 'mens-clothing'],
        ];

        foreach ($renames as $oldName => $new) {
            DB::table('categories')
                ->where('name', $oldName)
                ->update(['name' => $new['name'], 'slug' => $new['slug']]);
        }
    }
};
