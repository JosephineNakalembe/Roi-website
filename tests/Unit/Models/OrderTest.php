<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderUpdate;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'subtotal' => 100,
            'shipping' => 0,
            'total' => 100,
        ], $attributes));
    }

    public function test_order_number_is_generated_on_creation(): void
    {
        $order = $this->makeOrder();

        $this->assertSame('RS24001', $order->order_number);
    }

    public function test_order_number_increments_with_id(): void
    {
        $this->makeOrder();
        $second = $this->makeOrder();

        $this->assertSame('RS24002', $second->order_number);
    }

    public function test_provided_order_number_is_not_overwritten(): void
    {
        $order = $this->makeOrder(['order_number' => 'CUSTOM-1']);

        $this->assertSame('CUSTOM-1', $order->order_number);
    }

    public function test_money_fields_are_cast_with_two_decimals(): void
    {
        $order = $this->makeOrder(['subtotal' => 12, 'shipping' => 3, 'total' => 15])->fresh();

        $this->assertSame('12.00', $order->subtotal);
        $this->assertSame('3.00', $order->shipping);
        $this->assertSame('15.00', $order->total);
    }

    public function test_timestamp_fields_are_cast_to_datetime(): void
    {
        $order = $this->makeOrder(['placed_at' => now(), 'delivered_at' => now()])->fresh();

        $this->assertInstanceOf(Carbon::class, $order->placed_at);
        $this->assertInstanceOf(Carbon::class, $order->delivered_at);
    }

    public function test_belongs_to_relations(): void
    {
        $order = new Order;

        $this->assertInstanceOf(BelongsTo::class, $order->user());
        $this->assertInstanceOf(User::class, $order->user()->getRelated());

        $this->assertInstanceOf(BelongsTo::class, $order->address());
        $this->assertInstanceOf(Address::class, $order->address()->getRelated());

        $this->assertInstanceOf(BelongsTo::class, $order->paymentMethod());
        $this->assertInstanceOf(PaymentMethod::class, $order->paymentMethod()->getRelated());
    }

    public function test_has_many_relations(): void
    {
        $order = new Order;

        $this->assertInstanceOf(HasMany::class, $order->items());
        $this->assertInstanceOf(OrderItem::class, $order->items()->getRelated());

        $this->assertInstanceOf(HasMany::class, $order->updates());
        $this->assertInstanceOf(OrderUpdate::class, $order->updates()->getRelated());

        $this->assertInstanceOf(HasMany::class, $order->returns());
        $this->assertInstanceOf(OrderReturn::class, $order->returns()->getRelated());
    }

    public function test_updates_are_ordered_latest_first(): void
    {
        $order = $this->makeOrder();
        $older = OrderUpdate::create(['order_id' => $order->id, 'status' => 'pending', 'note' => 'a']);
        $newer = OrderUpdate::create(['order_id' => $order->id, 'status' => 'shipped', 'note' => 'b']);
        $older->created_at = now()->subDay();
        $older->save();

        $this->assertTrue($order->updates()->first()->is($newer));
    }
}
