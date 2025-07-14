<?php

namespace App\Domain\ChatMember\Entities;

use MongoDB\Laravel\Eloquent\Model;

class ChatMember extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chat_members';
    protected $fillable = [
        'user_id',
        'chat_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
