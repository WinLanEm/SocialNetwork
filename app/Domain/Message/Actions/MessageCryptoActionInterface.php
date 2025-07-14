<?php

namespace App\Domain\Message\Actions;

interface MessageCryptoActionInterface
{
    public function encrypt(string $message,string $chatToken);
    public function decrypt(string $encryptedMessage,string $chatToken);
}
