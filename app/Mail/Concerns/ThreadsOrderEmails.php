<?php

namespace App\Mail\Concerns;

use App\Models\Order;
use Illuminate\Mail\Mailables\Headers;

trait ThreadsOrderEmails
{
    public function headers(): Headers
    {
        $seq = $this->orderThreadSequence($this->order);
        $messageId = $this->orderThreadMessageId($this->order, $seq);

        if ($seq <= 1) {
            return new Headers(messageId: $messageId);
        }

        $references = [];
        for ($i = 1; $i < $seq; $i++) {
            $references[] = $this->orderThreadMessageId($this->order, $i);
        }

        return new Headers(
            messageId: $messageId,
            references: $references,
            text: ['In-Reply-To' => '<' . $references[count($references) - 1] . '>'],
        );
    }

    protected function orderThreadSequence(Order $order): int
    {
        return max(1, $order->updates()->count());
    }

    protected function orderThreadMessageId(Order $order, int $seq): string
    {
        return 'roi.' . $order->order_number . '.' . $seq . '@' . $this->orderThreadDomain();
    }

    protected function orderThreadDomain(): string
    {
        $host = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return str_contains($host, '.') ? $host : 'roistore.shop';
    }
}
