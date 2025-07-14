<?php

namespace App\Domain\Chat\Entities;

use App\Domain\ModelTraits\HandlesMongoArrays;
use MongoDB\Laravel\Eloquent\Model;

class Chat extends Model
{
    use HandlesMongoArrays;
    protected $connection = 'mongodb';
    protected $collection = 'chats';
    protected $fillable = [
        'participants',
        'type',
        'last_message' => '',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'participants' => 'array',
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
}
