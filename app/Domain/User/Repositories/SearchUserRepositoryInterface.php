<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use Illuminate\Database\Eloquent\Collection;

interface SearchUserRepositoryInterface
{
    public function exec(string $username):?Collection;
}
