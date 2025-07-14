<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use App\Domain\ChatMember\Entities\ChatMember;

class GetOrCreateChatRepository implements GetOrCreateChatRepositoryInterface
{
    public function exec(array $userIds, string $type): array
    {
        $normalizedIds = array_map(fn($id) => (string)$id, $userIds);
        $uniqueIds = array_unique($normalizedIds);
        $uniqueIds = array_values($uniqueIds);
        sort($uniqueIds);
        $chat = Chat::firstOrcreate(
            [
                'participants' => $uniqueIds,
                'type' => $type
            ]
        );
        return [
            'chat_id' => $chat->id,
            'last_message' => $chat->last_message
        ];
    }
}
