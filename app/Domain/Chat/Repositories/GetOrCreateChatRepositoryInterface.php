<?php

namespace App\Domain\Chat\Repositories;

interface GetOrCreateChatRepositoryInterface
{
    public function exec(array $userIds,string $type):array;
}
