<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Repositories\ChatIsReadRepositoryInterface;
use App\Domain\ChatUnreadMessage\Entities\ChatUnreadMessage;


class ChatIsReadRepository implements ChatIsReadRepositoryInterface
{
    public function exec(array $chatIds, int $userId): array
    {
        return ChatUnreadMessage::query()
            ->where('recipient_id', $userId)
            ->whereIn('chat_id', $chatIds)
            ->get(['chat_id','unread_count'])
            ->keyBy('chat_id')
            ->map(fn ($item) => $item->unread_count === 0)
            ->toArray();
    }

}
