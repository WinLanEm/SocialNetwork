<?php

namespace App\Domain\Chat\Repositories;

interface ChatIsReadRepositoryInterface
{
    public function exec(array $chatIds,int $userId):array;
}
