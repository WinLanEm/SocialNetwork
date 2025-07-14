<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;

class GetChatByIdRepository implements GetChatByIdRepositoryInterface
{
    public function exec(int $chatId):?Chat
    {
        return Chat::first($chatId);
    }
}
