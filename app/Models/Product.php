<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'stock',
        'size_guide',
        'size_guide_type',
        'colors',
        'sizes',
        'color_stock',
        'color_prices',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'colors' => 'array',
        'sizes' => 'array',
        'color_stock' => 'array',
        'color_prices' => 'array',
        'size_guide' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the price for a specific color, falling back to the base price.
     */
    public function priceForColor(?string $color = null): float
    {
        if ($color && is_array($this->color_prices) && isset($this->color_prices[$color]) && $this->color_prices[$color] !== null && $this->color_prices[$color] !== '') {
            return (float) $this->color_prices[$color];
        }

        return (float) $this->price;
    }
}


