<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'admin_reply',
        'replies',
        'status',
        'seen_by_user',
    ];

    protected $casts = [
        'replies' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Append a reply to the conversation thread and return the updated list.
     *
     * @param  'user'|'admin'  $sender
     */
    public function addReply(string $sender, string $message): array
    {
        $replies = $this->replies ?? [];
        $replies[] = [
            'sender' => $sender,
            'message' => $message,
            'created_at' => now()->toDateTimeString(),
        ];

        return $replies;
    }
}
