<?php

namespace App\Application\User\Jobs;

use App\Application\User\Events\UserOnlineEvent;
use App\Domain\User\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLastSeenInDatabaseJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;
    private User $user;
    private $lastSeen;

    public function __construct(User $user,$lastSeen)
    {
        $this->user = $user;
        $this->lastSeen = $lastSeen;
    }

    public function handle()
    {
        $this->user->last_seen = $this->lastSeen;
        $this->user->save();
        event(new UserOnlineEvent($this->user->fresh()));
    }
}
