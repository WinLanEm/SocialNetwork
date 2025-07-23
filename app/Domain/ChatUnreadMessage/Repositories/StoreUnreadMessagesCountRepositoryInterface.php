<?php

namespace App\Domain\ChatUnreadMessage\Repositories;

interface StoreUnreadMessagesCountRepositoryInterface
{
    public function exec(int $recipientId,int $senderId,string $chatId): bool;
}
