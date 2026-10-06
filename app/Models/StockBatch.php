<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    use HasFactory;

    /** Stock acquired from the supplier but not yet paid for. */
    public const TYPE_CREDIT = 'credit';

    /** Stock already paid for up front. Sold items are always counted as debit. */
    public const TYPE_DEBIT = 'debit';

    protected $fillable = [
        'product_id',
        'quantity',
        'quantity_remaining',
        'unit_cost',
        'payment_type',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_remaining' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeCredit($query)
    {
        return $query->where('payment_type', self::TYPE_CREDIT);
    }

    public function scopeDebit($query)
    {
        return $query->where('payment_type', self::TYPE_DEBIT);
    }

    /**
     * Record a batch of stock entering inventory (restock / initial stock).
     * The caller is responsible for updating product.stock itself.
     */
    public static function record(Product $product, int $quantity, string $paymentType = self::TYPE_DEBIT, ?float $unitCost = null): self
    {
        if ($quantity <= 0) {
            abort(500, 'Cannot record a stock batch with a quantity of zero or less.');
        }

        return $product->stockBatches()->create([
            'quantity' => $quantity,
            'quantity_remaining' => $quantity,
            'unit_cost' => $unitCost ?? (float) ($product->cost_price ?? 0),
            'payment_type' => $paymentType === self::TYPE_CREDIT ? self::TYPE_CREDIT : self::TYPE_DEBIT,
        ]);
    }

    /**
     * Remove units from stock when an item is sold (oldest batches first).
     * Sold units leave the credit balance automatically — a sale means the
     * admin has purchased the item in order to deliver it, so it is counted
     * as purchased on debit in reports.
     */
    public static function consume(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $batches = $product->stockBatches()
            ->where('quantity_remaining', '>', 0)
            ->orderBy('id')
            ->get();

        foreach ($batches as $batch) {
            if ($quantity <= 0) {
                break;
            }

            $take = min((int) $batch->quantity_remaining, $quantity);
            $batch->quantity_remaining = (int) $batch->quantity_remaining - $take;
            $batch->save();
            $quantity -= $take;
        }

        // Any remainder means product.stock and the ledger had drifted apart;
        // product.stock has already been decremented by the caller, so we simply
        // stop here rather than creating a negative batch.
    }

    /**
     * Return cancelled units to stock, putting them back into the batches they
     * came from (so their original credit/debit type is preserved).
     */
    public static function restore(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        $batches = $product->stockBatches()->orderBy('id')->get();

        foreach ($batches as $batch) {
            if ($quantity <= 0) {
                break;
            }

            $capacity = (int) $batch->quantity - (int) $batch->quantity_remaining;
            if ($capacity <= 0) {
                continue;
            }

            $put = min($capacity, $quantity);
            $batch->quantity_remaining = (int) $batch->quantity_remaining + $put;
            $batch->save();
            $quantity -= $put;
        }

        if ($quantity > 0) {
            self::record($product, $quantity, self::TYPE_DEBIT, (float) ($product->cost_price ?? 0));
        }
    }

    /**
     * Align the ledger with product.stock after the admin edits stock directly.
     * Extra units are recorded as a new batch with the given payment type;
     * missing units are consumed oldest-first.
     */
    public static function reconcile(Product $product, string $paymentType = self::TYPE_DEBIT): void
    {
        $stock = max(0, (int) $product->stock);
        $tracked = (int) $product->stockBatches()->sum('quantity_remaining');

        if ($stock > $tracked) {
            self::record($product, $stock - $tracked, $paymentType, (float) ($product->cost_price ?? 0));
        } elseif ($stock < $tracked) {
            self::consume($product, $tracked - $stock);
        }
    }

    /**
     * Re-mark every unsold batch of a product as credit or debit
     * (used to correct stock that existed before this feature).
     */
    public static function setType(Product $product, string $paymentType): void
    {
        $paymentType = $paymentType === self::TYPE_CREDIT ? self::TYPE_CREDIT : self::TYPE_DEBIT;

        self::reconcile($product, $paymentType);

        $product->stockBatches()
            ->where('quantity_remaining', '>', 0)
            ->update(['payment_type' => $paymentType]);
    }
}