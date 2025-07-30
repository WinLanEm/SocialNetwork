<?php

namespace App\Domain\Message\Entities;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    /** @use HasFactory<\Database\Factories\MessageFactory> */
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
    protected static function newFactory()
    {
        return MessageFactory::new();
    }
}
