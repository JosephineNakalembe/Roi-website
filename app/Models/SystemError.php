<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemError extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'exception',
        'message',
        'file',
        'line',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
