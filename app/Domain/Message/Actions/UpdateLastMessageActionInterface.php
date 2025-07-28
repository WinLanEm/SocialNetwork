<?php

namespace App\Domain\Message\Actions;

use App\Domain\Chat\Entities\Chat;

interface UpdateLastMessageActionInterface
{
    public function update(Chat $chat, string $encryptedMessage, int $senderId,string $decryptedMessage): void;
}
