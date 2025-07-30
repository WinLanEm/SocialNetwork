<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;

class DestroyChatRepository implements DestroyChatRepositoryInterface
{
    public function exec(string $id):int
    {
        $chat = Chat::find($id);
        if(!$chat){
            return 0;
        }

        if (!in_array(auth()->id(), $chat->participants)) {
            return 0;
        }

        return Chat::destroy($id);
    }
}
