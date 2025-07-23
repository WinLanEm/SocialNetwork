<?php

namespace App\Domain\Message\Actions;

interface MessageCryptoActionInterface
{
    public function encrypt(string $secretKey, string $message): string;
    public function decrypt(string $secretKey, string $message): string;
}
