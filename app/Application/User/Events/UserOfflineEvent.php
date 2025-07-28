<?php

namespace App\Application\User\Events;

use App\Domain\User\Entities\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOfflineEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private User $user;

    /**
     * Create a new event instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('last_seen.'.$this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.offline';

    }

    public function broadcastWith(): array
    {
        return [
            'last_seen' => $this->user->last_seen
        ];
    }
}
