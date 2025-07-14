<?php

namespace App\Domain\User\Repositories;

use App\Application\User\DTOs\RegisterDTO;
use App\Domain\User\Entities\User;

interface CreateUserRepositoryInterface
{
    public function exec(RegisterDTO $DTO):User;
}
