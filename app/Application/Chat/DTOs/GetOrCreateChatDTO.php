<?php

namespace App\Application\Chat\DTOs;

class GetOrCreateChatDTO
{
    public function __construct(
        public array $userIds,
        public string $type,
    )
    {
    }
    public static function fromRequest(array $data):self
    {
        return new self(
            $data['participants'],
            $data['type']
        );
    }
}
