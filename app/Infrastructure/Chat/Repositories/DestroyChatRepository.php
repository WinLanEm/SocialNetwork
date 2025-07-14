<?php

namespace App\Infrastructure\Chat\Repositories;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Chat\Repositories\DestroyChatRepositoryInterface;

class DestroyChatRepository implements DestroyChatRepositoryInterface
{
    public function exec(string $id):bool
    {
        return Chat::destroy($id);
    }
}
