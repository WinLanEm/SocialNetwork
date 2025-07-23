<?php

namespace App\Infrastructure\Message\Repositories;

use App\Domain\Message\Entities\Message;
use App\Domain\Message\Repositories\ReadMessageByIdRepositoryInterface;

class ReadMessageByIdRepository implements ReadMessageByIdRepositoryInterface
{
    public function exec(string $messageId): ?Message
    {
        return Message::find($messageId);
    }
}
