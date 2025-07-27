<?php

namespace App\Application\Message\Events;

use App\Domain\Message\Entities\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StoreMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private array $data;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->data = [
            'sender_id' => $message->sender_id,
            'content' => $message->content,
            'chat_id' => $message->chat_id,
            'is_read' => $message->is_read,
            'updated_at' => $message->updated_at,
            'id' => $message->id
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('message_in_chat.'.$this->data['chat_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.created';

    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
