<?php

namespace App\Domain\User\Repositories;

use App\Application\User\DTOs\AuthorizationDTO;

interface AuthUserRepositoryInterface
{
    public function exec(string $phone,string $password):bool;
}
