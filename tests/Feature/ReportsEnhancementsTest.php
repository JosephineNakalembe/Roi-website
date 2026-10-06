<?php

namespace Tests\Feature;

use App\Models\Expenditure;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportsEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Report Product',
            'slug' => 'report-product-' . uniqid(),
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 0,
            'is_active' => true,
        ], $overrides));
    }

    private function makeOrderWithItem(Product $product, int $qty, float $unitPrice, float $orderTotal): array
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => $unitPrice * $qty,
            'shipping' => 0,
            'total' => $orderTotal,
            'status' => 'delivered',
            'placed_at' => now(),
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $qty,
            'total_price' => $unitPrice * $qty,
        ]);

        return [$order, $item];
    }

    private function makeReturn(Order $order, OrderItem $item, string $status): OrderReturn
    {
        $return = OrderReturn::create([
            'return_number' => 'RET' . uniqid(),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'reason' => 'Item arrived damaged',
            'notes' => 'Customer reported damage on delivery.',
            'refund_number' => '0777000111',
            'refund_network' => 'MTN Mobile Money',
            'refund_name' => 'Test Buyer',
            'pickup_address' => 'Kampala',
            'pickup_contact' => '0777000111',
            'pickup_area' => 'Kampala',
            'status' => $status,
        ]);

        OrderReturnItem::create([
            'order_return_id' => $return->id,
            'order_item_id' => $item->id,
        ]);

        return $return;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_reports_show_daily_sales_expenditures_and_net_profit(): void
    {
        $product = $this->makeProduct(['price' => 1010, 'cost_price' => 400]);
        $this->makeOrderWithItem($product, 2, 1010, 2020);

        Expenditure::create([
            'name' => 'Packing materials',
            'amount' => 333,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Sales by Day');
        $response->assertSee(Carbon::parse(now()->toDateString())->format('D, d M Y'));
        $response->assertSee('Expenditures');
        $response->assertSee('Net Profit (after expenses)');
        // profit = 2,020 - 800 = 1,220; net = 1,220 - 333 = 887
        $response->assertSee(number_format(887, 0));
        $response->assertSee(number_format(333, 0));
    }

    public function test_refunded_returns_are_deducted_from_sales(): void
    {
        $product = $this->makeProduct();
        [$order, $item] = $this->makeOrderWithItem($product, 1, 4123, 9123);

        $this->makeReturn($order, $item, 'refunded');

        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertOk();
        // 9,123 - 4,123 refunded = 5,000 remaining as a sale
        $response->assertSee('UGX5,000');
        $response->assertSee('Refunded Returns (deducted)');
        $response->assertSee(number_format(4123, 0));
        // The refunded item no longer appears in the product sales breakdown
        $response->assertSee('No sales data yet.');
    }

    public function test_stock_csv_export_downloads(): void
    {
        $product = $this->makeProduct(['name' => 'CSV Export Item', 'stock' => 5]);
        StockBatch::record($product, 5, StockBatch::TYPE_CREDIT, 400);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.export', 'stock'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv');

        $content = $response->streamedContent();
        $this->assertStringContainsString('CSV Export Item', $content);
        $this->assertStringContainsString('On Credit', $content);
    }

    public function test_unknown_csv_export_type_returns_404(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.export', 'bogus'))
            ->assertNotFound();
    }

    public function test_low_stock_list_and_restock_history_render(): void
    {
        $low = $this->makeProduct(['name' => 'Low Stock Item', 'stock' => 2]);
        StockBatch::record($low, 2, StockBatch::TYPE_CREDIT, 400);

        $this->makeProduct(['name' => 'Fully Sold Item', 'stock' => 0]);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Reorder Soon');
        $response->assertSee('Low Stock Item');
        // Stock 0 products only appear via the low-stock list
        $response->assertSee('Fully Sold Item');
        $response->assertSee('Stock In — Restock History');
    }

    public function test_marking_a_return_refunded_restores_stock_and_ledger(): void
    {
        $product = $this->makeProduct(['name' => 'Return Restock Item', 'stock' => 0]);
        $batch = StockBatch::record($product, 1, StockBatch::TYPE_CREDIT, 400);
        StockBatch::consume($product, 1); // it was sold earlier

        $this->assertSame(0, (int) $product->fresh()->stock);
        $this->assertSame(0, (int) $batch->fresh()->quantity_remaining);

        [$order, $item] = $this->makeOrderWithItem($product, 1, 1000, 1000);
        $return = $this->makeReturn($order, $item, 'pending');

        $this->actingAs($this->admin())
            ->patch(route('admin.returns.update', $return), ['status' => 'refunded'])
            ->assertSessionHasNoErrors();

        $product->refresh();

        $this->assertSame(1, (int) $product->stock);
        $this->assertSame('credit', $product->stock_purchase_type);
        $this->assertSame(1, (int) $batch->fresh()->quantity_remaining);
        $this->assertSame(500.0 === 500.0 ? 400.0 : 0.0, (float) $product->stockWorthSplit()['credit']);
    }
}