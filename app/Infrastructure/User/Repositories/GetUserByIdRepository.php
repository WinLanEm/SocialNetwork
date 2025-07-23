<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;

class GetUserByIdRepository implements GetUserByIdRepositoryInterface
{
    public function exec(int $userId): ?User
    {
        return User::find($userId);
    }
}
