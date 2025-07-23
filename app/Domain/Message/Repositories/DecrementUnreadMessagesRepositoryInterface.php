<?php

namespace App\Domain\Message\Repositories;

interface DecrementUnreadMessagesRepositoryInterface
{
    public function exec(int $userId, string $chatId, int $messagesCount):bool;
}
