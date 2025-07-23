<?php

namespace App\Domain\Message\Repositories;

interface MakeMessagesIsReadRepositoryInterface
{
    public function exec(array $messageIds): void;
}
