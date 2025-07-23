<?php

namespace App\Application\Chat\Events;

use App\Domain\User\Entities\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RenderChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private array $data;

    /**
     * Create a new event instance.
     */
    public function __construct(int $recipientId, string $message,string $data,User $sender,string $chatId,bool $isRead)
    {
        $this->data = [
            'recipient_id' => $recipientId,
            'last_message' => $message,
            'data' => $data,
            'sender' => $sender,
            'chat_id' => $chatId,
            'is_read' => $isRead,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat_render_by_user.'.$this->data['recipient_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.render';

    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
