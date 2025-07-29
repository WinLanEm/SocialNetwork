<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Application\Chat\DTOs\GetOrCreateChatDTO;
use App\Application\Message\Actions\MessageCryptoAction;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\CacheChatsRepositoryInterface;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use App\Domain\Message\Repositories\PaginateChatMessagesRepositoryInterface;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;


class GetOrCreateChatRepository implements GetOrCreateChatRepositoryInterface
{
    public function __construct(
        readonly private PaginateChatMessagesRepositoryInterface $paginateChatMessagesRepository,
        readonly private GetUserByIdRepositoryInterface $getUserByIdRepository,
        readonly private CacheChatsRepositoryInterface $cacheChatsRepository
    )
    {
    }

    public function exec(array $userIds, GetOrCreateChatDTO $DTO): array
    {
        $uniqueIds = $this->normalizeUserIds($userIds);
        $chat = Chat::where(['participants' => $uniqueIds, 'type' => $DTO->type])->first();
        $chatUser = '';
        if(count($DTO->userIds) === 1){
            $chatUser = $this->getUserByIdRepository->exec($userIds[0]);
        }
        if(!$chat) {
            $chat = Chat::create([
                'participants' => $uniqueIds,
                'type' => $DTO->type,
                'secret_key' => $this->getSecretKey()
            ]);
            return [
                'last_seen' => $chatUser->last_seen,
                'chat_id' => $chat->id,
                'last_message' => $chat->last_message,
                'messages' => []
            ];
        }else{
            return [
                'last_seen' => $chatUser->last_seen,
                'chat_id' => $chat->id,
                'last_message' => $chat->last_message,
                'messages' => $this->paginateChatMessagesRepository->exec($chat->id,$chat->secret_key,1)
            ];
        }


    }
    private function getSecretKey(): string
    {
        return bin2hex(random_bytes(32));
    }
    private function normalizeUserIds(array $userIds): array
    {
        $normalizedIds = array_map(fn($id) => (string)$id, $userIds);
        $uniqueIds = array_unique($normalizedIds);
        $uniqueIds = array_values($uniqueIds);
        sort($uniqueIds);
        return $uniqueIds;
    }
}
