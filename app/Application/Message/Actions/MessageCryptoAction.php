<?php

namespace App\Application\Message\Actions;

use App\Domain\Chat\Entities\Chat;
use App\Domain\Message\Actions\MessageCryptoActionInterface;
use Illuminate\Support\Facades\Crypt;

class MessageCryptoAction implements MessageCryptoActionInterface
{
    public function encrypt(string $secretKey, string $message): string
    {
        $chatKey = hex2bin($secretKey);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $message,
            'AES-256-CBC',
            $chatKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }
    public function decrypt(string $secretKey, string $message): string
    {
        $chatKey = hex2bin($secretKey);
        $data = base64_decode($message);
        if ($data === false || strlen($data) < 16) {
            throw new \RuntimeException('Invalid encrypted message format');
        }
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $chatKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed: ' . openssl_error_string());
        }
        return $decrypted;
    }
}
