<?php

namespace App\Application\User\DTOs;

class AuthorizationDTO
{
    public function __construct(
        public string $phone,
        public string $password
    )
    {
    }

    public static function fromRequest(array $data):self
    {
        return new self(
            $data['phone'],
            $data['password'],
        );
    }
}
