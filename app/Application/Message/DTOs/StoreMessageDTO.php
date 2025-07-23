<?php

namespace App\Application\Message\DTOs;

class StoreMessageDTO
{
    public function __construct(
        public string $content,
        public string $chatId,
        public string $tempId,
    )
    {
    }
    public static function fromRequest(array $data):self
    {
        return new self(
            (string) $data['content'],
            (string) $data['chat_id'],
            (string) $data['temp_id'],
        );
    }
}
