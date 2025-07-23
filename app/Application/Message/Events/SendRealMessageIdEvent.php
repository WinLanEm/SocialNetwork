<?php

namespace App\Application\Message\Events;

use App\Domain\Message\Entities\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class SendRealMessageIdEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $messageId;
    private string $tempId;
    private int $senderId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $messageId, string $tempId,int $senderId)
    {
        $this->messageId = $messageId;
        $this->tempId = $tempId;
        $this->senderId = $senderId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('send_real_id.'.$this->senderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message_id.updated';

    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'temp_id' => $this->tempId,
        ];
    }
}
