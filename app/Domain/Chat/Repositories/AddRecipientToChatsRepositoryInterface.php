<?php

namespace App\Domain\Chat\Repositories;

use Illuminate\Support\Collection;

interface AddRecipientToChatsRepositoryInterface
{
    public function exec(Collection $userChats,int $currentUserId):array;
}
