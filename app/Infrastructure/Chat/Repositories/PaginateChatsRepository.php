<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\PaginateChatsRepositoryInterface;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use Illuminate\Database\Eloquent\Collection;


class PaginateChatsRepository implements PaginateChatsRepositoryInterface
{
    private int $perPage = 20;
    public function __construct(
        readonly private MessageCryptoActionInterface $messageCryptoAction,
    )
    {
    }

    public function exec(int $page, string $userId): Collection
    {
        $chats = Chat::where('participants',$userId)
            ->orderBy('updated_at','desc')
            ->paginate($this->perPage,['last_message','updated_at','participants','secret_key','id'],'page',$page);
        $chats = $chats->filter(function (Chat $chat) {
            if (empty($chat['last_message'])) {
                $this->deleteUselessChats($chat);
                return false;
            }
            return true;
        })->map(function (Chat $chat) {
            $chat['last_message'] = $this->messageCryptoAction->decrypt(
                $chat->secret_key,
                $chat['last_message']
            );
            return $chat;
        });
        return $chats;
    }
    private function deleteUselessChats(Chat $chat): void
    {
        $chat->delete();
    }
}
