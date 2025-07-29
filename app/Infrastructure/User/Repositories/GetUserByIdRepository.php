<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetUserByIdRepository implements GetUserByIdRepositoryInterface
{
    public function exec(int $userId): ?User
    {
        return User::find($userId);
    }
    public function getUsers(array $userIds): ?Collection
    {
        return User::whereIn('id', $userIds)->get();
    }
}
