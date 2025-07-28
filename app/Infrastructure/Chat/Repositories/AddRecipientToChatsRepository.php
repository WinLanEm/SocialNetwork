<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\AddRecipientToChatsRepositoryInterface;
use App\Domain\Chat\Repositories\ChatIsReadRepositoryInterface;
use App\Domain\User\Entities\User;
use Illuminate\Support\Collection;

class AddRecipientToChatsRepository implements AddRecipientToChatsRepositoryInterface
{
    public function __construct(
        readonly private ChatIsReadRepositoryInterface $chatIsReadRepository,
    )
    {
    }

    public function exec(Collection $userChats,int $currentUserId):array
    {
        $allRecipientIds = $userChats->flatmap(function (Chat $chat) use($currentUserId){
            return array_diff($chat->participants,[$currentUserId]);
        })
            ->unique()
            ->values()
            ->all();
        $recipientsData = User::whereIn('id', $allRecipientIds)
            ->get(['username','avatar_url','id','last_seen'])
            ->keyBy('id');
        $chatsIsRead = $this->chatIsReadRepository->exec($userChats->pluck('id')->toArray(), $currentUserId);
        return $userChats
            ->map(function (Chat $chat) use ($currentUserId, $recipientsData,$chatsIsRead) {
                $recipientId = array_values(array_diff($chat->participants, [$currentUserId]))[0] ?? null;
                return [
                    'chat_data' => [
                        'last_message' => $chat->last_message,
                        'updated_at' => $chat->updated_at,
                        'chat_id' => $chat->id,
                        'is_read' => $chatsIsRead[$chat->id] ?? true,
                    ],
                    'recipient' => $recipientId ? [
                        'id' => $recipientId,
                        'data' => $recipientsData[$recipientId] ?? null
                    ] : null
                ];
            })
            ->values()
            ->all();
    }
}
