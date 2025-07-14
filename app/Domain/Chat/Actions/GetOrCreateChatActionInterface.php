<?php

namespace App\Domain\Chat\Actions;

use App\Application\Chat\DTOs\GetOrCreateChatDTO;

interface GetOrCreateChatActionInterface
{
    public function exec(GetOrCreateChatDTO $DTO):array;
}
