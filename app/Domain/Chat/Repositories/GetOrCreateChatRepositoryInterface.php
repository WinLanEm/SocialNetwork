<?php

namespace App\Domain\Chat\Repositories;

use App\Application\Chat\DTOs\GetOrCreateChatDTO;

interface GetOrCreateChatRepositoryInterface
{
    public function exec(array $userIds,GetOrCreateChatDTO $DTO):array;
}
