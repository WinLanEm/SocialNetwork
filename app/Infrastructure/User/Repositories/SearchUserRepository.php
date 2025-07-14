<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\SearchUserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SearchUserRepository implements SearchUserRepositoryInterface
{
    public function exec(string $username): ?Collection
    {
        if (empty($username)) {
            return null;
        } else {
            return User::search($username)
                ->query(fn($query) => $query->select(['id','username','avatar_url','last_seen']))
                ->take(10)
                ->get();
        }
    }
}
