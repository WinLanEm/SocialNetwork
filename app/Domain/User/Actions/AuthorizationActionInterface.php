<?php

namespace App\Domain\User\Actions;

use App\Application\User\DTOs\AuthorizationDTO;

interface AuthorizationActionInterface
{
    public function exec(AuthorizationDTO $DTO):bool;
}
