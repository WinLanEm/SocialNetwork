<?php

namespace App\Infrastructure\Message\Repositories;

use App\Domain\Message\Actions\MessageCryptoActionInterface;
use App\Domain\Message\Entities\Message;
use App\Domain\Message\Repositories\PaginateChatMessagesRepositoryInterface;

class PaginateChatMessagesRepository implements PaginateChatMessagesRepositoryInterface
{
    public function __construct(
        readonly private MessageCryptoActionInterface $messageCryptoAction,
    )
    {
    }

    private int $perPage = 20;
    public function exec(string $chatId,string $secretKey, ?int $page):array
    {
        $messages = Message::where('chat_id', $chatId)
            ->orderBy('updated_at','desc')
            ->paginate($this->perPage,['content','sender_id','updated_at','is_read','id'],'page',$page ?? 1);
        $messages->each(function (Message $message) use ($secretKey) {
            $message['content'] = $this->messageCryptoAction->decrypt($secretKey,$message['content']);
        });
        return $messages->toArray();
    }
}
