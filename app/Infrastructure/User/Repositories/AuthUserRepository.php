<?php

namespace App\Infrastructure\User\Repositories;

use App\Application\User\DTOs\AuthorizationDTO;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\AuthUserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AuthUserRepository implements AuthUserRepositoryInterface
{
    public function exec(string $phone,string $password): bool
    {
        return Auth::attempt([
            'phone' => $phone,
            'password' => $password
            ],
            true);
    }
}
