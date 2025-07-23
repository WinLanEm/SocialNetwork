<?php

namespace App\Application\Message\Jobs;

use App\Application\Chat\Events\RenderChatEvent;
use App\Application\ChatUnreadMessage\Jobs\StoreUnreadMessagesCountJob;
use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Events\FailStoreMessageEvent;
use App\Application\Message\Events\LastMessageUpdateEvent;
use App\Application\Message\Events\SendRealMessageIdEvent;
use App\Application\Message\Events\StoreMessageEvent;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use App\Domain\Message\Entities\Message;
use App\Domain\User\Repositories\GetUserByIdRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StoreMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;
    private StoreMessageDTO $dto;
    private int $senderId;

    public function __construct(StoreMessageDTO $DTO,int $senderId)
    {
        $this->dto = $DTO;
        $this->senderId = $senderId;
    }

    public function handle(GetChatByIdRepositoryInterface $getChatByIdRepository,
                           GetUserByIdRepositoryInterface $getUserByIdRepository,
                           MessageCryptoActionInterface $messageCryptoAction): void
    {
        $chat = $getChatByIdRepository->exec($this->dto->chatId);
        $encryptedMessageContent = $messageCryptoAction->encrypt($chat->secret_key, $this->dto->content);
        try {
            $message = Message::create([
                'sender_id' => $this->senderId,
                'content' => $encryptedMessageContent,
                'chat_id' => $this->dto->chatId,
            ]);
            event(new SendRealMessageIdEvent($message->id,$this->dto->tempId,$this->senderId));
            $message->content = $this->dto->content;
            if(!$chat->last_message){
                $this->renderChat($chat, $message, $getUserByIdRepository);
            }
            $this->replaceLastMessage($chat,$encryptedMessageContent,$this->senderId);
            event(new StoreMessageEvent($message));
            $this->storeUnreadMessagesCountJob($chat);
        }catch (\Exception $exception){
            Log::error("Message not saved in StoreMessageJob", [
                'error' => $exception->getMessage(),
                'sender_id' => $this->senderId,
                'chat_id' => $this->dto->chatId,
                'trace' => $exception->getTraceAsString()
            ]);
            event(new FailStoreMessageEvent($this->senderId));
        }
    }
    private function replaceLastMessage(Chat $chat,string $encryptedMessage,int $sender_id):void
    {
        $chat->update(['last_message' => $encryptedMessage]);
        $members = array_diff($chat->participants,[$sender_id]);
        foreach ($members as $member) {
            event(new LastMessageUpdateEvent($this->dto->content,$this->dto->chatId,$member));
        }
    }
    private function renderChat(Chat $chat,Message $message,GetUserByIdRepositoryInterface $getUserByIdRepository):void
    {
        $members = $chat->participants;
        if($chat->type === 'private'){
            $this->ChatForRecipientAndSender($members,$getUserByIdRepository,$chat,$message);
        }
    }
    private function storeUnreadMessagesCountJob(Chat $chat):void
    {
        $members = array_diff($chat->participants,[$this->senderId]);
        StoreUnreadMessagesCountJob::dispatch($members,$this->senderId, $this->dto->chatId);
    }
    private function ChatForRecipientAndSender(array $members,GetUserByIdRepositoryInterface $getUserByIdRepository,Chat $chat, Message $message):void
    {
        $recipientId = ((int)$members[0] !== (int)$message->sender_id) ? $members[0] : $members[1];
        $sender = $getUserByIdRepository->exec($message->sender_id);
        $recipient = $getUserByIdRepository->exec($recipientId);
        event(new RenderChatEvent($recipientId,$message->content,$message->created_at,$sender,$chat->id,true));
        event(new RenderChatEvent($message->sender_id,$message->content,$message->created_at,$recipient,$chat->id,false));
    }
}
