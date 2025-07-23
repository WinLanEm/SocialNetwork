<?php

namespace App\Application\Message\DTOs;

use App\Presentation\Message\Requests\MakeIsReadRequest;

class MakeMessagesIsReadDTO
{
    public function __construct(
        public array $messageIds,
        public int $userId,
        public string $chatId,
    )
    {
    }
    public static function fromRequest(MakeIsReadRequest $request): self
    {
        return new self(
            $request->get('message_ids'),
            $request->get('user_id'),
            $request->get('chat_id'),
        );
    }
}
