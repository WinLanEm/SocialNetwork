<?php

namespace App\Application\Message\Actions;

use App\Application\Message\DTOs\StoreMessageDTO;
use App\Application\Message\Jobs\StoreStringMessageJob;
use App\Domain\Chat\Repositories\GetChatByIdRepositoryInterface;
use App\Domain\Message\Actions\ChoiceStoreMessageStrategyInterface;

class ChoiceStoreMessageStrategy implements ChoiceStoreMessageStrategyInterface
{
    public function exec(StoreMessageDTO $DTO,int $senderId):bool
    {
        switch ($DTO->content){
            case is_string($DTO->content):{
                StoreStringMessageJob::dispatch($DTO,$senderId);
                break;
            }
            case is_file($DTO->content):{
                break;
            }
            default:return false;
        }
        return true;
    }
}
