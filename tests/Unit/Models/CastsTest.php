<?php

namespace Tests\Unit\Models;

use App\Models\CustomerMessage;
use App\Models\OrderItem;
use Tests\TestCase;

class CastsTest extends TestCase
{
    public function test_customer_message_replies_is_cast_to_array(): void
    {
        $message = new CustomerMessage([
            'replies' => [
                ['from' => 'user', 'body' => 'Hi'],
                ['from' => 'admin', 'body' => 'Hello'],
            ],
        ]);

        $this->assertIsArray($message->replies);
        $this->assertSame('Hello', $message->replies[1]['body']);
    }

    public function test_order_item_money_fields_are_cast_with_two_decimals(): void
    {
        $item = new OrderItem([
            'unit_price' => 12.5,
            'total_price' => 25,
        ]);

        $this->assertSame('12.50', $item->unit_price);
        $this->assertSame('25.00', $item->total_price);
    }
}
