<?php

namespace App\Application\Message\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MakeMessagesIsReadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $senderId;
    private array $messageIds;
    private string $chatId;

    /**
     * Create a new event instance.
     */
    public function __construct(array $messagesIds,int $senderId,string $chatId)
    {
        $this->messageIds = $messagesIds;
        $this->senderId = $senderId;
        $this->chatId = $chatId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('read_messages_user.'.$this->senderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';

    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
            'message_ids' => $this->messageIds,
        ];
    }
}
