<?php

namespace App\Application\Message\Jobs;

use App\Application\ChatUnreadMessage\Jobs\StoreUnreadMessagesCountJob;
use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Events\FailStoreMessageEvent;
use App\Application\Message\Events\SendRealMessageIdEvent;
use App\Application\Message\Events\StoreMessageEvent;
use App\Domain\Chat\Actions\ChatRenderActionInterface;
use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use App\Domain\Message\Actions\UpdateLastMessageActionInterface;
use App\Domain\Message\Entities\Message;
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
                           MessageCryptoActionInterface $messageCryptoAction,
                            ChatRenderActionInterface $chatRenderAction,
                            UpdateLastMessageActionInterface $lastMessageAction): void
    {
        $chat = $getChatByIdRepository->exec($this->dto->chatId);
        $chat->update([
            'updated_at' => now(),
        ]);
        try {
            $message = $this->createMessage($chat, $messageCryptoAction);
            $this->handleSuccessScenario($chat, $message, $chatRenderAction,$lastMessageAction);
        }catch (\Exception $exception){
            $this->handleFailure($exception);
        }
    }

    private function handleFailure(\Exception $exception):void
    {
        Log::error("Message not saved in StoreMessageJob", [
            'error' => $exception->getMessage(),
            'sender_id' => $this->senderId,
            'chat_id' => $this->dto->chatId,
            'trace' => $exception->getTraceAsString()
        ]);
        event(new FailStoreMessageEvent($this->senderId));
    }
    private function createMessage(Chat $chat,MessageCryptoActionInterface $crypto):Message
    {
        return Message::create([
            'sender_id' => $this->senderId,
            'content' => $crypto->encrypt($chat->secret_key,$this->dto->content),
            'chat_id' => $this->dto->chatId,
        ]);
    }
    private function handleSuccessScenario(Chat $chat,Message $message,ChatRenderActionInterface $renderService,UpdateLastMessageActionInterface $lastMessageAction):void
    {
        event(new SendRealMessageIdEvent($message->id,$this->dto->tempId,$this->senderId));
        $message->content = $this->dto->content;
        if (!$chat->last_message && $chat->type === 'private') {
            $renderService->renderForPrivateChat($chat->participants,$chat,$message);
        }
        $lastMessageAction->update(
            $chat,
            $message->getRawOriginal('content'),
            $this->senderId,
            $this->dto->content,
        );
        event(new StoreMessageEvent($message));
        $this->dispatchUnreadCountJob($chat);
    }
    private function dispatchUnreadCountJob(Chat $chat):void
    {
        $members = array_diff($chat->participants,[$this->senderId]);
        StoreUnreadMessagesCountJob::dispatch($members,$this->senderId, $this->dto->chatId);
    }
}
