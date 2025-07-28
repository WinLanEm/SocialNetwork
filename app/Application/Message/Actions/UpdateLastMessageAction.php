<?php

namespace App\Application\Message\Actions;

use App\Application\Message\Events\LastMessageUpdateEvent;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Message\Actions\UpdateLastMessageActionInterface;

class UpdateLastMessageAction implements UpdateLastMessageActionInterface
{
    public function update(Chat $chat, string $encryptedMessage, int $senderId, string $decryptedMessage): void
    {
        $chat->update(['last_message' => $encryptedMessage]);
        $members = array_diff($chat->participants, [$senderId]);

        foreach ($members as $member) {
            event(new LastMessageUpdateEvent($decryptedMessage, $chat->id, $member));
        }
    }

}
