<?php

namespace App\Domain\ChatUnreadMessage\Entities;

use Illuminate\Database\Eloquent\Model;

class ChatUnreadMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'recipient_id',
        'sender_id',
        'unread_count',
    ];
}
