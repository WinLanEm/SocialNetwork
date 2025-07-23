<?php

namespace App\Application\Message\Events;

use App\Domain\Message\Entities\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class FailStoreMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $senderId;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId)
    {
        $this->senderId = $senderId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('fail_store_message_user.'.$this->senderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.failed';

    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->senderId,
            'error' => 'message failed to create, try again'
        ];
    }
}
