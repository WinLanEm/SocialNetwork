<?php

namespace App\Domain\Message\Entities;

use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'messages';
    protected $fillable = [
        'content',
        'sender_id',
        'chat_id',
        'is_read',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'sender_id' => 'integer',
        'consumer_id' => 'integer',
        'chat_id' => 'string',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'is_read' => false
    ];
}
