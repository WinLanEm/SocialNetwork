<?php

namespace App\Infrastructure\Message\Repositories;

use App\Domain\Message\Entities\Message;
use App\Domain\Message\Repositories\MakeMessagesIsReadRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MakeMessagesIsReadRepository implements MakeMessagesIsReadRepositoryInterface
{
    public function exec(array $messageIds): void
    {
        if (empty($messageIds)) {
            return;
        }
        DB::transaction(function () use ($messageIds) {
            Message::whereIn('id', $messageIds)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        });
    }
}
