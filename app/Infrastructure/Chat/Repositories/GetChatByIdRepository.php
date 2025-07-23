<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;

class GetChatByIdRepository implements GetChatByIdRepositoryInterface
{
    public function exec(string $chatId):?Chat
    {
        return Chat::find($chatId);
    }
}
