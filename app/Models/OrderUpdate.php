<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected static function booted()
    {
        static::created(function (self $update) {
            $user = $update->order?->user;

            if (!$user) {
                return;
            }

            $user->userNotifications()->create([
                'order_id' => $update->order_id,
                'title' => 'Order ' . $update->order->order_number . ' — ' . ucfirst($update->status),
                'body' => $update->note ?: 'Your order status is now ' . ucfirst($update->status) . '.',
                'link' => route('orders.show', $update->order_id),
            ]);
        });
    }
}
