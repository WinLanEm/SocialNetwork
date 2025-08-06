<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;
use App\Exceptions\JsonAuthorizationException;
use Illuminate\Support\Facades\Gate;

class DestroyChatRepository implements DestroyChatRepositoryInterface
{
    public function exec(string $id):int
    {
        $chat = Chat::find($id);
        if(!$chat){
            return 0;
        }
        Gate::authorize('view-chat', $chat);

        return Chat::destroy($id);
    }
}
