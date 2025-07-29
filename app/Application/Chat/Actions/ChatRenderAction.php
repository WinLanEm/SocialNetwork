<?php

namespace App\Application\Chat\Actions;

use App\Application\Chat\Events\RenderChatEvent;
use App\Domain\Chat\Actions\ChatRenderActionInterface;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Message\Entities\Message;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;

class ChatRenderAction implements ChatRenderActionInterface
{
    public function __construct(
        readonly private GetUserByIdRepositoryInterface $userRepository
    ) {}
    public function renderForPrivateChat(array $members, Chat $chat, Message $message): void
    {
        $recipientId = $this->resolveRecipientId($members,$message->sender_id);
        $sender = $this->userRepository->exec($message->sender_id);
        $recipient = $this->userRepository->exec($recipientId);
        $this->dispatchRenderEvents($message,$chat,$sender,$recipient,$recipientId);
    }
    private function resolveRecipientId(array $members, int $senderId): int
    {
        return ((int)$members[0] !== $senderId) ? $members[0] : $members[1];
    }

    private function dispatchRenderEvents(Message $message,Chat $chat, User $sender, User $recipient, int $recipientId): void
    {
        event(new RenderChatEvent($recipientId, $message->content, $chat->updated_at->toISOString(), $sender, $chat->id, true));
        event(new RenderChatEvent($message->sender_id, $message->content, $chat->updated_at->toISOString(), $recipient, $chat->id, false));
    }

}
