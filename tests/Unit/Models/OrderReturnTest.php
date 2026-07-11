<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\OrderReturnUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReturnTest extends TestCase
{
    use RefreshDatabase;

    private function makeReturn(array $attributes = []): OrderReturn
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => 100,
            'shipping' => 0,
            'total' => 100,
        ]);

        return OrderReturn::create(array_merge([
            'return_number' => 'RET-'.uniqid(),
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => 'Wrong Size',
            'notes' => 'Too small',
            'refund_number' => '0700000000',
            'refund_network' => 'MTN',
            'refund_name' => 'Jane Doe',
            'pickup_address' => '123 Street',
            'pickup_contact' => '0700000000',
            'pickup_area' => 'Central',
            'status' => 'pending',
        ], $attributes));
    }

    public function test_status_scope_filters_by_status(): void
    {
        $this->makeReturn(['status' => 'pending']);
        $approved = $this->makeReturn(['status' => 'approved']);

        $results = OrderReturn::status('approved')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($approved));
    }

    public function test_belongs_to_order_and_user(): void
    {
        $return = new OrderReturn;

        $this->assertInstanceOf(BelongsTo::class, $return->order());
        $this->assertInstanceOf(Order::class, $return->order()->getRelated());

        $this->assertInstanceOf(BelongsTo::class, $return->user());
        $this->assertInstanceOf(User::class, $return->user()->getRelated());
    }

    public function test_has_many_items_and_status_updates(): void
    {
        $return = new OrderReturn;

        $this->assertInstanceOf(HasMany::class, $return->items());
        $this->assertInstanceOf(OrderReturnItem::class, $return->items()->getRelated());

        $this->assertInstanceOf(HasMany::class, $return->statusUpdates());
        $this->assertInstanceOf(OrderReturnUpdate::class, $return->statusUpdates()->getRelated());
    }

    public function test_order_items_is_a_belongs_to_many(): void
    {
        $return = new OrderReturn;

        $this->assertInstanceOf(BelongsToMany::class, $return->orderItems());
    }

    public function test_status_updates_are_ordered_latest_first(): void
    {
        $return = $this->makeReturn();
        $older = OrderReturnUpdate::create(['order_return_id' => $return->id, 'status' => 'pending', 'note' => 'a']);
        $newer = OrderReturnUpdate::create(['order_return_id' => $return->id, 'status' => 'approved', 'note' => 'b']);
        $older->created_at = now()->subDay();
        $older->save();

        $this->assertTrue($return->statusUpdates()->first()->is($newer));
    }
}
