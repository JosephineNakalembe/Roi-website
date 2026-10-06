<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug(): void
    {
        $product = Product::create([
            'name' => 'Dbg',
            'slug' => 'dbg-' . uniqid(),
            'price' => 1000,
            'cost_price' => 400,
            'stock' => 0,
            'is_active' => true,
        ]);
        StockBatch::record($product, 1, StockBatch::TYPE_CREDIT, 400);
        StockBatch::consume($product, 1);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 1000,
            'shipping' => 0,
            'total' => 1000,
            'status' => 'delivered',
            'placed_at' => now(),
        ]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Dbg',
            'unit_price' => 1000,
            'quantity' => 1,
            'total_price' => 1000,
        ]);
        $return = OrderReturn::create([
            'return_number' => 'RET' . uniqid(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => 'r',
            'notes' => 'n',
            'refund_number' => '1',
            'refund_network' => 'MTN Mobile Money',
            'refund_name' => 'x',
            'pickup_address' => 'a',
            'pickup_contact' => 'c',
            'pickup_area' => 'p',
            'status' => 'pending',
        ]);
        OrderReturnItem::create([
            'order_return_id' => $return->id,
            'order_item_id' => $item->id,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)
            ->patch(route('admin.returns.update', $return), ['status' => 'refunded']);

        fwrite(STDERR, "\nSTATUS: " . $response->getStatusCode() . "\n");
        fwrite(STDERR, "CONTENT: " . substr(strip_tags($response->getContent()), 0, 800) . "\n");
        fwrite(STDERR, "RETURN ITEMS: " . $return->fresh()->items()->count() . "\n");
        fwrite(STDERR, "ORDER ITEM: " . json_encode($item->fresh()->only(['id', 'order_item_id', 'quantity'])) . "\n");
        fwrite(STDERR, "PRODUCT STOCK: " . $product->fresh()->stock . "\n");

        $this->assertTrue(true);
    }
}