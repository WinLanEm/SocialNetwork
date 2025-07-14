<?php

namespace App\Domain\Chat\Actions;

interface ChatTokenCryptoActionInterface
{
    public function create():string;
    public function encrypt(string $token):string;
    public function decrypt(string $encryptedToken):string;
}
