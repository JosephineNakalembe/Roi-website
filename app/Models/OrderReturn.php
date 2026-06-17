<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'order_id',
        'user_id',
        'reason',
        'notes',
        'refund_number',
        'refund_network',
        'refund_name',
        'pickup_address',
        'pickup_contact',
        'pickup_area',
        'pickup_fee',
        'images',
        'status',
        'admin_notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class, 'order_return_id');
    }

    public function statusUpdates()
    {
        return $this->hasMany(OrderReturnUpdate::class, 'order_return_id')->latest();
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'order_return_items', 'order_return_id', 'order_item_id');
    }
}