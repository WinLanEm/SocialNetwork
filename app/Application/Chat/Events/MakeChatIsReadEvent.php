<?php

namespace App\Application\Chat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MakeChatIsReadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $recipientId;
    private string $chatId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $recipientId,string $chatId)
    {
        $this->recipientId = $recipientId;
        $this->chatId = $chatId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('make_chat_is_read_user.'.$this->recipientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.is_read';

    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
        ];
    }
}
