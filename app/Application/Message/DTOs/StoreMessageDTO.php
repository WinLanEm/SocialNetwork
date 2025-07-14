<?php

namespace App\Application\Message\DTOs;

class StoreMessageDTO
{
    public function __construct(
        public string $content,
        public int $chatId,
    )
    {
    }
    public static function fromRequest(array $data):self
    {
        return new self(
            $data['content'],
            $data['chat_id']
        );
    }
}
