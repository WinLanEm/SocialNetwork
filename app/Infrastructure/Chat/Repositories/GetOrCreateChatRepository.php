<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Application\Message\Actions\MessageCryptoAction;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use App\Domain\Message\Repositories\PaginateChatMessagesRepositoryInterface;


class GetOrCreateChatRepository implements GetOrCreateChatRepositoryInterface
{
    public function __construct(
        readonly private PaginateChatMessagesRepositoryInterface $paginateChatMessagesRepository
    )
    {
    }

    public function exec(array $userIds, string $type): array
    {
        $uniqueIds = $this->normalizeUserIds($userIds);
        $chat = Chat::where(['participants' => $uniqueIds, 'type' => $type])->first();
        if(!$chat) {
            $chat = Chat::create([
                'participants' => $uniqueIds,
                'type' => $type,
                'secret_key' => $this->getSecretKey()
            ]);
            return [
                'chat_id' => $chat->id,
                'last_message' => $chat->last_message,
                'messages' => []
            ];
        }else{
            return [
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
