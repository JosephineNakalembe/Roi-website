<?php

namespace Tests\Unit;

use App\Models\CustomerMessage;
use PHPUnit\Framework\TestCase;

class CustomerMessageReplyTest extends TestCase
{
    public function test_add_reply_appends_to_existing_thread(): void
    {
        $message = new CustomerMessage;
        $message->replies = [
            ['sender' => 'user', 'message' => 'first', 'created_at' => '2020-01-01 00:00:00'],
        ];

        $replies = $message->addReply('admin', 'second');

        $this->assertCount(2, $replies);
        $this->assertSame('admin', $replies[1]['sender']);
        $this->assertSame('second', $replies[1]['message']);
        $this->assertArrayHasKey('created_at', $replies[1]);
    }

    public function test_add_reply_starts_a_thread_when_none_exists(): void
    {
        $replies = (new CustomerMessage)->addReply('user', 'hello');

        $this->assertCount(1, $replies);
        $this->assertSame('user', $replies[0]['sender']);
        $this->assertSame('hello', $replies[0]['message']);
    }
}
