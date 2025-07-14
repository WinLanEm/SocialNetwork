<?php

namespace App\Application\Message\Jobs;

use App\Application\Message\DTOs\StoreMessageDTO;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Chat\Repositories\GetOrCreateChatRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreStringMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;
    private StoreMessageDTO $dto;
    private int $senderId;

    public function __construct(StoreMessageDTO $DTO,int $senderId)
    {
        $this->dto = $DTO;
        $this->senderId = $senderId;
    }

    public function handle(GetChatByIdRepositoryInterface $getChatByIdRepository)
    {
        $chat = $getChatByIdRepository->exec($this->dto->chatId);

    }
}
