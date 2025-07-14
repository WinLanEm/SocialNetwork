<?php

namespace App\Infrastructure\User\Repositories;

use App\Application\User\DTOs\RegisterDTO;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\CreateUserRepositoryInterface;

class CreateUserRepository implements CreateUserRepositoryInterface
{
    public function exec(RegisterDTO $DTO): User
    {
        return User::create([
            'username' => $DTO->username,
            'password' => $DTO->password,
            'phone' => $DTO->phone,
        ]);
    }
}
