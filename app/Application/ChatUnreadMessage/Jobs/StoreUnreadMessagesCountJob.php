<?php

namespace App\Application\ChatUnreadMessage\Jobs;

use App\Domain\ChatUnreadMessage\Repositories\StoreUnreadMessagesCountRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreUnreadMessagesCountJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;
    private array $chatMembersIds;
    private string $chatId;
    private int $senderId;
    public function __construct(array $chatMembersIds,int $senderId,string $chatId)
    {
        $this->chatMembersIds = $chatMembersIds;
        $this->chatId = $chatId;
        $this->senderId = $senderId;
    }

    public function handle(StoreUnreadMessagesCountRepositoryInterface $storeUnreadMessagesCountRepository)
    {
        collect($this->chatMembersIds)->chunk(50)->each(function ($chunk) use ($storeUnreadMessagesCountRepository) {
            foreach ($chunk as $memberId) {
                $storeUnreadMessagesCountRepository->exec($memberId,$this->senderId, $this->chatId);
            }
        });
    }
}
