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
        'discount_price',
        'cost_price',
        'supplier',
        'stock',
        'size_guide',
        'size_guide_type',
        'colors',
        'sizes',
        'color_stock',
        'color_prices',
        'is_active',
        'non_returnable',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
        'non_returnable' => 'boolean',
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

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    /**
     * How the stock currently on hand was obtained:
     *  - credit: not yet paid to the supplier
     *  - debit:  already paid for (also used for sold items)
     *  - mixed:  both credit and debit batches are in stock
     *  - null:   no stock on hand
     *
     * Falls back to 'debit' when stock exists but no ledger batches were
     * recorded (stock predates the credit/debit feature).
     */
    public function getStockPurchaseTypeAttribute(): ?string
    {
        if ((int) ($this->stock ?? 0) <= 0) {
            return null;
        }

        $active = $this->stockBatches
            ->filter(fn ($batch) => (int) $batch->quantity_remaining > 0);

        if ($active->isEmpty()) {
            return StockBatch::TYPE_DEBIT;
        }

        $hasCredit = $active->contains('payment_type', StockBatch::TYPE_CREDIT);
        $hasDebit = $active->contains('payment_type', StockBatch::TYPE_DEBIT);

        if ($hasCredit && $hasDebit) {
            return 'mixed';
        }

        return $hasCredit ? StockBatch::TYPE_CREDIT : StockBatch::TYPE_DEBIT;
    }

    /**
     * Value of the stock currently on hand, split by payment type.
     *
     * @return array{credit: float, debit: float, total: float}
     */
    public function stockWorthSplit(): array
    {
        $credit = 0.0;
        $debit = 0.0;

        foreach ($this->stockBatches as $batch) {
            $worth = (int) $batch->quantity_remaining * (float) $batch->unit_cost;

            if ($batch->payment_type === StockBatch::TYPE_CREDIT) {
                $credit += $worth;
            } else {
                $debit += $worth;
            }
        }

        // Fallback for stock without ledger batches: count it as debit
        // (assumed paid) at the current cost price.
        if (($credit + $debit) <= 0 && (int) $this->stock > 0) {
            $debit = (int) $this->stock * (float) ($this->cost_price ?: 0);
        }

        return [
            'credit' => $credit,
            'debit' => $debit,
            'total' => $credit + $debit,
        ];
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

    public function getVariantStock(?string $color, ?string $size): int
    {
        $colorStock = $this->color_stock ?? [];
        $key = $size ? "$color ($size)" : ($color ?: '');
        if (!$key) return $this->stock ?? 0;
        return (int) ($colorStock[$key] ?? 0);
    }
}


