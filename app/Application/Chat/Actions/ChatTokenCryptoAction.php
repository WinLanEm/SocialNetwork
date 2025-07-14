<?php

namespace App\Application\Chat\Actions;

use App\Domain\Chat\Actions\ChatTokenCryptoActionInterface;
use http\Exception\RuntimeException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Crypt;

class ChatTokenCryptoAction implements ChatTokenCryptoActionInterface
{
    public function create(): string
    {
        return random_bytes(32);
    }

    public function encrypt(string $token): string
    {
        try {
            return Crypt::encrypt($token);
        }catch (EncryptException $e){
            throw new RuntimeException('Encrypt token error: ' . $e->getMessage());
        }
    }

    public function decrypt(string $encryptedToken): string
    {
        try {
            return Crypt::decrypt($encryptedToken);
        } catch (DecryptException $e) {
            throw new RuntimeException("Decrypt token error: " . $e->getMessage());
        }
    }

}
