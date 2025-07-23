<?php

namespace App\Application\ChatUnreadMessage\Repositories;

use App\Domain\ChatUnreadMessage\Repositories\StoreUnreadMessagesCountRepositoryInterface;
use App\Domain\ChatUnreadMessage\Entities\ChatUnreadMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreUnreadMessagesCountRepository implements StoreUnreadMessagesCountRepositoryInterface
{
    public function exec(int $recipientId,int $senderId,string $chatId): bool
    {
        try {
            return DB::transaction(function () use ($recipientId, $senderId, $chatId) {
                $affected = ChatUnreadMessage::where([
                    'chat_id' => $chatId,
                    'recipient_id' => $recipientId,
                    'sender_id' => $senderId
                ])->increment('unread_count');

                if ($affected === 0) {
                    ChatUnreadMessage::create([
                        'chat_id' => $chatId,
                        'recipient_id' => $recipientId,
                        'sender_id' => $senderId,
                        'unread_count' => 1
                    ]);
                }

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to increment unread messages count', [
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }
}
