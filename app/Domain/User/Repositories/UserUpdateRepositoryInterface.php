<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use App\Presentation\User\Requests\UserUpdateRequest;

interface UserUpdateRepositoryInterface
{
    public function exec(UserUpdateRequest $request): User;
}
