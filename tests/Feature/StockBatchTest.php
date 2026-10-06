<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockBatchTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'price' => 2000,
            'cost_price' => 1000,
            'stock' => 0,
            'is_active' => true,
        ], $overrides));
    }

    public function test_stock_batches_are_split_by_payment_type(): void
    {
        $product = $this->makeProduct(['stock' => 8]);

        StockBatch::record($product, 5, StockBatch::TYPE_CREDIT, 1000);
        StockBatch::record($product, 3, StockBatch::TYPE_DEBIT, 2000);

        $split = $product->stockWorthSplit();

        $this->assertSame(5000.0, $split['credit']);
        $this->assertSame(6000.0, $split['debit']);
        $this->assertSame(11000.0, $split['total']);
        $this->assertSame('mixed', $product->stock_purchase_type);
    }

    public function test_selling_consumes_stock_oldest_first_and_leaves_credit(): void
    {
        $product = $this->makeProduct(['stock' => 8]);

        StockBatch::record($product, 5, StockBatch::TYPE_CREDIT, 1000);
        StockBatch::record($product, 3, StockBatch::TYPE_DEBIT, 2000);

        // A sale of 6 units: 5 credit units leave stock first (FIFO),
        // then 1 debit unit. Sold units are no longer part of stock —
        // reports count them as purchased on debit.
        $product->decrement('stock', 6);
        StockBatch::consume($product, 6);

        $product->refresh();

        $this->assertSame(2, (int) $product->stock);

        $split = $product->stockWorthSplit();
        $this->assertSame(0.0, $split['credit']);
        $this->assertSame(4000.0, $split['debit']);
        $this->assertSame('debit', $product->stock_purchase_type);
    }

    public function test_cancelling_an_order_restores_units_to_their_original_batches(): void
    {
        $product = $this->makeProduct(['stock' => 5]);

        StockBatch::record($product, 5, StockBatch::TYPE_CREDIT, 1000);

        $product->decrement('stock', 3);
        StockBatch::consume($product, 3);
        $product->refresh();

        // 2 credit units are still in stock (worth 2,000); the 3 sold units
        // have left stock and are counted as purchased on debit in reports.
        $this->assertSame(2000.0, $product->stockWorthSplit()['credit']);

        // Order cancelled — the 3 units come back as credit again
        $product->increment('stock', 3);
        StockBatch::restore($product, 3);
        $product->refresh();

        $this->assertSame(5, (int) $product->stock);

        $split = $product->stockWorthSplit();
        $this->assertSame(5000.0, $split['credit']);
        $this->assertSame('credit', $product->stock_purchase_type);
    }

    public function test_reconcile_records_units_added_through_the_edit_form(): void
    {
        $product = $this->makeProduct(['stock' => 5]);

        StockBatch::record($product, 5, StockBatch::TYPE_DEBIT, 1000);

        // Admin adds 4 units directly via the edit form (on credit)
        $product->update(['stock' => 9]);
        StockBatch::reconcile($product, StockBatch::TYPE_CREDIT);

        $split = $product->fresh()->stockWorthSplit();

        $this->assertSame(4000.0, $split['credit']);
        $this->assertSame(5000.0, $split['debit']);
        $this->assertSame('mixed', $product->fresh()->stock_purchase_type);
    }

    public function test_set_type_remarks_unsold_stock(): void
    {
        $product = $this->makeProduct(['stock' => 4]);

        StockBatch::record($product, 4, StockBatch::TYPE_DEBIT, 1000);

        StockBatch::setType($product, StockBatch::TYPE_CREDIT);

        $product->refresh();

        $this->assertSame('credit', $product->stock_purchase_type);
        $this->assertSame(4000.0, $product->stockWorthSplit()['credit']);
    }

    public function test_stock_without_batches_falls_back_to_debit(): void
    {
        $product = $this->makeProduct(['stock' => 6]);

        $this->assertSame('debit', $product->stock_purchase_type);

        $split = $product->stockWorthSplit();
        $this->assertSame(0.0, $split['credit']);
        $this->assertSame(6000.0, $split['debit']);
    }

    public function test_reports_page_shows_stock_worth_split(): void
    {
        $product = $this->makeProduct(['stock' => 5]);
        StockBatch::record($product, 5, StockBatch::TYPE_CREDIT, 1000);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Stock on Hand — Credit vs Debit');
        $response->assertSee('On Credit (owed to suppliers)');
        // 5 units * 1000 cost, all on credit
        $response->assertSee(number_format(5000, 0));
    }

    public function test_add_stock_endpoint_requires_and_records_payment_type(): void
    {
        $product = $this->makeProduct(['stock' => 2]);
        StockBatch::record($product, 2, StockBatch::TYPE_DEBIT, 1000);

        $admin = User::factory()->create(['role' => 'admin']);

        // Missing payment type -> validation error
        $this->actingAs($admin)
            ->post(route('admin.products.add-stock', $product), ['quantity' => 3])
            ->assertSessionHasErrors('payment_type');

        // Valid credit restock -> batch recorded
        $this->actingAs($admin)
            ->from(route('admin.products.index'))
            ->post(route('admin.products.add-stock', $product), [
                'quantity' => 3,
                'payment_type' => 'credit',
            ])
            ->assertSessionHasNoErrors();

        $product->refresh();

        $this->assertSame(5, (int) $product->stock);
        $this->assertSame('mixed', $product->stock_purchase_type);

        $split = $product->stockWorthSplit();
        $this->assertSame(3000.0, $split['credit']);
        $this->assertSame(2000.0, $split['debit']);
    }
}