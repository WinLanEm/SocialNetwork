<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\AddRecipientToChatsRepositoryInterface;
use App\Domain\Chat\Repositories\CacheChatsRepositoryInterface;
use App\Domain\Chat\Repositories\ChatIsReadRepositoryInterface;
use App\Domain\User\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AddRecipientToChatsRepository implements AddRecipientToChatsRepositoryInterface
{
    public function __construct(
        readonly private ChatIsReadRepositoryInterface $chatIsReadRepository,
        readonly private CacheChatsRepositoryInterface $cacheChatsRepository,
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
        $recipientsData = $this->cacheChatsRepository->getRecipientsDataForPrivate($allRecipientIds);
        if(empty($recipientsData)){
            $recipientsData = User::whereIn('id', $allRecipientIds)
                ->get(['username','avatar_url','id'])
                ->keyBy('id')
                ->map(function ($user){
                    return $user->toArray();
                })
                ->toArray();
            $this->cacheChatsRepository->putRecipientsDataForPrivate($allRecipientIds,$recipientsData);
        }
        $lastSeens = User::whereIn('id', $allRecipientIds)
            ->get(['id', 'last_seen'])
            ->keyBy('id');
        $chatsIsRead = $this->chatIsReadRepository->exec($userChats->pluck('id')->toArray(), $currentUserId);
        return $userChats
            ->map(function (Chat $chat) use ($currentUserId, $recipientsData,$chatsIsRead,$lastSeens) {
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
                        'data' => array_merge(
                            $recipientsData[$recipientId] ?? [],
                            ['last_seen' => $lastSeens[$recipientId]->last_seen ?? null]
                        )
                    ] : null
                ];
            })
            ->values()
            ->all();
    }
}
