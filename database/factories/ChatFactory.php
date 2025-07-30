<?php

namespace Database\Factories;



use App\Domain\Chat\Entities\Chat;
use App\Domain\User\Entities\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatFactory extends Factory
{

    protected $model = Chat::class;
    public function definition():array
    {
        return [
            'participants' => [
                User::factory()->create(['id' => rand(0,532532)])->id,
                User::factory()->create(['id' => rand(0,532532)])->id,
            ],
            'type' => 'private',
            'last_message' => $this->faker->sentence,
            'secret_key' => bin2hex(random_bytes(32)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    public function groupChat(): static
    {
        return $this->state([
            'type' => 'group',
            'participants' => User::factory()->count(3)->create()->pluck('id')->toArray()
        ]);
    }
}
