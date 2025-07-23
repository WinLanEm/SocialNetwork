<?php

namespace App\Domain\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;

interface GetChatByIdRepositoryInterface
{
    public function exec(string $chatId):?Chat;
}
