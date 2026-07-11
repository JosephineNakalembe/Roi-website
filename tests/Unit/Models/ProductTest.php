<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_array_attributes_are_cast_to_arrays(): void
    {
        $product = Product::factory()->create([
            'colors' => ['red', 'blue'],
            'sizes' => ['S', 'M', 'L'],
            'color_stock' => ['red' => 3, 'blue' => 5],
            'color_prices' => ['red' => '12.50'],
            'size_guide' => ['chest' => '40cm'],
        ])->fresh();

        $this->assertSame(['red', 'blue'], $product->colors);
        $this->assertSame(['S', 'M', 'L'], $product->sizes);
        $this->assertSame(['red' => 3, 'blue' => 5], $product->color_stock);
        $this->assertSame(['chest' => '40cm'], $product->size_guide);
    }

    public function test_is_active_is_cast_to_boolean(): void
    {
        $product = Product::factory()->create(['is_active' => 1])->fresh();

        $this->assertIsBool($product->is_active);
        $this->assertTrue($product->is_active);
    }

    public function test_price_is_cast_with_two_decimals(): void
    {
        $product = Product::factory()->create(['price' => 10])->fresh();

        $this->assertSame('10.00', $product->price);
    }

    public function test_price_for_color_returns_color_specific_price_when_set(): void
    {
        $product = new Product([
            'price' => 20.00,
            'color_prices' => ['gold' => '35.75'],
        ]);

        $this->assertSame(35.75, $product->priceForColor('gold'));
    }

    public function test_price_for_color_falls_back_to_base_price(): void
    {
        $product = new Product([
            'price' => 20.00,
            'color_prices' => ['gold' => '35.75'],
        ]);

        $this->assertSame(20.00, $product->priceForColor('silver'));
        $this->assertSame(20.00, $product->priceForColor(null));
        $this->assertSame(20.00, $product->priceForColor());
    }

    public function test_price_for_color_ignores_empty_or_null_color_prices(): void
    {
        $product = new Product([
            'price' => 20.00,
            'color_prices' => ['gold' => '', 'silver' => null],
        ]);

        $this->assertSame(20.00, $product->priceForColor('gold'));
        $this->assertSame(20.00, $product->priceForColor('silver'));
    }

    public function test_it_has_a_category_relationship(): void
    {
        $product = new Product;

        $this->assertInstanceOf(BelongsTo::class, $product->category());
        $this->assertInstanceOf(Category::class, $product->category()->getRelated());
    }

    public function test_it_has_a_categories_relationship(): void
    {
        $product = new Product;

        $this->assertInstanceOf(BelongsToMany::class, $product->categories());
        $this->assertInstanceOf(Category::class, $product->categories()->getRelated());
    }

    public function test_it_has_many_images(): void
    {
        $product = new Product;

        $this->assertInstanceOf(HasMany::class, $product->images());
        $this->assertInstanceOf(ProductImage::class, $product->images()->getRelated());
    }

    public function test_primary_image_is_a_constrained_has_one(): void
    {
        $product = Product::factory()->create();
        ProductImage::create(['product_id' => $product->id, 'path' => 'a.jpg', 'is_primary' => false]);
        $primary = ProductImage::create(['product_id' => $product->id, 'path' => 'b.jpg', 'is_primary' => true]);

        $this->assertInstanceOf(HasOne::class, $product->primaryImage());
        $this->assertTrue($product->primaryImage->is($primary));
    }
}
