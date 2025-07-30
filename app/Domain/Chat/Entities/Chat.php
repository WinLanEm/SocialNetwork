<?php

namespace App\Domain\Chat\Entities;

use App\Domain\ModelTraits\HandlesMongoArrays;

use Database\Factories\ChatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    /** @use HasFactory<\Database\Factories\ChatFactory> */

    use HandlesMongoArrays;
    protected $connection = 'mongodb';
    protected $collection = 'chats';
    protected $fillable = [
        'participants',
        'type',
        'last_message',
        'secret_key',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'type' => 'string',
        'last_message' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    protected $attributes = [
        'last_message' => ''
    ];
    public function setParticipantsAttribute($value)
    {
        $this->setArrayAttribute('participants', $value);
        $this->attributes['participants_hashed'] = md5(implode('|', $value));
    }
    protected static function newFactory()
    {
        return ChatFactory::new();
    }
}
