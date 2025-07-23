<?php

namespace App\Domain\Message\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface PaginateChatMessagesRepositoryInterface
{
    public function exec(string $chatId,string $secretKey,?int $page):array;
}
