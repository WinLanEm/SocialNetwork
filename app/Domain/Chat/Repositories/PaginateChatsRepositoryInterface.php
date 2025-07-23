<?php

namespace App\Domain\Chat\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaginateChatsRepositoryInterface
{
    public function exec(int $page,string $userId):Collection;
}
