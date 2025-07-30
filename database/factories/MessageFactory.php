<?php

namespace Database\Factories;

use App\Application\Message\Actions\MessageCryptoAction;
use App\Domain\Message\Entities\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;
    protected MessageCryptoAction $crypto;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->crypto = new MessageCryptoAction();
    }
    public function definition()
    {
        return [
            'content' => $this->faker->realText(),
            'chat_id' => $this->faker->uuid(),
            'sender_id' => $this->faker->uuid(),
            'is_read' => $this->faker->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    public function forChat(string $chatId): self
    {
        return $this->state(function (array $attributes) use ($chatId) {
            return ['chat_id' => $chatId];
        });
    }

    public function fromSender(string $senderId): self
    {
        return $this->state(function (array $attributes) use ($senderId) {
            return ['sender_id' => $senderId];
        });
    }
    public function withSecretKey(string $secretKey): self
    {
        return $this->state(function (array $attributes) use ($secretKey) {
            $plaintext = $this->faker->realText();

            return [
                'content' => $this->crypto->encrypt($secretKey, $plaintext),
            ];
        });
    }
}
