<?php

namespace App\Domain\User\Actions;

use App\Application\User\DTOs\RegisterDTO;

interface RegisterActionInterface
{
    public function exec(RegisterDTO $DTO):bool;
}
