<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

interface ReadUserRepositoryInterface
{
    public function exec(int $id):User;
}
