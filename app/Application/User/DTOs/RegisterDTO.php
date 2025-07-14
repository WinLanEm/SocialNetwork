<?php

namespace App\Application\User\DTOs;

class RegisterDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly string $password_confirmation,
        public readonly string $phone
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['username'],
            $data['password'],
            $data['password_confirmation'],
            $data['phone']
        );
    }
}
