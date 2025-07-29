<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use Illuminate\Database\Eloquent\Collection;

interface GetUserByIdRepositoryInterface
{
    public function exec(int $userId):?User;
    public function getUsers(array $userIds): ?Collection;
}
