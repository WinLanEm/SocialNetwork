<?php

namespace App\Infrastructure\Message\Repositories;

use App\Domain\ChatUnreadMessage\Entities\ChatUnreadMessage;
use App\Domain\Message\Repositories\DecrementUnreadMessagesRepositoryInterface;

class DecrementUnreadMessagesRepository implements DecrementUnreadMessagesRepositoryInterface
{
    public function exec(int $userId, string $chatId, int $messagesCount):bool
    {
        $unread = ChatUnreadMessage::where(['chat_id' => $chatId, 'recipient_id' => $userId])->first();
        if (!$unread) {
            return false;
        }
        $decrementAmount = min($messagesCount, $unread->unread_count);

        if ($decrementAmount > 0) {
            $unread->decrement('unread_count', $decrementAmount);
            if($unread->unread_count === 0){
                return false;
            }else{
                return true;
            }
        }
        return false;
    }
}
