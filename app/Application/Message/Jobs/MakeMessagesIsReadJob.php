<?php

namespace App\Application\Message\Jobs;


use App\Application\Chat\Events\MakeChatIsReadEvent;
use App\Application\Message\DTOs\MakeMessagesIsReadDTO;
use App\Application\Message\Events\MakeMessagesIsReadEvent;
use App\Domain\Message\Entities\Message;
use App\Domain\Message\Repositories\DecrementUnreadMessagesRepositoryInterface;
use App\Domain\Message\Repositories\MakeMessagesIsReadRepositoryInterface;
use App\Domain\Message\Repositories\ReadMessageByIdRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        $unreadMessagesIds = $this->deleteIsReadMessages();
        if(empty($unreadMessagesIds)){
            return;
        }
        $isReadRepository->exec($unreadMessagesIds);
        $hasUnreadMessages = $messagesRepository->exec($this->DTO->userId,$this->DTO->chatId,count($unreadMessagesIds));
        $message = $readMessageByIdRepository->exec($unreadMessagesIds[0]);
        event(new MakeMessagesIsReadEvent($unreadMessagesIds,$message->sender_id,$this->DTO->chatId));
        if(!$hasUnreadMessages){
            event(new MakeChatIsReadEvent($this->DTO->userId,$this->DTO->chatId));
        }
    }
    private function deleteIsReadMessages()
    {
        $messages = Message::whereIn('id',$this->DTO->messageIds)->get();
        $unreadMessages = [];
        foreach ($messages as $message){
            if($message->is_read === false){
                $unreadMessages[] = $message->id;
            }
        }
        return $unreadMessages;
    }
}
