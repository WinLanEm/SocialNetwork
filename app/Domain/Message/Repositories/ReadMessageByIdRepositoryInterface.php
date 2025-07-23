<?php

namespace App\Domain\Message\Repositories;

use App\Domain\Message\Entities\Message;

interface ReadMessageByIdRepositoryInterface
{
    public function exec(string $messageId):?Message;
}
