<?php

namespace App\Domain\Message\Actions;

use App\Application\Message\DTOs\StoreMessageDTO;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;

interface ChoiceStoreMessageStrategyInterface
{
    public function exec(StoreMessageDTO $DTO,int $senderId):bool;
}
