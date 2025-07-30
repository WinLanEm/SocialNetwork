<?php

namespace App\Domain\User\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Application\User\Events\UserOnlineEvent;
use App\Application\User\Jobs\UpdateLastSeenInDatabaseJob;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redis;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'phone',
        'password',
        'bio',
        'date_of_birth',
        'avatar_url',
        'last_seen',
        'show_last_seen'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    protected static function newFactory()
    {
        return UserFactory::new();
    }
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }
}
