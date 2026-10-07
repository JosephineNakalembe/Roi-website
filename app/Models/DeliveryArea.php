<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'division',
        'name',
        'fee',
    ];

    protected $casts = [
        'fee' => 'decimal:0',
    ];
}
