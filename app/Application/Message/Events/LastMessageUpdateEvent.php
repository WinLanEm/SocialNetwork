<?php

namespace App\Application\Message\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LastMessageUpdateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private array $data;

    /**
     * Create a new event instance.
     */
    public function __construct($content,$chatId,$userId)
    {
        $this->data = [
            'content' => $content,
            'chat_id' => $chatId,
            'user_id' => $userId,
            'updated_at' => time()
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('last_message.'.$this->data['user_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'last_message.updated';

    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
