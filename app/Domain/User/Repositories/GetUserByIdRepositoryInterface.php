<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

interface GetUserByIdRepositoryInterface
{
    public function exec(int $userId):?User;
}
