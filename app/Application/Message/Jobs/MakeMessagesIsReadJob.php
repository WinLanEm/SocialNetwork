<?php

namespace App\Application\Message\Jobs;


use App\Application\Chat\Events\MakeChatIsReadEvent;
use App\Application\Message\DTOs\MakeMessagesIsReadDTO;
use App\Application\Message\Events\MakeMessagesIsReadEvent;
use App\Domain\Message\Repositories\DecrementUnreadMessagesRepositoryInterface;
use App\Domain\Message\Repositories\MakeMessagesIsReadRepositoryInterface;
use App\Domain\Message\Repositories\ReadMessageByIdRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeMessagesIsReadJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    private MakeMessagesIsReadDTO $DTO;
    public function __construct(MakeMessagesIsReadDTO $dto)
    {
        $this->DTO = $dto;
    }
    public function handle(MakeMessagesIsReadRepositoryInterface $isReadRepository,
                            DecrementUnreadMessagesRepositoryInterface $messagesRepository,
                            ReadMessageByIdRepositoryInterface $readMessageByIdRepository):void
    {
        $isReadRepository->exec($this->DTO->messageIds);
        $hasUnreadMessages = $messagesRepository->exec($this->DTO->userId,$this->DTO->chatId,count($this->DTO->messageIds));
        $message = $readMessageByIdRepository->exec($this->DTO->messageIds[0]);
        $senderId = $message->sender_id;
        event(new MakeMessagesIsReadEvent($this->DTO->messageIds,$senderId,$this->DTO->chatId));
        if(!$hasUnreadMessages){
            event(new MakeChatIsReadEvent($this->DTO->userId,$this->DTO->chatId));
        }
    }
}
