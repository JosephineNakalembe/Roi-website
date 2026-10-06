<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0)->comment('Units added in this restock');
            $table->integer('quantity_remaining')->default(0)->comment('Unsold units still in stock from this batch');
            $table->decimal('unit_cost', 10, 2)->default(0)->comment('Cost price per unit when the batch was received');
            $table->string('payment_type')->default('debit')->comment('credit = not yet paid to supplier, debit = already paid');
            $table->timestamps();

            $table->index(['product_id', 'payment_type']);
        });

        // Backfill: stock that already exists is recorded as one debit batch
        // (assumed already paid for). The admin can re-mark it from the reports page.
        DB::table('products')->where('stock', '>', 0)->orderBy('id')->each(function ($product) {
            DB::table('stock_batches')->insert([
                'product_id' => $product->id,
                'quantity' => (int) $product->stock,
                'quantity_remaining' => (int) $product->stock,
                'unit_cost' => (float) ($product->cost_price ?? 0),
                'payment_type' => 'debit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};